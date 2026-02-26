<?php

declare(strict_types=1);

namespace App\Imports;

use App\Models\Branch;
use App\Models\Member;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

final class MembersImport implements SkipsOnFailure, ToCollection, WithBatchInserts, WithChunkReading, WithHeadingRow, WithValidation
{
    use \Maatwebsite\Excel\Concerns\Importable;

    private int $branchId;

    private array $errors = [];

    private array $successes = [];

    private int $successCount = 0;

    private int $failureCount = 0;

    private array $usersForAccountSetup = [];

    private ?int $currentRow = null;

    private string $registrationSource;

    private string $defaultMemberStatus;

    public function __construct(int $branchId, string $registrationSource = 'imported', string $defaultMemberStatus = 'member')
    {
        $this->branchId = $branchId;
        $this->registrationSource = $registrationSource;
        $this->defaultMemberStatus = $defaultMemberStatus;
    }

    /**
     * Process the collection of member data.
     */
    public function collection(Collection $rows): void
    {
        // Disable query log for better performance
        \DB::disableQueryLog();

        foreach ($rows as $index => $row) {
            try {
                $this->processRow($row->toArray(), $index + 2); // +2 for header and 0-index

                // Free memory periodically
                if (($index + 1) % 50 === 0) {
                    gc_collect_cycles();
                }
            } catch (\Exception $e) {
                $this->addError($index + 2, 'general', $e->getMessage());
                $this->failureCount++;
            }
        }

        // Re-enable query log
        \DB::enableQueryLog();
    }

    /**
     * Process a single row of member data.
     */
    private function processRow(array $row, int $rowNumber): void
    {
        // Store current row for logging in helper methods
        $this->currentRow = $rowNumber;

        try {
            // Clean and validate data
            $data = $this->cleanRowData($row);

            $validator = Validator::make($data, $this->getRowValidationRules());

            if ($validator->fails()) {
                // Log first few validation errors for debugging
                if ($this->failureCount < 5) {
                    \Log::warning('Import validation failed', [
                        'row' => $rowNumber,
                        'data' => $data,
                        'errors' => $validator->errors()->all(),
                        'original_row_keys' => array_keys($row),
                    ]);
                }

                foreach ($validator->errors()->all() as $error) {
                    $this->addError($rowNumber, 'validation', $error);
                }
                $this->failureCount++;

                return;
            }
        } catch (\Exception $e) {
            $this->addError($rowNumber, 'general', 'Failed to clean/validate row data: '.$e->getMessage());
            $this->failureCount++;

            return;
        }

        // Check if member already exists (by email if provided, otherwise by name and phone)
        $existingMember = null;

        if (! empty($data['email'])) {
            $existingMember = Member::where('email', $data['email'])->first();
        } elseif (! empty($data['phone'])) {
            $existingMember = Member::where('name', $data['name'])
                ->where('phone', $data['phone'])
                ->first();
        }

        if ($existingMember) {
            $identifier = $data['email'] ?? $data['name'];
            $comparison = $this->compareMemberData($existingMember, $data);
            $this->addError($rowNumber, 'duplicate', [
                'message' => "Member already exists: {$identifier}",
                'comparison' => $comparison,
                'existing_id' => $existingMember->id,
            ]);
            $this->failureCount++;

            return;
        }

        // Create or find user account (always creates, generates temporary email if needed)
        $user = $this->createOrFindUser($data);

        // Create member record
        $memberData = $this->prepareMemberData($data, $user?->id);
        $member = Member::create($memberData);

        // Assign member role to user (only if user account exists)
        if ($user) {
            $memberRole = Role::where('name', 'church_member')->first();
            if ($memberRole && ! $user->roles()->where('role_id', $memberRole->id)->exists()) {
                $user->assignRole('church_member', $member->branch_id);
            }
        }

        $this->successCount++;
        $this->successes[] = [
            'row' => $rowNumber,
            'member_id' => $member->id,
            'email' => $member->email,
            'name' => $member->name,
        ];

        // Add user to account setup email list (all users get accounts now)
        if ($user) {
            $this->usersForAccountSetup[] = [
                'user_id' => $user->id,
                'email' => $user->email,
                'name' => $user->name,
            ];
        }

        Log::info('Member imported successfully', [
            'member_id' => $member->id,
            'email' => $member->email,
            'row_number' => $rowNumber,
        ]);
    }

