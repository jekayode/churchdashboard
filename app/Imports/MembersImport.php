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
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Carbon\Carbon;

final class MembersImport implements ToCollection, WithHeadingRow, WithValidation, WithBatchInserts, WithChunkReading, SkipsOnFailure
{
    use \Maatwebsite\Excel\Concerns\Importable;

    private int $branchId;
    private array $errors = [];
    private array $successes = [];
    private int $successCount = 0;
    private int $failureCount = 0;

    public function __construct(int $branchId)
    {
        $this->branchId = $branchId;
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
        // Clean and validate data
        $data = $this->cleanRowData($row);
        
        $validator = Validator::make($data, $this->getRowValidationRules());
        
        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->addError($rowNumber, 'validation', $error);
            }
            $this->failureCount++;
            return;
        }

        // Check if member already exists (by email if provided, otherwise by name and phone)
        // Use select to optimize query performance
        $existingMember = null;
        
        if (!empty($data['email'])) {
            $existingMember = Member::select('id')->where('email', $data['email'])->first();
        } elseif (!empty($data['phone'])) {
            $existingMember = Member::select('id')
                ->where('name', $data['name'])
                ->where('phone', $data['phone'])
                ->first();
        }

        if ($existingMember) {
            $identifier = $data['email'] ?? $data['name'];
            $this->addError($rowNumber, 'duplicate', "Member already exists: {$identifier}");
            $this->failureCount++;
            return;
        }

        // Create or find user account (only if email is provided)
        $user = $this->createOrFindUser($data);
        
        // Note: user can be null if no email is provided, which is acceptable
        
        // Create member record
        $memberData = $this->prepareMemberData($data, $user?->id);
        $member = Member::create($memberData);

        // Assign member role to user (only if user account exists)
        if ($user) {
            $memberRole = Role::where('name', 'church_member')->first();
            if ($memberRole && !$user->roles()->where('role_id', $memberRole->id)->exists()) {
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
        
        // Map column headers to database fields
        $fieldMap = [
            'name' => ['name', 'full_name', 'member_name'],
            'first_name' => ['first_name', 'firstname', 'fname'],
            'last_name' => ['last_name', 'lastname', 'lname', 'surname'],
            'email' => ['email', 'email_address'],
            'phone' => ['phone', 'phone_number', 'mobile'],
            'date_of_birth' => ['date_of_birth', 'dob', 'birth_date'],
            'anniversary' => ['anniversary', 'wedding_anniversary'],
            'gender' => ['gender', 'sex'],
            'marital_status' => ['marital_status', 'marriage_status'],
            'occupation' => ['occupation', 'job', 'profession'],
            'nearest_bus_stop' => ['nearest_bus_stop', 'bus_stop'],
            'date_joined' => ['date_joined', 'join_date', 'membership_date'],
            'date_attended_membership_class' => ['date_attended_membership_class', 'membership_class_date'],
            'teci_status' => ['teci_status', 'teci'],
            'growth_level' => ['growth_level', 'level'],
            'member_status' => ['member_status', 'status'],
            'branch_name' => ['branch_name', 'branch', 'church_branch'],
        ];

        foreach ($fieldMap as $dbField => $possibleHeaders) {
            foreach ($possibleHeaders as $header) {
                if (isset($row[$header]) && !empty($row[$header])) {
                    $value = $row[$header];
                    
                    // Convert phone numbers to string
                    if ($dbField === 'phone') {
                        $value = (string) $value;
                    }
                    
                    $cleanData[$dbField] = trim($value);
                    break;
                }
            }
        }

        // Handle combining first_name and last_name into name if name is not already set
        if (!isset($cleanData['name']) && (isset($cleanData['first_name']) || isset($cleanData['last_name']))) {
            $firstName = trim($cleanData['first_name'] ?? '');
            $lastName = trim($cleanData['last_name'] ?? '');
            
            if ($firstName || $lastName) {
                $cleanData['name'] = trim($firstName . ' ' . $lastName);
            }
        }
        
        // Remove first_name and last_name since we only need the combined name
        unset($cleanData['first_name'], $cleanData['last_name']);

        // Parse leadership trainings if present
        if (isset($row['leadership_trainings']) && !empty($row['leadership_trainings'])) {
            $trainings = explode(',', $row['leadership_trainings']);
            $cleanData['leadership_trainings'] = array_map('trim', $trainings);
        }

        // Normalize gender
        if (isset($cleanData['gender'])) {
            $cleanData['gender'] = strtolower($cleanData['gender']) === 'female' ? 'female' : 'male';
        }

        // Normalize marital status
        if (isset($cleanData['marital_status'])) {
            $status = strtolower($cleanData['marital_status']);
            $cleanData['marital_status'] = match($status) {
                'married', 'wed' => 'married',
                'single', 'unmarried' => 'single',
                'divorced' => 'divorced',
                'separated' => 'separated',
                'widowed', 'widow', 'widower' => 'widowed',
                'in a relationship', 'relationship', 'dating' => 'in_a_relationship',
                'engaged', 'engagement' => 'engaged',
                default => 'single'
            };
        }

        // Parse dates
        foreach (['date_of_birth', 'anniversary', 'date_joined', 'date_attended_membership_class'] as $dateField) {
            if (isset($cleanData[$dateField])) {
                $cleanData[$dateField] = $this->parseDate($cleanData[$dateField]);
            }
        }

        return array_filter($cleanData, fn($value) => !is_null($value) && $value !== '');
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
     * Get validation rules for each row.
     */
    private function getRowValidationRules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'date_of_birth' => 'nullable|date',
            'anniversary' => 'nullable|date',
            'gender' => 'nullable|in:male,female',
            'marital_status' => 'nullable|in:single,married,divorced,separated,widowed,in_a_relationship,engaged',
            'occupation' => 'nullable|string|max:255',
            'nearest_bus_stop' => 'nullable|string|max:255',
            'date_joined' => 'nullable|date',
            'date_attended_membership_class' => 'nullable|date',
            'teci_status' => 'nullable|in:not_started,100_level,200_level,300_level,400_level,500_level,graduated,paused',
            'growth_level' => 'nullable|in:core,pastor,growing,new_believer',
            'member_status' => 'nullable|in:visitor,member,volunteer,leader,minister',
            'branch_name' => $this->branchId ? 'nullable' : 'required|string',
        ];
    }

    /**
     * Create or find existing user account.
     */
    private function createOrFindUser(array $data): ?User
    {
        // If no email provided, skip user account creation
        if (empty($data['email'])) {
            return null;
        }
        
        // Check if user already exists (optimize query)
        $user = User::select('id', 'email')->where('email', $data['email'])->first();
        
        if ($user) {
            return $user;
        }

        // Create new user account
        try {
            $password = 'Church' . rand(1000, 9999);
            
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'] ?? null,
                'password' => Hash::make($password),
                'email_verified_at' => now(), // Auto-verify church members
            ]);

            Log::info('User account created for member import', [
                'user_id' => $user->id,
                'email' => $user->email,
                'temporary_password' => $password,
            ]);

            return $user;
        } catch (\Exception $e) {
            Log::error('Failed to create user account during member import', [
                'email' => $data['email'],
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
        if (!$branchId && isset($data['branch_name'])) {
            $branch = Branch::where('name', 'like', "%{$data['branch_name']}%")->first();
            $branchId = $branch?->id;
        }

        // Default to first branch if none found
        if (!$branchId) {
            $branchId = Branch::first()?->id;
        }

        return [
            'user_id' => $userId, // Can be null if no email provided
            'branch_id' => $branchId,
            'name' => $data['name'],
            'email' => $data['email'] ?? null,
            'phone' => $data['phone'] ?? null,
            'date_of_birth' => $data['date_of_birth'] ?? null,
            'anniversary' => $data['anniversary'] ?? null,
            'gender' => $data['gender'] ?? null,
            'marital_status' => $data['marital_status'] ?? 'single',
            'occupation' => $data['occupation'] ?? null,
            'nearest_bus_stop' => $data['nearest_bus_stop'] ?? null,
            'date_joined' => $data['date_joined'] ?? now()->format('Y-m-d'),
            'date_attended_membership_class' => $data['date_attended_membership_class'] ?? null,
            'teci_status' => $data['teci_status'] ?? 'not_started',
            'growth_level' => $data['growth_level'] ?? 'new_believer',
            'leadership_trainings' => $data['leadership_trainings'] ?? [],
            'member_status' => $data['member_status'] ?? 'member',
        ];
    }

    /**
     * Add an error to the errors array.
     */
    private function addError(int $row, string $type, string $message): void
    {
        $this->errors[] = [
            'row' => $row,
            'type' => $type,
            'message' => $message,
        ];
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
            '*.gender' => 'nullable|in:male,female',
            '*.date_of_birth' => 'nullable|date|before:today',
            '*.member_status' => 'nullable|in:visitor,member,volunteer,leader,minister',
            '*.teci_status' => 'nullable|in:not_started,100_level,200_level,300_level,400_level,500_level,graduated,paused',
            '*.growth_level' => 'nullable|in:core,pastor,growing,new_believer',
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
            '*.gender.in' => 'Gender must be either male or female.',
            '*.date_of_birth.before' => 'Date of birth must be in the past.',
            '*.teci_status.in' => 'TECI status must be a valid level.',
            '*.growth_level.in' => 'Growth level must be a valid level.',
            '*.member_status.in' => 'Member status must be a valid status.',
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
            'successes' => $this->successes
        ];
    }
} 