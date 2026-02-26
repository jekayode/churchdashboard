<?php

declare(strict_types=1);

namespace App\Services;

use App\Imports\MembersImport;
use App\Models\Branch;
use App\Models\GuestFollowUp;
use App\Models\Member;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

final class GuestManagementService
{
    /**
     * Get paginated guest list with filtering.
     */
    public function getGuests(?int $branchId = null, array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Member::guests()
            ->with(['branch:id,name', 'user:id,name,email'])
            ->orderBy('created_at', 'desc');

        // Apply branch filter
        if ($branchId !== null) {
            $query->where('branch_id', $branchId);
        }

        // Filter by date range (newly registered)
        if (! empty($filters['date_from'])) {
            $query->where('created_at', '>=', Carbon::parse($filters['date_from'])->startOfDay());
        }

        if (! empty($filters['date_to'])) {
            $query->where('created_at', '<=', Carbon::parse($filters['date_to'])->endOfDay());
        }

        // Filter by staying intention
        if (! empty($filters['staying_intention'])) {
            $query->where('staying_intention', $filters['staying_intention']);
        }

        // Filter by discovery source
        if (! empty($filters['discovery_source'])) {
            $query->where('discovery_source', $filters['discovery_source']);
        }

        // Filter by gender
        if (! empty($filters['gender'])) {
            $query->where('gender', $filters['gender']);
        }

        // Search by name, email, or phone
        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('first_name', 'like', "%{$search}%")
                    ->orWhere('surname', 'like', "%{$search}%");
            });
        }

        return $query->paginate($perPage);
    }

    /**
     * Get full guest details with relationships.
     */
    public function getGuestDetails(int $memberId): ?Member
    {
        return Member::guests()
            ->with([
                'branch:id,name',
                'user:id,name,email,phone',
                'followUps' => function ($query) {
                    $query->orderBy('contact_date', 'desc')
                        ->with(['createdBy:id,name', 'assignedTo:id,name']);
                },
                'statusHistory' => function ($query) {
                    $query->orderBy('changed_at', 'desc')
                        ->with(['changedBy:id,name']);
                },
                'prayerRequests' => function ($query) {
                    $query->orderBy('created_at', 'desc');
                },
            ])
            ->find($memberId);
    }

    /**
     * Update guest status using the existing changeStatus method.
     */
    public function updateGuestStatus(Member $member, string $newStatus, ?string $reason = null, ?string $notes = null): bool
    {
        // Validate that member is a guest
        if ($member->member_status !== 'visitor' || $member->registration_source !== 'guest-form') {
            return false;
        }

        // Validate that new status is not 'visitor'
        if ($newStatus === 'visitor') {
            return false;
        }

        // Use the existing changeStatus method
        return $member->changeStatus(
            $newStatus,
            $reason,
            $notes,
            auth()->id()
        );
    }

    /**
     * Add a follow-up note for a guest.
     */
    public function addFollowUp(Member $member, array $data): GuestFollowUp
    {
        // Validate that member is a guest
        if ($member->member_status !== 'visitor' || $member->registration_source !== 'guest-form') {
            throw new \InvalidArgumentException('Member is not a guest');
        }

        // Set created_by to current user
        $data['member_id'] = $member->id;
        $data['created_by'] = auth()->id();
        $data['updated_by'] = auth()->id();

        // Parse dates if provided
        if (! empty($data['contact_date']) && is_string($data['contact_date'])) {
            $data['contact_date'] = Carbon::parse($data['contact_date']);
        }

        if (! empty($data['next_follow_up_date']) && is_string($data['next_follow_up_date'])) {
            $data['next_follow_up_date'] = Carbon::parse($data['next_follow_up_date']);
        }

        return GuestFollowUp::create($data);
    }

    /**
     * Export guests data to CSV or Excel.
     */
    public function exportGuests(?int $branchId, array $filters, string $format = 'csv'): Collection
    {
        $query = Member::guests()
            ->with(['branch:id,name', 'user:id,name,email'])
            ->withCount('followUps')
            ->orderBy('created_at', 'desc');

        // Apply branch filter
        if ($branchId !== null) {
            $query->where('branch_id', $branchId);
        }

        // Apply same filters as getGuests
        if (! empty($filters['date_from'])) {
            $query->where('created_at', '>=', Carbon::parse($filters['date_from'])->startOfDay());
        }

        if (! empty($filters['date_to'])) {
            $query->where('created_at', '<=', Carbon::parse($filters['date_to'])->endOfDay());
        }

        if (! empty($filters['staying_intention'])) {
            $query->where('staying_intention', $filters['staying_intention']);
        }

        if (! empty($filters['discovery_source'])) {
            $query->where('discovery_source', $filters['discovery_source']);
        }

        if (! empty($filters['gender'])) {
            $query->where('gender', $filters['gender']);
        }

        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('first_name', 'like', "%{$search}%")
                    ->orWhere('surname', 'like', "%{$search}%");
            });
        }

        return $query->get();
    }

    /**
     * Get export data formatted for CSV/Excel.
     */
    public function formatExportData(Collection $guests): array
    {
        return $guests->map(function ($guest) {
            return [
                'Name' => $guest->name,
                'Email' => $guest->email ?? '',
                'Phone' => $guest->phone ?? '',
                'Branch' => $guest->branch?->name ?? 'Unknown',
                'Registration Date' => $guest->created_at?->format('Y-m-d H:i:s') ?? '',
                'Gender' => $guest->gender ? ucfirst($guest->gender) : '',
                'Age Group' => $guest->age_group ?? '',
                'Marital Status' => $guest->marital_status ? ucfirst(str_replace('_', ' ', $guest->marital_status)) : '',
                'Discovery Source' => $guest->discovery_source ?? '',
                'Staying Intention' => $guest->staying_intention ?? '',
                'Prayer Request' => $guest->prayer_request ?? '',
                'Status' => ucfirst($guest->member_status),
                'Follow-up Count' => $guest->follow_ups_count ?? 0,
                'Home Address' => $guest->home_address ?? '',
                'Preferred Call Time' => $guest->preferred_call_time ?? '',
                'Closest Location' => $guest->closest_location ?? '',
                'Additional Info' => $guest->additional_info ?? '',
            ];
        })->toArray();
    }

    /**
     * Import guests from an uploaded Excel/CSV file.
     */
    public function importGuests(UploadedFile $file, int $branchId): array
    {
        try {
            // Increase execution time and memory limits for large imports
            set_time_limit(config('import.timeout', 300));
            ini_set('memory_limit', config('import.memory_limit', '512M'));

            // Validate file type
            $allowedExtensions = config('import.allowed_extensions', ['xlsx', 'xls', 'csv']);
            $extension = $file->getClientOriginalExtension();

            if (! in_array(strtolower($extension), $allowedExtensions)) {
                throw new \InvalidArgumentException('Invalid file type. Only Excel (.xlsx, .xls) and CSV files are allowed.');
            }

            // Validate file size (max 10MB)
            if ($file->getSize() > 10 * 1024 * 1024) {
                throw new \InvalidArgumentException('File size too large. Maximum file size is 10MB.');
            }

            // Store file temporarily
            $filePath = $file->store('imports', 'local');

            Log::info('Starting guest import', [
                'file_name' => $file->getClientOriginalName(),
                'file_size' => $file->getSize(),
                'branch_id' => $branchId,
            ]);

            // Create import instance with 'guest-form' registration source and 'visitor' status so they appear in guest list
            $import = new MembersImport($branchId, 'guest-form', 'visitor');

            // Execute import
            Excel::import($import, $filePath, 'local');

            // Get import results
            $summary = $import->getImportSummary();

            // Send account setup emails to imported users
            $import->sendAccountSetupEmails();

            // Clean up temporary file
            Storage::disk('local')->delete($filePath);

            Log::info('Guest import completed', [
                'branch_id' => $branchId,
                'total_processed' => $summary['total_processed'],
                'successful' => $summary['successful_imports'],
                'failed' => $summary['failed_imports'],
                'account_setup_emails_scheduled' => $summary['account_setup_emails_scheduled'] ?? 0,
            ]);

            // Determine success based on whether there were any failures
            $success = $summary['failed_imports'] === 0;
            $message = $success
                ? 'Guest import completed successfully'
                : "Guest import completed with {$summary['failed_imports']} errors out of {$summary['total_processed']} total rows";

            // Format error details for frontend display
            $details = null;
            if (! $success && isset($summary['errors']) && count($summary['errors']) > 0) {
                $details = $this->formatErrorDetails($summary['errors']);
            }

            return [
                'success' => $success,
                'message' => $message,
                'summary' => $summary,
                'details' => $details,
            ];

        } catch (\Exception $e) {
            Log::error('Guest import failed', [
                'error' => $e->getMessage(),
                'branch_id' => $branchId,
                'file_name' => $file->getClientOriginalName() ?? 'unknown',
            ]);

            // Clean up file if it exists
            if (isset($filePath)) {
                Storage::disk('local')->delete($filePath);
            }

            return [
                'success' => false,
                'message' => 'Import failed: '.$e->getMessage(),
                'errors' => [$e->getMessage()],
            ];
        }
    }

    /**
     * Format error details for display.
     */
    private function formatErrorDetails(array $errors): array
    {
        $formatted = [];
        foreach ($errors as $error) {
            $formatted[] = [
                'row' => $error['row'] ?? 'Unknown',
                'type' => $error['type'] ?? 'error',
                'message' => $error['message'] ?? 'Unknown error',
            ];
        }

        return $formatted;
    }

    /**
     * Send account setup emails to guests.
     */
    public function sendAccountSetupEmailsToGuests(?int $branchId = null, ?array $memberIds = null): array
    {
        $query = Member::guests()
            ->whereHas('user')
            ->with(['user:id,email,name', 'branch:id,name']);

        // Apply branch filter
        if ($branchId !== null) {
            $query->where('branch_id', $branchId);
        }

        // Filter by specific member IDs if provided
        if ($memberIds !== null && count($memberIds) > 0) {
            $query->whereIn('id', $memberIds);
        }

        $guests = $query->get();

        $sent = 0;
        $failed = 0;
        $skipped = 0;
        $errors = [];

        foreach ($guests as $guest) {
            try {
                if (! $guest->user) {
                    $skipped++;
                    $errors[] = [
                        'member_id' => $guest->id,
                        'name' => $guest->name,
                        'reason' => 'No user account found',
                    ];

                    continue;
                }

                // Skip temporary email addresses
                if (str_contains($guest->user->email, '@church.local')) {
                    $skipped++;
                    $errors[] = [
                        'member_id' => $guest->id,
                        'name' => $guest->name,
                        'email' => $guest->user->email,
                        'reason' => 'Temporary email address (no real email provided)',
                    ];

                    continue;
                }

                // Dispatch account setup email job
                \App\Jobs\SendAccountSetupEmailJob::dispatch($guest->user);
                $sent++;

            } catch (\Exception $e) {
                $failed++;
                $errors[] = [
                    'member_id' => $guest->id,
                    'name' => $guest->name,
                    'email' => $guest->user?->email,
                    'reason' => $e->getMessage(),
                ];

                Log::error('Failed to queue account setup email for guest', [
                    'member_id' => $guest->id,
                    'user_id' => $guest->user?->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        Log::info('Account setup emails queued for guests', [
            'branch_id' => $branchId,
            'total_guests' => $guests->count(),
            'sent' => $sent,
            'skipped' => $skipped,
            'failed' => $failed,
        ]);

        return [
            'success' => true,
            'total' => $guests->count(),
            'sent' => $sent,
            'skipped' => $skipped,
            'failed' => $failed,
            'errors' => $errors,
        ];
    }

    /**
     * Get guest import template.
     */
    public function getGuestImportTemplate(): array
    {
        try {
            $data = [
                [
                    'name' => 'John Doe',
                    'email' => 'john.doe@example.com',
                    'phone' => '+2348012345678',
                    'gender' => 'male',
                    'date_of_birth' => '1990-01-15',
                    'marital_status' => 'single',
                    'home_address' => '123 Main Street, Lagos',
                    'age_group' => '31-35',
                    'preferred_call_time' => 'evening',
                    'discovery_source' => 'social-media',
                    'staying_intention' => 'yes-for-sure',
                    'closest_location' => 'Lekki',
                    'prayer_request' => 'Pray for my family',
                    'additional_info' => 'First time visitor',
                    'date_joined' => '2025-12-09',
                ],
                [
                    'name' => 'Jane Smith',
                    'email' => 'jane.smith@example.com',
                    'phone' => '+2348023456789',
                    'gender' => 'female',
                    'date_of_birth' => '1985-05-20',
                    'marital_status' => 'married',
                    'home_address' => '456 Oak Avenue, Lagos',
                    'age_group' => '36-40',
                    'preferred_call_time' => 'morning',
                    'discovery_source' => 'word-of-mouth',
                    'staying_intention' => 'visit-when-in-town',
                    'closest_location' => 'Victoria Island',
                    'prayer_request' => '',
                    'additional_info' => '',
                    'date_joined' => '2025-12-09',
                ],
                [
                    'name' => 'Michael Johnson',
                    'email' => '',
                    'phone' => '+2348034567890',
                    'gender' => 'male',
                    'date_of_birth' => '',
                    'marital_status' => 'single',
                    'home_address' => '789 Pine Street, Lagos',
                    'age_group' => '26-30',
                    'preferred_call_time' => 'anytime',
                    'discovery_source' => 'website',
                    'staying_intention' => 'weighing-options',
                    'closest_location' => 'Ikoyi',
                    'prayer_request' => 'Need guidance',
                    'additional_info' => 'Interested in joining',
                    'date_joined' => '2025-12-09',
                ],
            ];

            // Create filename with timestamp
            $filename = 'guest_import_template_'.now()->format('Y-m-d_H-i-s').'.xlsx';
            $filePath = 'exports/'.$filename;
            $fullPath = storage_path('app/public/'.$filePath);

            // Ensure directory exists
            $directory = dirname($fullPath);
            if (! is_dir($directory)) {
                mkdir($directory, 0755, true);
            }

            // Create Excel file
            $export = new class($data) implements \Maatwebsite\Excel\Concerns\FromCollection, \Maatwebsite\Excel\Concerns\WithHeadings
            {
                private array $data;

                public function __construct($data)
                {
                    $this->data = $data;
                }

                public function collection()
                {
                    return collect($this->data);
                }

                public function headings(): array
                {
                    return [
                        'Name',
                        'Email',
                        'Phone',
                        'Gender',
                        'Date of Birth',
                        'Marital Status',
                        'Home Address',
                        'Age Group',
                        'Preferred Call Time',
                        'Discovery Source',
                        'Staying Intention',
                        'Closest Location',
                        'Prayer Request',
                        'Additional Info',
                        'Date Joined',
                    ];
                }
            };

            Excel::store($export, $filePath, 'public');

            return [
                'success' => true,
                'file_path' => $filePath,
                'filename' => 'guest_import_template.xlsx',
                'message' => 'Template generated successfully',
            ];

        } catch (\Exception $e) {
            Log::error('Guest template generation failed', ['error' => $e->getMessage()]);

            return [
                'success' => false,
                'message' => 'Failed to generate guest import template',
            ];
        }
    }
}