    /**
     * Clean and normalize row data.
     */
    private function cleanRowData(array $row): array
    {
        $cleanData = [];

        // Map column headers to database fields (includes variations with spaces for export compatibility)
        $fieldMap = [
            'name' => ['name', 'full_name', 'member_name'],
            'first_name' => ['first_name', 'firstname', 'fname', 'first name'],
            'last_name' => ['last_name', 'lastname', 'lname', 'surname', 'last name'],
            'email' => ['email', 'email_address', 'email address'],
            // Note: Google Forms exports often use headers like "Phone Number (WhatsApp)"
            'phone' => ['phone', 'phone_number', 'mobile', 'phone number', 'whatsapp_number', 'phone_number_whatsapp', 'phone_number_(whatsapp)'],
            'date_of_birth' => ['date_of_birth', 'dob', 'birth_date', 'date of birth'],
            'anniversary' => ['anniversary', 'wedding_anniversary', 'wedding anniversary'],
            'gender' => ['gender', 'sex'],
            'marital_status' => ['marital_status', 'marriage_status', 'marital status'],
            'occupation' => ['occupation', 'job', 'profession'],
            'nearest_bus_stop' => ['nearest_bus_stop', 'bus_stop', 'nearest bus stop', 'bus stop'],
            'date_joined' => ['date_joined', 'join_date', 'membership_date', 'timestamp', 'date joined'],
            'date_attended_membership_class' => ['date_attended_membership_class', 'membership_class_date', 'date attended membership class'],
            'teci_status' => ['teci_status', 'teci', 'teci status'],
            'growth_level' => ['growth_level', 'level', 'growth level'],
            'member_status' => ['member_status', 'status', 'member status'],
            'branch_name' => ['branch_name', 'branch', 'church_branch', 'branch name'],
            // Guest form fields
            'preferred_call_time' => ['preferred_call_time', 'call_time', 'best_time_to_call', 'preferred call time'],
            'home_address' => ['home_address', 'address', 'residential_address', 'home address'],
            'age_group' => ['age_group', 'age_range', 'age group'],
            'prayer_request' => ['prayer_request', 'prayer_requests', 'prayer_needs', 'prayer request'],
            // Google Forms exports often include punctuation: "How did you hear about us?"
            'discovery_source' => ['discovery_source', 'how_did_you_hear', 'how_heard_about_us', 'discovery source', 'how_did_you_hear_about_us', 'how_did_you_hear_about_us?'],
            'staying_intention' => ['staying_intention', 'intention_to_stay', 'plan_to_stay', 'staying intention', 'will_you_be_staying_with_us', 'will_you_be_staying_with_us?'],
            'closest_location' => ['closest_location', 'nearest_location', 'preferred_location', 'closest location'],
            'additional_info' => ['additional_info', 'additional_information', 'other_info', 'notes', 'additional info'],
            'leadership_trainings' => ['leadership_trainings', 'trainings', 'completed_trainings', 'leadership trainings'],
        ];

        // Normalize row keys for case-insensitive matching.
        // Important: handle punctuation in headers (e.g., "Phone Number (WhatsApp)", "How did you hear about us?")
        $normalizedRow = [];
        foreach ($row as $key => $value) {
            $normalizedKey = $this->normalizeHeaderKey((string) $key);
            $normalizedRow[$normalizedKey] = $value;
        }

        foreach ($fieldMap as $dbField => $possibleHeaders) {
            foreach ($possibleHeaders as $header) {
                $normalizedHeader = $this->normalizeHeaderKey((string) $header);
                if (isset($normalizedRow[$normalizedHeader]) && ! empty($normalizedRow[$normalizedHeader])) {
                    $value = $normalizedRow[$normalizedHeader];

                    // Clean and format email
                    if ($dbField === 'email') {
                        $value = $this->cleanEmail($value);
                    }

                    // Format phone numbers to Nigerian format
                    if ($dbField === 'phone') {
                        $value = $this->formatPhoneNumber($value);
                    }

                    $cleanData[$dbField] = trim((string) $value);
                    break;
                }
            }
        }

        // Handle combining first_name and last_name into name if name is not already set
        if (! isset($cleanData['name']) && (isset($cleanData['first_name']) || isset($cleanData['last_name']))) {
            $firstName = trim($cleanData['first_name'] ?? '');
            $lastName = trim($cleanData['last_name'] ?? '');

            if ($firstName || $lastName) {
                $cleanData['name'] = trim($firstName.' '.$lastName);
            }
        }

        // Remove first_name and last_name since we only need the combined name
        unset($cleanData['first_name'], $cleanData['last_name']);

        // Parse leadership trainings if present (check normalized row for "Leadership Trainings" from exports)
        if (! isset($cleanData['leadership_trainings'])) {
            foreach (['leadership_trainings', 'trainings', 'completed_trainings'] as $key) {
                $normalizedKey = strtolower(trim($key));
                $normalizedKey = str_replace(' ', '_', $normalizedKey);
                if (isset($normalizedRow[$normalizedKey]) && ! empty($normalizedRow[$normalizedKey])) {
                    $leadershipTrainingsValue = $normalizedRow[$normalizedKey];
                    $trainings = explode(',', (string) $leadershipTrainingsValue);
                    $cleanData['leadership_trainings'] = array_map('trim', $trainings);
                    break;
                }
            }
        }

        // Normalize gender
        if (isset($cleanData['gender'])) {
            $gender = strtolower(trim((string) $cleanData['gender']));
            $cleanData['gender'] = match ($gender) {
                'female', 'f' => 'female',
                'male', 'm' => 'male',
                // DB enum is only male/female; treat other answers as null.
                default => null,
            };

            if ($cleanData['gender'] === null) {
                unset($cleanData['gender']);
            }
        }

        // Normalize marital status
        if (isset($cleanData['marital_status'])) {
            $status = strtolower($cleanData['marital_status']);
            $cleanData['marital_status'] = match ($status) {
                'married', 'wed' => 'married',
                'single', 'unmarried' => 'single',
                'divorced' => 'divorced',
                'separated' => 'separated',
                'widowed', 'widow', 'widower' => 'widowed',
                'in a relationship', 'relationship', 'dating', 'in-relationship' => 'in_a_relationship',
                'engaged', 'engagement' => 'engaged',
                default => 'single'
            };
        }

        // Normalize guest form enums to match DB enum values (Google Forms often uses human strings).
        if (isset($cleanData['preferred_call_time'])) {
            $raw = strtolower(trim((string) $cleanData['preferred_call_time']));
            $raw = str_replace([' ', '_'], '-', $raw);
            $cleanData['preferred_call_time'] = match ($raw) {
                'anytime', 'any-time', 'any' => 'anytime',
                'morning', 'am' => 'morning',
                'afternoon', 'pm' => 'afternoon',
                'evening', 'night' => 'evening',
                default => null,
            };
            if ($cleanData['preferred_call_time'] === null) {
                unset($cleanData['preferred_call_time']);
            }
        }

        if (isset($cleanData['age_group'])) {
            $raw = strtolower(trim((string) $cleanData['age_group']));
            $raw = preg_replace('/\s+/', '', $raw);
            $raw = str_replace(['–', '—'], '-', $raw); // normalize dashes
            $cleanData['age_group'] = match (true) {
                in_array($raw, ['15-20', '15to20'], true) => '15-20',
                in_array($raw, ['21-25', '21to25'], true) => '21-25',
                in_array($raw, ['26-30', '26to30'], true) => '26-30',
                in_array($raw, ['31-35', '31to35'], true) => '31-35',
                in_array($raw, ['36-40', '36to40'], true) => '36-40',
                in_array($raw, ['above-40', 'above40', '40+', '40plus', 'above_40'], true) => 'above-40',
                default => null,
            };
            if ($cleanData['age_group'] === null) {
                unset($cleanData['age_group']);
            }
        }

        if (isset($cleanData['discovery_source'])) {
            $raw = strtolower(trim((string) $cleanData['discovery_source']));
            $raw = preg_replace('/\s+/', ' ', $raw);
            $cleanData['discovery_source'] = match (true) {
                str_contains($raw, 'social') => 'social-media',
                str_contains($raw, 'word') || str_contains($raw, 'mouth') => 'word-of-mouth',
                str_contains($raw, 'bill') => 'billboard',
                str_contains($raw, 'email') => 'email',
                str_contains($raw, 'web') => 'website',
                str_contains($raw, 'promo') || str_contains($raw, 'flyer') => 'promotional-material',
                str_contains($raw, 'radio') || str_contains($raw, 'tv') => 'radio-tv',
                str_contains($raw, 'outreach') => 'outreach',
                default => null,
            };
            if ($cleanData['discovery_source'] === null) {
                unset($cleanData['discovery_source']);
            }
        }

        if (isset($cleanData['staying_intention'])) {
            $raw = strtolower(trim((string) $cleanData['staying_intention']));
            $raw = preg_replace('/\s+/', ' ', $raw);
            $cleanData['staying_intention'] = match (true) {
                str_contains($raw, 'yes') => 'yes-for-sure',
                str_contains($raw, 'town') => 'visit-when-in-town',
                str_contains($raw, 'just') || str_contains($raw, 'visit') => 'just-visiting',
                str_contains($raw, 'weigh') || str_contains($raw, 'option') => 'weighing-options',
                default => null,
            };
            if ($cleanData['staying_intention'] === null) {
                unset($cleanData['staying_intention']);
            }
        }

        // Parse dates (also check for "Date Joined", "Anniversary", etc. from exports)
        $dateFieldMap = [
            'anniversary' => ['anniversary', 'wedding_anniversary'],
            'date_joined' => ['date_joined', 'join_date', 'membership_date', 'timestamp', 'date_joined'], // "Date Joined" normalized becomes "date_joined"
            'date_attended_membership_class' => ['date_attended_membership_class', 'membership_class_date'],
        ];

        foreach ($dateFieldMap as $dbField => $possibleHeaders) {
            if (! isset($cleanData[$dbField])) {
                // Try to find it in normalized row
                foreach ($possibleHeaders as $header) {
                    $normalizedHeader = strtolower(trim($header));
                    $normalizedHeader = str_replace(' ', '_', $normalizedHeader);
                    if (isset($normalizedRow[$normalizedHeader]) && ! empty($normalizedRow[$normalizedHeader])) {
                        $cleanData[$dbField] = $this->parseDate($normalizedRow[$normalizedHeader]);
                        break;
                    }
                }
            } else {
                $cleanData[$dbField] = $this->parseDate($cleanData[$dbField]);
            }
        }

        // Parse birthdate with special handling for partial dates (day/month without year)
        // Check both cleanData and normalizedRow for date_of_birth (handles "Date of Birth" from exports)
        $dobValue = null;
        if (isset($cleanData['date_of_birth'])) {
            $dobValue = $cleanData['date_of_birth'];
        } else {
            // Try normalized row for "Date of Birth" header (normalized becomes "date_of_birth")
            foreach (['date_of_birth', 'dob', 'birth_date'] as $key) {
                $normalizedKey = strtolower(trim($key));
                $normalizedKey = str_replace(' ', '_', $normalizedKey);
                if (isset($normalizedRow[$normalizedKey]) && ! empty($normalizedRow[$normalizedKey])) {
                    $dobValue = $normalizedRow[$normalizedKey];
                    break;
                }
            }
        }

        if ($dobValue && is_string($dobValue)) {
            $dateValue = trim($dobValue);
            if (! empty($dateValue)) {
                $birthdayData = $this->parseBirthDate($dateValue);
                $cleanData['date_of_birth'] = $birthdayData['date'];
                $cleanData['birthday_month'] = $birthdayData['month'];
                $cleanData['birthday_day'] = $birthdayData['day'];
            } else {
                // Ensure null values are set explicitly
                $cleanData['date_of_birth'] = null;
                $cleanData['birthday_month'] = null;
                $cleanData['birthday_day'] = null;
            }
        } elseif (! isset($cleanData['date_of_birth'])) {
            // Set to null if not found
            $cleanData['date_of_birth'] = null;
            $cleanData['birthday_month'] = null;
            $cleanData['birthday_day'] = null;
        }

        // Convert empty strings to null for date fields to pass nullable validation
        foreach (['date_of_birth', 'anniversary', 'date_joined', 'date_attended_membership_class'] as $dateField) {
            if (isset($cleanData[$dateField]) && $cleanData[$dateField] === '') {
                $cleanData[$dateField] = null;
            }
        }

        return array_filter($cleanData, fn ($value) => ! is_null($value) && $value !== '');
    }

    /**
     * Normalize header keys to improve mapping robustness across CSV/Excel exports.
     *
     * Examples:
     * - "Phone Number (WhatsApp)" => "phone_number_whatsapp"
     * - "How did you hear about us?" => "how_did_you_hear_about_us"
     */
    private function normalizeHeaderKey(string $key): string
    {
        $key = strtolower(trim($key));
        $key = str_replace(['–', '—'], '-', $key);
        $key = str_replace([' ', '-'], '_', $key);
        $key = preg_replace('/[^a-z0-9_]+/', '_', $key);
        $key = preg_replace('/_+/', '_', $key);

        return trim($key, '_');
    }

    /**
     * Clean and normalize email address.
     */
    private function cleanEmail(string $email): string
    {
        // Trim whitespace
        $email = trim($email);

        // Convert to lowercase
        $email = strtolower($email);

        // Remove all whitespace characters
        $email = preg_replace('/\s+/', '', $email);

        return $email;
    }

    /**
     * Format phone number to Nigerian format (09068719246).
     */
    private function formatPhoneNumber(mixed $phone): string
    {
        // Convert to string and trim (Excel may provide numeric values)
        $phone = trim((string) $phone);

        // Remove all non-numeric characters except +
        $phone = preg_replace('/[^\d+]/', '', $phone);

        // Handle different formats
        if (str_starts_with($phone, '+234')) {
            // Convert +2349068719246 to 09068719246
            $phone = '0'.substr($phone, 4);
        } elseif (str_starts_with($phone, '234')) {
            // Convert 2349068719246 to 09068719246
            $phone = '0'.substr($phone, 3);
        } elseif (! str_starts_with($phone, '0') && strlen($phone) === 10) {
            // If it's 10 digits without leading 0, add 0
            $phone = '0'.$phone;
        }

        // Ensure it starts with 0 and has 11 digits (Nigerian format)
        if (str_starts_with($phone, '0') && strlen($phone) === 11) {
            return $phone;
        }

        // If format is still invalid, return as-is (validation will catch it)
        return $phone;
    }

    /**
     * Parse various date formats.
     */
    private function parseDate(string $dateString): ?string
    {
        try {
            $date = \Carbon\Carbon::parse($dateString);

            return $date->format('Y-m-d');
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Parse birthdate with special handling for partial dates (day/month without year).
     * Returns array with 'date' (full date or null) and 'month'/'day' (extracted values).
     */
    private function parseBirthDate(string $dateString): array
    {
        $dateString = trim($dateString);
        $result = [
            'date' => null,
            'month' => null,
            'day' => null,
        ];

        // Return early if empty
        if (empty($dateString)) {
            return $result;
        }

        // Check if it's a partial date (only day/month format like "26/09" or "13/11")
        // Pattern: digits/digits with optional leading zeros, no year
        if (preg_match('/^(\d{1,2})\/(\d{1,2})$/', $dateString, $matches)) {
            // This is a partial date without year
            $day = (int) $matches[1];
            $month = (int) $matches[2];

            // Determine if it's DD/MM or MM/DD format
            // If first part > 12, it's likely DD/MM, otherwise try both interpretations
            if ($day > 12) {
                // Definitely DD/MM format
                $result['day'] = $day;
                $result['month'] = $month;
            } elseif ($month > 12) {
                // Definitely MM/DD format
                $result['day'] = $month;
                $result['month'] = $day;
            } else {
                // Ambiguous - assume DD/MM (common in many countries)
                // But validate both possibilities
                if ($this->isValidDayForMonth($day, $month)) {
                    $result['day'] = $day;
                    $result['month'] = $month;
                } elseif ($this->isValidDayForMonth($month, $day)) {
                    $result['day'] = $month;
                    $result['month'] = $day;
                } else {
                    Log::warning('Partial birthdate could not be validated - storing as null', [
                        'date_string' => $dateString,
                        'row' => $this->currentRow ?? 'unknown',
                    ]);

                    return $result;
                }
            }

            // Validate the extracted values
            if (! $this->isValidDayForMonth($result['day'], $result['month'])) {
                Log::warning('Invalid partial birthdate (invalid day for month) - storing as null', [
                    'date_string' => $dateString,
                    'day' => $result['day'],
                    'month' => $result['month'],
                    'row' => $this->currentRow ?? 'unknown',
                ]);

                return ['date' => null, 'month' => null, 'day' => null];
            }

            Log::info('Partial birthdate detected (day/month only) - storing month/day only', [
                'date_string' => $dateString,
                'month' => $result['month'],
                'day' => $result['day'],
                'row' => $this->currentRow ?? 'unknown',
            ]);

            return $result;
        }

        // Try to parse as full date
        try {
            $date = \Carbon\Carbon::parse($dateString);

            // Validate it's a reasonable birthdate (not in the future, not too old)
            $maxAge = 120; // Reasonable maximum age
            $minDate = now()->subYears($maxAge);
            $maxDate = now();

            if ($date->isFuture()) {
                Log::warning('Birthdate is in the future - storing as null', [
                    'date_string' => $dateString,
                    'parsed_date' => $date->format('Y-m-d'),
                    'row' => $this->currentRow ?? 'unknown',
                ]);

                return $result;
            }

            if ($date->lt($minDate)) {
                Log::warning('Birthdate is too old (over '.$maxAge.' years) - storing as null', [
                    'date_string' => $dateString,
                    'parsed_date' => $date->format('Y-m-d'),
                    'row' => $this->currentRow ?? 'unknown',
                ]);

                return $result;
            }

            // Extract month and day from full date
            $result['date'] = $date->format('Y-m-d');
            $result['month'] = (int) $date->format('n'); // 1-12
            $result['day'] = (int) $date->format('j'); // 1-31

            return $result;
        } catch (\Exception $e) {
            Log::warning('Failed to parse birthdate - storing as null', [
                'date_string' => $dateString,
                'error' => $e->getMessage(),
                'row' => $this->currentRow ?? 'unknown',
            ]);

            return $result;
        }
    }

    /**
     * Validate that a day is valid for a given month.
     */
    private function isValidDayForMonth(int $day, int $month): bool
    {
        if ($month < 1 || $month > 12) {
            return false;
        }

        if ($day < 1 || $day > 31) {
            return false;
        }

        // Days in each month (non-leap year)
        $daysInMonth = [
            1 => 31, 2 => 28, 3 => 31, 4 => 30, 5 => 31, 6 => 30,
            7 => 31, 8 => 31, 9 => 30, 10 => 31, 11 => 30, 12 => 31,
        ];

        $maxDay = $daysInMonth[$month];

        // Handle leap year for February
        if ($month === 2 && $day === 29) {
            // Allow Feb 29 - it's valid in leap years
            return true;
        }

        return $day <= $maxDay;
    }

    /**
     * Get validation rules for each row.
     */
    private function getRowValidationRules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'first_name' => 'nullable|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'date_of_birth' => 'nullable|date',
            'anniversary' => 'nullable|date',
            'gender' => 'nullable|in:male,female,prefer-not-to-say',
            'marital_status' => 'nullable|in:single,married,divorced,separated,widowed,in_a_relationship,engaged',
            'occupation' => 'nullable|string|max:255',
            'nearest_bus_stop' => 'nullable|string|max:255',
            'date_joined' => 'nullable|date',
            'date_attended_membership_class' => 'nullable|date',
            'teci_status' => 'nullable|in:not_started,100_level,200_level,300_level,400_level,500_level,graduated,paused',
            'growth_level' => 'nullable|in:core,pastor,growing,new_believer',
            'member_status' => 'nullable|in:visitor,member,volunteer,leader,minister',
            'branch_name' => $this->branchId ? 'nullable' : 'required|string',
            // Guest form fields
            'preferred_call_time' => 'nullable|in:anytime,morning,afternoon,evening',
            'home_address' => 'nullable|string|max:1000',
            'age_group' => 'nullable|in:15-20,21-25,26-30,31-35,36-40,above-40',
            'prayer_request' => 'nullable|string|max:2000',
            'discovery_source' => 'nullable|in:social-media,word-of-mouth,billboard,email,website,promotional-material,radio-tv,outreach',
            'staying_intention' => 'nullable|in:yes-for-sure,visit-when-in-town,just-visiting,weighing-options',
            'closest_location' => 'nullable|string|max:255',
            'additional_info' => 'nullable|string|max:2000',
            'leadership_trainings' => 'nullable|string', // Will be processed as JSON array
        ];
    }

    /**
     * Create or find existing user account.
     * Always creates a user account, generating temporary email if needed.
     */
    private function createOrFindUser(array $data): ?User
    {
        $email = $data['email'] ?? null;
        $isTemporaryEmail = false;

        // Generate temporary email if none provided
        if (empty($email)) {
            $phone = $data['phone'] ?? null;
            if ($phone) {
                // Use phone-based temporary email
                $sanitizedPhone = preg_replace('/[^0-9]/', '', $phone);
                $email = "guest-{$sanitizedPhone}@church.local";
            } else {
                // Use timestamp and random number
                $email = 'guest-'.time().'-'.rand(1000, 9999).'@church.local';
            }
            $isTemporaryEmail = true;
        }

        // Check if user already exists (optimize query)
        $user = User::select('id', 'email')->where('email', $email)->first();

        if ($user) {
            return $user;
        }

        // Create new user account
        try {
            $password = $this->generateTemporaryPassword();

            $user = User::create([
                'name' => $data['name'],
                'email' => $email,
                'phone' => $data['phone'] ?? null,
                'password' => Hash::make($password),
                'email_verified_at' => now(), // Auto-verify church members
            ]);

            // Store metadata for temporary emails
            if ($isTemporaryEmail) {
                Log::info('User account created with temporary email for member import', [
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'phone' => $data['phone'] ?? null,
                    'name' => $data['name'],
                ]);
            } else {
                Log::info('User account created for member import', [
                    'user_id' => $user->id,
                    'email' => $user->email,
                ]);
            }

            return $user;
        } catch (\Exception $e) {
            Log::error('Failed to create user account during member import', [
                'email' => $email,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Prepare member data for database insertion.
     */
    private function prepareMemberData(array $data, ?int $userId): array
    {
        $branchId = $this->branchId;

        // If no branch ID provided, try to find by name
        if (! $branchId && isset($data['branch_name'])) {
            $branch = Branch::where('name', 'like', "%{$data['branch_name']}%")->first();
            $branchId = $branch?->id;
        }

        // Default to first branch if none found
        if (! $branchId) {
            $branchId = Branch::first()?->id;
        }

        // Handle first_name and surname
        $firstName = $data['first_name'] ?? null;
        $surname = $data['last_name'] ?? null;

        // If name is provided but first_name/surname are not, try to split
        if (! $firstName && ! $surname && isset($data['name'])) {
            $nameParts = explode(' ', trim($data['name']), 2);
            $firstName = $nameParts[0] ?? null;
            $surname = $nameParts[1] ?? null;
        }

        // Handle leadership_trainings - convert string to array if needed
        $leadershipTrainings = $data['leadership_trainings'] ?? [];
        if (is_string($leadershipTrainings)) {
            // Try to parse as JSON first, then as comma-separated
            $decoded = json_decode($leadershipTrainings, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $leadershipTrainings = $decoded;
            } else {
                $leadershipTrainings = array_map('trim', explode(',', $leadershipTrainings));
            }
        }

        return [
            'user_id' => $userId, // Can be null if no email provided
            'branch_id' => $branchId,
            'name' => $data['name'],
            'first_name' => $firstName,
            'surname' => $surname,
            'email' => $data['email'] ?? null,
            'phone' => $data['phone'] ?? null,
            'date_of_birth' => $data['date_of_birth'] ?? null,
            'birthday_month' => $data['birthday_month'] ?? null,
            'birthday_day' => $data['birthday_day'] ?? null,
            'anniversary' => $data['anniversary'] ?? null,
            'gender' => $data['gender'] ?? null,
            'marital_status' => $data['marital_status'] ?? 'single',
            'occupation' => $data['occupation'] ?? null,
            'nearest_bus_stop' => $data['nearest_bus_stop'] ?? null,
            'date_joined' => $data['date_joined'] ?? now()->format('Y-m-d'),
            'date_attended_membership_class' => $data['date_attended_membership_class'] ?? null,
            'teci_status' => $data['teci_status'] ?? 'not_started',
            'growth_level' => $data['growth_level'] ?? 'new_believer',
            'leadership_trainings' => $leadershipTrainings,
            'member_status' => $data['member_status'] ?? $this->defaultMemberStatus,
            // Guest form fields
            'preferred_call_time' => $data['preferred_call_time'] ?? null,
            'home_address' => $data['home_address'] ?? null,
            'age_group' => $data['age_group'] ?? null,
            'prayer_request' => $data['prayer_request'] ?? null,
            'discovery_source' => $data['discovery_source'] ?? null,
            'staying_intention' => $data['staying_intention'] ?? null,
            'closest_location' => $data['closest_location'] ?? null,
            'additional_info' => $data['additional_info'] ?? null,
            'registration_source' => $this->registrationSource,
        ];
    }

    /**
     * Compare existing member data with imported data.
     */
    private function compareMemberData(Member $existing, array $imported): array
    {
        $comparison = [
            'matches' => [],
            'differences' => [],
        ];

        $fieldsToCompare = [
            'name',
            'email',
            'phone',
            'branch_id',
            'member_status',
            'registration_source',
            'gender',
            'marital_status',
            'date_of_birth',
        ];

        foreach ($fieldsToCompare as $field) {
            $existingValue = $existing->$field ?? null;
            $importedValue = $imported[$field] ?? null;

            // Normalize for comparison
            if ($field === 'branch_id' && $importedValue) {
                // If branch_name is provided, try to resolve it
                if (isset($imported['branch_name'])) {
                    $branch = Branch::where('name', 'like', "%{$imported['branch_name']}%")->first();
                    $importedValue = $branch?->id;
                }
            }

            if ($existingValue == $importedValue) {
                $comparison['matches'][] = [
                    'field' => $field,
                    'value' => $existingValue,
                ];
            } else {
                $comparison['differences'][] = [
                    'field' => $field,
                    'existing' => $existingValue,
                    'imported' => $importedValue,
                ];
            }
        }

        return $comparison;
    }

    /**
     * Add an error to the errors array.
     */
    private function addError(int $row, string $type, string|array $message): void
    {
        if (is_array($message)) {
            $this->errors[] = array_merge([
                'row' => $row,
                'type' => $type,
            ], $message);
        } else {
            $this->errors[] = [
                'row' => $row,
                'type' => $type,
                'message' => $message,
            ];
        }
    }

    /**
     * Get import results.
     */
    public function getResults(): array
    {
        return [
            'success_count' => $this->successCount,
            'failure_count' => $this->failureCount,
            'errors' => $this->errors,
        ];
    }

    /**
     * Validation rules for the import.
     */
    public function rules(): array
    {
        return [
            '*.name' => 'required_without_all:*.first_name,*.last_name|string|max:255',
            '*.first_name' => 'required_without:*.name|string|max:255',
            '*.last_name' => 'required_without:*.name|string|max:255',
            '*.email' => 'nullable|email|max:255',
            '*.phone' => 'nullable|max:20',
            '*.gender' => 'nullable|in:male,female,prefer-not-to-say',
            '*.marital_status' => 'nullable|in:single,married,divorced,separated,widowed,in_a_relationship,engaged',
            '*.date_of_birth' => 'nullable|date|before:today',
            '*.member_status' => 'nullable|in:visitor,member,volunteer,leader,minister',
            '*.teci_status' => 'nullable|in:not_started,100_level,200_level,300_level,400_level,500_level,graduated,paused',
            '*.growth_level' => 'nullable|in:core,pastor,growing,new_believer',
            // Guest form fields
            '*.preferred_call_time' => 'nullable|in:anytime,morning,afternoon,evening',
            '*.home_address' => 'nullable|string|max:1000',
            '*.age_group' => 'nullable|in:15-20,21-25,26-30,31-35,36-40,above-40',
            '*.prayer_request' => 'nullable|string|max:2000',
            '*.discovery_source' => 'nullable|in:social-media,word-of-mouth,billboard,email,website,promotional-material,radio-tv,outreach',
            '*.staying_intention' => 'nullable|in:yes-for-sure,visit-when-in-town,just-visiting,weighing-options',
            '*.closest_location' => 'nullable|string|max:255',
            '*.additional_info' => 'nullable|string|max:2000',
            '*.leadership_trainings' => 'nullable|string',
        ];
    }

    /**
     * Custom validation messages for the import.
     */
    public function customValidationMessages(): array
    {
        return [
            '*.name.required_without_all' => 'Either a full name or both first name and last name are required.',
            '*.first_name.required_without' => 'First name is required when full name is not provided.',
            '*.last_name.required_without' => 'Last name is required when full name is not provided.',
            '*.email.email' => 'Please provide a valid email address.',
            '*.gender.in' => 'Gender must be male, female, or prefer-not-to-say.',
            '*.marital_status.in' => 'Marital status must be a valid option.',
            '*.date_of_birth.before' => 'Date of birth must be in the past.',
            '*.teci_status.in' => 'TECI status must be a valid level.',
            '*.growth_level.in' => 'Growth level must be a valid level.',
            '*.member_status.in' => 'Member status must be a valid status.',
            '*.preferred_call_time.in' => 'Preferred call time must be anytime, morning, afternoon, or evening.',
            '*.age_group.in' => 'Age group must be a valid range.',
            '*.discovery_source.in' => 'Discovery source must be a valid option.',
            '*.staying_intention.in' => 'Staying intention must be a valid option.',
        ];
    }

    /**
     * Batch size for bulk inserts.
     */
    public function batchSize(): int
    {
        return config('import.batch_sizes.members', 50);
    }

    /**
     * Chunk size for reading large files.
     */
    public function chunkSize(): int
    {
        return config('import.chunk_sizes.members', 100);
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function getSuccesses(): array
    {
        return $this->successes;
    }

    public function failures(): array
    {
        return $this->failures;
    }

    /**
     * Handle validation failures from Laravel Excel.
     */
    public function onFailure(\Maatwebsite\Excel\Validators\Failure ...$failures): void
    {
        foreach ($failures as $failure) {
            $this->addError(
                $failure->row(),
                'validation',
                $failure->errors()[0] ?? 'Validation failed'
            );
            $this->failureCount++;
        }
    }

    public function getImportSummary(): array
    {
        return [
            'total_processed' => count($this->successes) + count($this->errors),
            'successful_imports' => count($this->successes),
            'failed_imports' => count($this->errors),
            'success_rate' => count($this->successes) > 0 ?
                round((count($this->successes) / (count($this->successes) + count($this->errors))) * 100, 2) : 0,
            'errors' => $this->errors,
            'successes' => $this->successes,
            'account_setup_emails_scheduled' => count($this->usersForAccountSetup),
        ];
    }

    /**
     * Generate a secure temporary password.
     */
    private function generateTemporaryPassword(): string
    {
        return 'Church'.rand(1000, 9999);
    }

    /**
     * Get the temporary password for a user (stored during creation).
     */
    private function getTemporaryPassword(User $user): string
    {
        return $user->temporary_password ?? $this->generateTemporaryPassword();
    }

    /**
     * Send account setup emails (password reset links) to all imported users.
     */
    public function sendAccountSetupEmails(): void
    {
        if (empty($this->usersForAccountSetup)) {
            Log::info('No account setup emails to send - no users were created during import');

            return;
        }

        try {
            $branch = Branch::find($this->branchId);
            if (! $branch) {
                Log::error('Branch not found for account setup emails', ['branch_id' => $this->branchId]);

                return;
            }

            // Dispatch bulk account setup email job
            \App\Jobs\SendBulkAccountSetupEmailsJob::dispatch(
                $branch,
                $this->usersForAccountSetup,
                5, // Process 5 emails at a time
                30 // 30 seconds between batches
            );

            Log::info('Account setup emails scheduled for imported members', [
                'branch_id' => $this->branchId,
                'users_count' => count($this->usersForAccountSetup),
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to schedule account setup emails', [
                'branch_id' => $this->branchId,
                'users_count' => count($this->usersForAccountSetup),
                'error' => $e->getMessage(),
            ]);
        }
    }
}
