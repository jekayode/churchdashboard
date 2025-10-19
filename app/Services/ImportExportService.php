<?php

declare(strict_types=1);

namespace App\Services;

use App\Exports\MembersExport;
use App\Imports\MembersImport;
use App\Models\Branch;
use App\Models\Department;
use App\Models\Event;
use App\Models\Member;
use App\Models\Ministry;
use App\Models\Projection;
use App\Models\SmallGroup;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

final class ImportExportService
{
    /**
     * Import members from an uploaded Excel/CSV file.
     */
    public function importMembers(UploadedFile $file, int $branchId): array
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

            Log::info('Starting member import', [
                'file_name' => $file->getClientOriginalName(),
                'file_size' => $file->getSize(),
                'branch_id' => $branchId,
            ]);

            // Create import instance
            $import = new MembersImport($branchId);

            // Execute import
            Excel::import($import, $filePath, 'local');

            // Get import results
            $summary = $import->getImportSummary();

            // Send welcome emails to imported users
            $import->sendWelcomeEmails();

            // Clean up temporary file
            Storage::disk('local')->delete($filePath);

            Log::info('Member import completed', [
                'branch_id' => $branchId,
                'total_processed' => $summary['total_processed'],
                'successful' => $summary['successful_imports'],
                'failed' => $summary['failed_imports'],
                'welcome_emails_scheduled' => $summary['welcome_emails_scheduled'] ?? 0,
            ]);

            // Determine success based on whether there were any failures
            $success = $summary['failed_imports'] === 0;
            $message = $success
                ? 'Import completed successfully'
                : "Import completed with {$summary['failed_imports']} errors out of {$summary['total_processed']} total rows";

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
            Log::error('Member import failed', [
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
     * Import ministries from an uploaded Excel/CSV file.
     */
    public function importMinistries(UploadedFile $file, int $branchId): array
    {
        try {
            // Validate file type
            $allowedExtensions = ['xlsx', 'xls', 'csv'];
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

            Log::info('Starting ministry import', [
                'file_name' => $file->getClientOriginalName(),
                'file_size' => $file->getSize(),
                'branch_id' => $branchId,
            ]);

            // Create import instance
            $import = new \App\Imports\MinistriesImport($branchId);

            // Execute import
            Excel::import($import, $filePath, 'local');

            // Get import results
            $summary = $import->getImportSummary();

            // Clean up temporary file
            Storage::disk('local')->delete($filePath);

            Log::info('Ministry import completed', [
                'branch_id' => $branchId,
                'total_processed' => $summary['total_processed'],
                'successful' => $summary['successful_imports'],
                'failed' => $summary['failed_imports'],
            ]);

            return [
                'success' => true,
                'message' => 'Import completed successfully',
                'summary' => $summary,
            ];

        } catch (\Exception $e) {
            Log::error('Ministry import failed', [
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
     * Import departments from an uploaded Excel/CSV file.
     */
    public function importDepartments(UploadedFile $file, int $branchId): array
    {
        try {
            // Validate file type
            $allowedExtensions = ['xlsx', 'xls', 'csv'];
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

            Log::info('Starting department import', [
                'file_name' => $file->getClientOriginalName(),
                'file_size' => $file->getSize(),
                'branch_id' => $branchId,
            ]);

            // Create import instance
            $import = new \App\Imports\DepartmentsImport($branchId);

            // Execute import
            Excel::import($import, $filePath, 'local');

            // Get import results
            $summary = $import->getImportSummary();

            // Clean up temporary file
            Storage::disk('local')->delete($filePath);

            Log::info('Department import completed', [
                'branch_id' => $branchId,
                'total_processed' => $summary['total_processed'],
                'successful' => $summary['successful_imports'],
                'failed' => $summary['failed_imports'],
            ]);

            return [
                'success' => true,
                'message' => 'Import completed successfully',
                'summary' => $summary,
            ];

        } catch (\Exception $e) {
            Log::error('Department import failed', [
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
     * Import event reports from an uploaded Excel/CSV file.
     */
    public function importEventReports(UploadedFile $file, int $branchId): array
    {
        try {
            // Increase execution time and memory limits for large imports
            set_time_limit(300); // 5 minutes
            ini_set('memory_limit', '512M');

            // Validate file type
            $allowedExtensions = ['xlsx', 'xls', 'csv'];
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

            Log::info('Starting event reports import', [
                'file_name' => $file->getClientOriginalName(),
                'file_size' => $file->getSize(),
                'branch_id' => $branchId,
            ]);

            // Create import instance
            $import = new \App\Imports\EventReportsImport($branchId);

            // Execute import
            Excel::import($import, $filePath, 'local');

            // Get import results
            $summary = $import->getImportSummary();

            // Clean up temporary file
            Storage::disk('local')->delete($filePath);

            Log::info('Event reports import completed', [
                'branch_id' => $branchId,
                'total_processed' => $summary['total_processed'],
                'successful' => $summary['successful_imports'],
                'failed' => $summary['failed_imports'],
            ]);

            // Determine success based on whether there were any failures
            $success = $summary['failed_imports'] === 0;
            $message = $success
                ? 'Import completed successfully'
                : "Import completed with {$summary['failed_imports']} errors out of {$summary['total_processed']} total rows";

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
            Log::error('Event reports import failed', [
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
     * Import small groups from an uploaded Excel/CSV file.
     */
    public function importSmallGroups(UploadedFile $file, int $branchId): array
    {
        try {
            // Validate file type
            $allowedExtensions = ['xlsx', 'xls', 'csv'];
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

            Log::info('Starting small group import', [
                'file_name' => $file->getClientOriginalName(),
                'file_size' => $file->getSize(),
                'branch_id' => $branchId,
            ]);

            // Create import instance
            $import = new \App\Imports\SmallGroupsImport($branchId);

            // Execute import
            Excel::import($import, $filePath, 'local');

            // Get import results
            $summary = $import->getImportSummary();

            // Clean up temporary file
            Storage::disk('local')->delete($filePath);

            Log::info('Small group import completed', [
                'branch_id' => $branchId,
                'total_processed' => $summary['total_processed'],
                'successful' => $summary['successful_imports'],
                'failed' => $summary['failed_imports'],
            ]);

            return [
                'success' => true,
                'message' => 'Import completed successfully',
                'summary' => $summary,
            ];

        } catch (\Exception $e) {
            Log::error('Small group import failed', [
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
     * Export members to Excel file.
     */
    public function exportMembers(?int $branchId = null, array $filters = []): array
    {
        try {
            Log::info('Starting member export', [
                'branch_id' => $branchId,
                'filters' => $filters,
            ]);

            // Create export instance
            $export = new MembersExport($branchId, $filters);

            // Generate filename
            $filename = MembersExport::getExportFilename($branchId, $filters);

            // Store export file
            $filePath = 'exports/'.$filename;
            Excel::store($export, $filePath, 'public');

            // Get export summary
            $summary = $export->getExportSummary();

            Log::info('Member export completed', [
                'branch_id' => $branchId,
                'filename' => $filename,
                'total_members' => $summary['total_members'],
            ]);

            return [
                'success' => true,
                'message' => 'Export completed successfully',
                'filename' => $filename,
                'file_path' => $filePath,
                'download_url' => Storage::disk('public')->url($filePath),
                'summary' => $summary,
            ];

        } catch (\Exception $e) {
            Log::error('Member export failed', [
                'error' => $e->getMessage(),
                'branch_id' => $branchId,
                'filters' => $filters,
            ]);

            return [
                'success' => false,
                'message' => 'Export failed: '.$e->getMessage(),
                'errors' => [$e->getMessage()],
            ];
        }
    }

    /**
     * Export branches to Excel file.
     */
    public function exportBranches(): string
    {
        try {
            $branches = Branch::with(['members', 'ministries'])
                ->select([
                    'id',
                    'name',
                    'address',
                    'city',
                    'state',
                    'phone',
                    'email',
                    'pastor_name',
                    'created_at',
                    'updated_at',
                ])
                ->get();

            $data = $branches->map(function ($branch) {
                return [
                    'ID' => $branch->id,
                    'Name' => $branch->name,
                    'Address' => $branch->address,
                    'City' => $branch->city,
                    'State' => $branch->state,
                    'Phone' => $branch->phone,
                    'Email' => $branch->email,
                    'Pastor Name' => $branch->pastor_name,
                    'Total Members' => $branch->members->count(),
                    'Total Ministries' => $branch->ministries->count(),
                    'Created Date' => $branch->created_at?->format('Y-m-d H:i:s'),
                    'Last Updated' => $branch->updated_at?->format('Y-m-d H:i:s'),
                ];
            });

            return $this->createExcelFromCollection($data, 'branches');
        } catch (\Exception $e) {
            Log::error('Branches export failed', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Export ministries to Excel file.
     */
    public function exportMinistries(?int $branchId = null): string
    {
        try {
            $query = Ministry::with(['branch', 'departments', 'leader']);

            if ($branchId) {
                $query->where('branch_id', $branchId);
            }

            $ministries = $query->select([
                'id',
                'branch_id',
                'name',
                'description',
                'leader_id',
                'status',
                'created_at',
                'updated_at',
            ])->get();

            $data = $ministries->map(function ($ministry) {
                return [
                    'ID' => $ministry->id,
                    'Branch' => $ministry->branch?->name ?? '',
                    'Name' => $ministry->name,
                    'Description' => $ministry->description,
                    'Leader Name' => $ministry->leader?->name ?? '',
                    'Leader Email' => $ministry->leader?->email ?? '',
                    'Leader Phone' => $ministry->leader?->phone ?? '',
                    'Status' => ucfirst($ministry->status ?? 'active'),
                    'Total Departments' => $ministry->departments->count(),
                    'Created Date' => $ministry->created_at?->format('Y-m-d H:i:s'),
                    'Last Updated' => $ministry->updated_at?->format('Y-m-d H:i:s'),
                ];
            });

            return $this->createExcelFromCollection($data, 'ministries', $branchId);
        } catch (\Exception $e) {
            Log::error('Ministries export failed', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Export departments to Excel file.
     */
    public function exportDepartments(?int $branchId = null): string
    {
        try {
            $query = Department::with(['ministry.branch', 'leader', 'members']);

            if ($branchId) {
                $query->whereHas('ministry', function ($q) use ($branchId) {
                    $q->where('branch_id', $branchId);
                });
            }

            $departments = $query->select([
                'id',
                'ministry_id',
                'name',
                'description',
                'leader_id',
                'created_at',
                'updated_at',
            ])->get();

            $data = $departments->map(function ($department) {
                return [
                    'ID' => $department->id,
                    'Branch' => $department->ministry?->branch?->name ?? '',
                    'Ministry' => $department->ministry?->name ?? '',
                    'Name' => $department->name,
                    'Description' => $department->description,
                    'Leader Name' => $department->leader?->name ?? '',
                    'Leader Email' => $department->leader?->email ?? '',
                    'Leader Phone' => $department->leader?->phone ?? '',
                    'Total Members' => $department->members->count(),
                    'Created Date' => $department->created_at?->format('Y-m-d H:i:s'),
                    'Last Updated' => $department->updated_at?->format('Y-m-d H:i:s'),
                ];
            });

            return $this->createExcelFromCollection($data, 'departments', $branchId);
        } catch (\Exception $e) {
            Log::error('Departments export failed', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Export small groups to Excel file.
     */
    public function exportSmallGroups(?int $branchId = null): string
    {
        try {
            $query = SmallGroup::with(['branch', 'leader', 'members']);

            if ($branchId) {
                $query->where('branch_id', $branchId);
            }

            $smallGroups = $query->select([
                'id',
                'branch_id',
                'name',
                'description',
                'location',
                'meeting_day',
                'meeting_time',
                'leader_id',
                'status',
                'created_at',
                'updated_at',
            ])->get();

            $data = $smallGroups->map(function ($group) {
                return [
                    'ID' => $group->id,
                    'Branch' => $group->branch?->name ?? '',
                    'Name' => $group->name,
                    'Description' => $group->description,
                    'Location' => $group->location,
                    'Meeting Day' => $group->meeting_day,
                    'Meeting Time' => $group->meeting_time,
                    'Leader Name' => $group->leader?->name ?? '',
                    'Leader Email' => $group->leader?->email ?? '',
                    'Leader Phone' => $group->leader?->phone ?? '',
                    'Status' => ucfirst($group->status ?? 'active'),
                    'Current Members' => $group->members->count(),
                    'Created Date' => $group->created_at?->format('Y-m-d H:i:s'),
                    'Last Updated' => $group->updated_at?->format('Y-m-d H:i:s'),
                ];
            });

            return $this->createExcelFromCollection($data, 'small-groups', $branchId);
        } catch (\Exception $e) {
            Log::error('Small groups export failed', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Export events to Excel file.
     */
    public function exportEvents(?int $branchId = null, array $filters = []): string
    {
        try {
            $query = Event::with(['branch', 'registrations']);

            if ($branchId) {
                $query->where('branch_id', $branchId);
            }

            // Apply date filters
            if (! empty($filters['start_date'])) {
                $query->whereDate('event_date', '>=', $filters['start_date']);
            }

            if (! empty($filters['end_date'])) {
                $query->whereDate('event_date', '<=', $filters['end_date']);
            }

            if (! empty($filters['event_type'])) {
                $query->where('event_type', $filters['event_type']);
            }

            $events = $query->select([
                'id',
                'branch_id',
                'title',
                'description',
                'event_type',
                'event_date',
                'start_time',
                'end_time',
                'location',
                'max_attendees',
                'registration_fee',
                'created_at',
                'updated_at',
            ])->orderBy('event_date', 'desc')->get();

            $data = $events->map(function ($event) {
                return [
                    'ID' => $event->id,
                    'Branch' => $event->branch?->name ?? '',
                    'Title' => $event->title,
                    'Description' => $event->description,
                    'Event Type' => ucfirst($event->event_type ?? ''),
                    'Event Date' => $event->event_date?->format('Y-m-d'),
                    'Start Time' => $event->start_time,
                    'End Time' => $event->end_time,
                    'Location' => $event->location,
                    'Max Attendees' => $event->max_attendees,
                    'Registration Fee' => $event->registration_fee ? number_format($event->registration_fee, 2) : '0.00',
                    'Total Registrations' => $event->registrations->count(),
                    'Available Spots' => $event->max_attendees ? ($event->max_attendees - $event->registrations->count()) : 'Unlimited',
                    'Created Date' => $event->created_at?->format('Y-m-d H:i:s'),
                    'Last Updated' => $event->updated_at?->format('Y-m-d H:i:s'),
                ];
            });

            return $this->createExcelFromCollection($data, 'events', $branchId);
        } catch (\Exception $e) {
            Log::error('Events export failed', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Export projections to Excel file.
     */
    public function exportProjections(?int $branchId = null, ?int $year = null): string
    {
        try {
            $query = Projection::with(['branch']);

            if ($branchId) {
                $query->where('branch_id', $branchId);
            }

            if ($year) {
                $query->where('year', $year);
            }

            $projections = $query->select([
                'id',
                'branch_id',
                'year',
                'quarter',
                'category',
                'subcategory',
                'description',
                'amount',
                'status',
                'approved_by',
                'approved_at',
                'created_at',
                'updated_at',
            ])->orderBy('year', 'desc')->orderBy('quarter')->get();

            $data = $projections->map(function ($projection) {
                return [
                    'ID' => $projection->id,
                    'Branch' => $projection->branch?->name ?? '',
                    'Year' => $projection->year,
                    'Quarter' => $projection->quarter,
                    'Category' => $projection->category,
                    'Subcategory' => $projection->subcategory,
                    'Description' => $projection->description,
                    'Amount' => number_format($projection->amount, 2),
                    'Status' => ucfirst($projection->status ?? ''),
                    'Approved By' => $projection->approved_by,
                    'Approved Date' => $projection->approved_at?->format('Y-m-d H:i:s'),
                    'Created Date' => $projection->created_at?->format('Y-m-d H:i:s'),
                    'Last Updated' => $projection->updated_at?->format('Y-m-d H:i:s'),
                ];
            });

            return $this->createExcelFromCollection($data, 'projections', $branchId);
        } catch (\Exception $e) {
            Log::error('Projections export failed', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Create Excel file from collection data.
     */
    private function createExcelFromCollection($data, string $entityType, ?int $branchId = null): string
    {
        $filename = $this->generateExportFilename($entityType, $branchId);
        $filePath = "exports/{$filename}";

        // Convert to array format expected by Excel
        $arrayData = [];

        if ($data->isNotEmpty()) {
            // Add headers
            $arrayData[] = array_keys($data->first());

            // Add data rows
            foreach ($data as $row) {
                $arrayData[] = array_values($row);
            }
        }

        // Create simple Excel export
        Excel::store(new class($arrayData) implements \Maatwebsite\Excel\Concerns\FromArray
        {
            private array $data;

            public function __construct(array $data)
            {
                $this->data = $data;
            }

            public function array(): array
            {
                return $this->data;
            }
        }, $filePath, 'public');

        return Storage::path($filePath);
    }

    /**
     * Generate export filename.
     */
    private function generateExportFilename(string $entityType, ?int $branchId = null): string
    {
        $timestamp = now()->format('Y-m-d_H-i-s');
        $branchSuffix = $branchId ? "_branch-{$branchId}" : '';

        return "{$entityType}_export{$branchSuffix}_{$timestamp}.xlsx";
    }

    /**
     * Get list of available export types.
     */
    public function getAvailableExports(): array
    {
        return [
            'members' => 'Church Members',
            'branches' => 'Church Branches',
            'ministries' => 'Ministries',
            'departments' => 'Departments',
            'small-groups' => 'Small Groups',
            'events' => 'Events',
            'projections' => 'Financial Projections',
        ];
    }

    /**
     * Get sample template for member import.
     */
    public function getMemberImportTemplate(): array
    {
        try {
            $data = [
                [
                    'first_name' => 'John',
                    'last_name' => 'Doe',
                    'email' => 'john.doe@example.com',
                    'phone' => '+1234567890',
                    'gender' => 'male',
                    'date_of_birth' => '1990-01-15',
                    'address' => '123 Main Street, City, State',
                    'member_status' => 'member',
                    'growth_level' => 'growing',
                    'teci_status' => '200_level',
                    'marital_status' => 'married',
                    'occupation' => 'Software Engineer',
                    'nearest_bus_stop' => 'Central Station',
                    'leadership_trainings' => 'ELP,MLCC',
                ],
                [
                    'first_name' => 'Jane',
                    'last_name' => 'Smith',
                    'email' => 'jane.smith@example.com',
                    'phone' => '+1234567891',
                    'gender' => 'female',
                    'date_of_birth' => '1985-05-20',
                    'address' => '456 Oak Avenue, City, State',
                    'member_status' => 'volunteer',
                    'growth_level' => 'core',
                    'teci_status' => '300_level',
                    'marital_status' => 'engaged',
                    'occupation' => 'Teacher',
                    'nearest_bus_stop' => 'School Junction',
                    'leadership_trainings' => 'ELP',
                ],
                [
                    'first_name' => 'Michael',
                    'last_name' => 'Johnson',
                    'email' => 'michael.johnson@example.com',
                    'phone' => '+1234567892',
                    'gender' => 'male',
                    'date_of_birth' => '1975-12-10',
                    'address' => '789 Pine Street, City, State',
                    'member_status' => 'leader',
                    'growth_level' => 'pastor',
                    'teci_status' => 'graduated',
                    'marital_status' => 'married',
                    'occupation' => 'Pastor',
                    'nearest_bus_stop' => 'Church Avenue',
                    'leadership_trainings' => 'ELP,MLCC,PLI',
                ],
                [
                    'first_name' => 'Sarah',
                    'last_name' => 'Williams',
                    'email' => 'sarah.williams@example.com',
                    'phone' => '+1234567893',
                    'gender' => 'female',
                    'date_of_birth' => '1992-08-25',
                    'address' => '321 Elm Street, City, State',
                    'member_status' => 'member',
                    'growth_level' => 'growing',
                    'teci_status' => '100_level',
                    'marital_status' => 'in_a_relationship',
                    'occupation' => 'Nurse',
                    'nearest_bus_stop' => 'Hospital Stop',
                    'leadership_trainings' => '',
                ],
                [
                    'first_name' => 'David',
                    'last_name' => 'Brown',
                    'email' => 'david.brown@example.com',
                    'phone' => '+1234567894',
                    'gender' => 'male',
                    'date_of_birth' => '1980-03-15',
                    'address' => '654 Maple Drive, City, State',
                    'member_status' => 'member',
                    'growth_level' => 'core',
                    'teci_status' => '400_level',
                    'marital_status' => 'separated',
                    'occupation' => 'Accountant',
                    'nearest_bus_stop' => 'Business District',
                    'leadership_trainings' => 'ELP',
                ],
                [
                    'first_name' => 'Mary',
                    'last_name' => 'Davis',
                    'email' => '',
                    'phone' => '+1234567895',
                    'gender' => 'female',
                    'date_of_birth' => '1960-11-30',
                    'address' => '987 Cedar Lane, City, State',
                    'member_status' => 'member',
                    'growth_level' => 'pastor',
                    'teci_status' => 'graduated',
                    'marital_status' => 'widowed',
                    'occupation' => 'Retired',
                    'nearest_bus_stop' => 'Senior Center',
                    'leadership_trainings' => 'ELP,MLCC',
                ],
            ];

            // Create filename with timestamp
            $filename = 'member_import_template_'.now()->format('Y-m-d_H-i-s').'.xlsx';
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
                        'First Name',
                        'Last Name',
                        'Email',
                        'Phone',
                        'Gender',
                        'Date of Birth',
                        'Address',
                        'Member Status',
                        'Growth Level',
                        'TECI Status',
                        'Marital Status',
                        'Occupation',
                        'Nearest Bus Stop',
                        'Leadership Trainings',
                    ];
                }
            };

            Excel::store($export, $filePath, 'public');

            return [
                'success' => true,
                'file_path' => $filePath,
                'filename' => 'member_import_template.xlsx',
                'message' => 'Template generated successfully',
            ];

        } catch (\Exception $e) {
            Log::error('Member template generation failed', ['error' => $e->getMessage()]);

            return [
                'success' => false,
                'message' => 'Failed to generate member import template',
            ];
        }
    }

    /**
     * Get ministry import template.
     */
    public function getMinistryImportTemplate(): array
    {
        try {
            $data = [
                [
                    'name' => 'Youth Ministry',
                    'description' => 'Ministry focused on young people and their spiritual growth',
                    'leader_name' => 'John Leader',
                    'leader_email' => 'john.leader@example.com',
                    'leader_phone' => '+1234567890',
                    'status' => 'active',
                ],
                [
                    'name' => 'Music Ministry',
                    'description' => 'Ministry responsible for worship music and choir',
                    'leader_name' => 'Jane Musician',
                    'leader_email' => 'jane.musician@example.com',
                    'leader_phone' => '+1234567891',
                    'status' => 'active',
                ],
            ];

            // Create filename with timestamp
            $filename = 'ministry_import_template_'.now()->format('Y-m-d_H-i-s').'.xlsx';
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
                        'Description',
                        'Leader Name',
                        'Leader Email',
                        'Leader Phone',
                        'Status',
                    ];
                }
            };

            Excel::store($export, $filePath, 'public');

            return [
                'success' => true,
                'file_path' => $filePath,
                'filename' => 'ministry_import_template.xlsx',
                'message' => 'Template generated successfully',
            ];

        } catch (\Exception $e) {
            Log::error('Ministry template generation failed', ['error' => $e->getMessage()]);

            return [
                'success' => false,
                'message' => 'Failed to generate ministry import template',
            ];
        }
    }

    /**
     * Get department import template.
     */
    public function getDepartmentImportTemplate(): array
    {
        try {
            $data = [
                [
                    'name' => 'Worship Team',
                    'description' => 'Team responsible for leading worship during services',
                    'ministry_name' => 'Music Ministry',
                    'leader_name' => 'David Worship',
                    'leader_email' => 'david.worship@example.com',
                    'leader_phone' => '+1234567892',
                ],
                [
                    'name' => 'Sound Team',
                    'description' => 'Team responsible for audio and sound equipment',
                    'ministry_name' => 'Music Ministry',
                    'leader_name' => 'Sarah Sound',
                    'leader_email' => 'sarah.sound@example.com',
                    'leader_phone' => '+1234567893',
                ],
            ];

            // Create filename with timestamp
            $filename = 'department_import_template_'.now()->format('Y-m-d_H-i-s').'.xlsx';
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
                        'Description',
                        'Ministry Name',
                        'Leader Name',
                        'Leader Email',
                        'Leader Phone',
                    ];
                }
            };

            Excel::store($export, $filePath, 'public');

            return [
                'success' => true,
                'file_path' => $filePath,
                'filename' => 'department_import_template.xlsx',
                'message' => 'Template generated successfully',
            ];

        } catch (\Exception $e) {
            Log::error('Department template generation failed', ['error' => $e->getMessage()]);

            return [
                'success' => false,
                'message' => 'Failed to generate department import template',
            ];
        }
    }

    /**
     * Get event reports import template.
     */
    public function getEventReportsImportTemplate(): array
    {
        try {
            // Sample data for template
            $sampleData = [
                [
                    'event_type' => 'service',
                    'service_type' => 'Sunday Service',
                    'report_date' => '2024-01-07',
                    'attendance_male' => 45,
                    'attendance_female' => 55,
                    'attendance_children' => 20,
                    'attendance_online' => 15,
                    'first_time_guests' => 3,
                    'converts' => 1,
                    'start_time' => '09:00',
                    'end_time' => '11:30',
                    'number_of_cars' => 25,
                    'notes' => 'Great service with powerful worship',
                ],
                [
                    'event_type' => 'Prayer Meeting',
                    'service_type' => null,
                    'report_date' => '2024-01-10',
                    'attendance_male' => 15,
                    'attendance_female' => 20,
                    'attendance_children' => 5,
                    'attendance_online' => 8,
                    'first_time_guests' => 1,
                    'converts' => 0,
                    'start_time' => '18:00',
                    'end_time' => '19:30',
                    'number_of_cars' => 10,
                    'notes' => 'Focused prayer for church growth',
                ],
                [
                    'event_type' => 'Youth Service',
                    'service_type' => null,
                    'report_date' => '2024-01-14',
                    'attendance_male' => 25,
                    'attendance_female' => 30,
                    'attendance_children' => 15,
                    'attendance_online' => 12,
                    'first_time_guests' => 5,
                    'converts' => 2,
                    'start_time' => '16:00',
                    'end_time' => '18:00',
                    'number_of_cars' => 15,
                    'notes' => 'Youth baptism ceremony',
                    // Multi-service fields for full compatibility
                    'is_multi_service' => true,
                    'second_service_attendance_male' => 18,
                    'second_service_attendance_female' => 22,
                    'second_service_attendance_children' => 9,
                    'second_service_first_time_guests' => 2,
                    'second_service_converts' => 1,
                    'second_service_number_of_cars' => 11,
                    'second_service_start_time' => '18:30',
                    'second_service_end_time' => '20:00',
                    'second_service_notes' => 'Evening youth rally',
                ],
            ];

            // Create anonymous class for export
            $export = new class($sampleData) implements \Maatwebsite\Excel\Concerns\FromCollection, \Maatwebsite\Excel\Concerns\WithHeadings
            {
                private $data;

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
                        'Event Type',
                        'Service Type',
                        'Report Date',
                        'Male Attendance',
                        'Female Attendance',
                        'Children Attendance',
                        'Online Attendance',
                        'First Time Guests',
                        'Converts',
                        'Start Time',
                        'End Time',
                        'Number of Cars',
                        'Notes',
                        // Second service compatible columns
                        'Is Multi Service',
                        'Second Service Male Attendance',
                        'Second Service Female Attendance',
                        'Second Service Children Attendance',
                        'Second Service First Time Guests',
                        'Second Service Converts',
                        'Second Service Number of Cars',
                        'Second Service Start Time',
                        'Second Service End Time',
                        'Second Service Notes',
                    ];
                }
            };

            // Generate filename
            $filename = 'event_reports_import_template_'.now()->format('Y-m-d_H-i-s').'.xlsx';
            $filePath = 'exports/'.$filename;
            $fullPath = storage_path('app/public/'.$filePath);

            // Ensure directory exists
            $directory = dirname($fullPath);
            if (! is_dir($directory)) {
                mkdir($directory, 0755, true);
            }

            // Create the Excel file
            Excel::store($export, $filePath, 'public');

            return [
                'success' => true,
                'file_path' => $filePath,
                'filename' => 'event_reports_import_template.xlsx',
                'message' => 'Template generated successfully',
            ];

        } catch (\Exception $e) {
            Log::error('Event reports template generation failed', [
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Failed to generate template: '.$e->getMessage(),
            ];
        }
    }

    /**
     * Get small group import template.
     */
    public function getSmallGroupImportTemplate(): array
    {
        try {
            $data = [
                [
                    'name' => 'Young Adults Group',
                    'description' => 'Small group for young adults aged 18-30',
                    'location' => 'Church Hall A',
                    'meeting_day' => 'Wednesday',
                    'meeting_time' => '19:00',
                    'leader_name' => 'Mike Leader',
                    'leader_email' => 'mike.leader@example.com',
                    'leader_phone' => '+1234567894',
                    'status' => 'active',
                ],
                [
                    'name' => 'Couples Group',
                    'description' => 'Small group for married couples',
                    'location' => 'Church Hall B',
                    'meeting_day' => 'Friday',
                    'meeting_time' => '19:30',
                    'leader_name' => 'Lisa Leader',
                    'leader_email' => 'lisa.leader@example.com',
                    'leader_phone' => '+1234567895',
                    'status' => 'active',
                ],
            ];

            // Create filename with timestamp
            $filename = 'small_group_import_template_'.now()->format('Y-m-d_H-i-s').'.xlsx';
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
                        'Description',
                        'Location',
                        'Meeting Day',
                        'Meeting Time',
                        'Leader Name',
                        'Leader Email',
                        'Leader Phone',
                        'Status',
                    ];
                }
            };

            Excel::store($export, $filePath, 'public');

            return [
                'success' => true,
                'file_path' => $filePath,
                'filename' => 'small_group_import_template.xlsx',
                'message' => 'Template generated successfully',
            ];

        } catch (\Exception $e) {
            Log::error('Small group template generation failed', ['error' => $e->getMessage()]);

            return [
                'success' => false,
                'message' => 'Failed to generate small group import template',
            ];
        }
    }

    /**
     * Validate import file without processing.
     */
    public function validateImportFile(UploadedFile $file): array
    {
        try {
            // Basic file validation
            $allowedExtensions = ['xlsx', 'xls', 'csv'];
            $extension = $file->getClientOriginalExtension();

            if (! in_array(strtolower($extension), $allowedExtensions)) {
                return [
                    'valid' => false,
                    'errors' => ['Invalid file type. Only Excel (.xlsx, .xls) and CSV files are allowed.'],
                ];
            }

            if ($file->getSize() > 10 * 1024 * 1024) {
                return [
                    'valid' => false,
                    'errors' => ['File size too large. Maximum file size is 10MB.'],
                ];
            }

            // Store file temporarily for validation
            $filePath = $file->store('temp', 'local');

            // Read first few rows to validate structure
            $data = Excel::toArray([], $filePath, 'local')[0] ?? [];

            // Clean up
            Storage::disk('local')->delete($filePath);

            if (empty($data)) {
                return [
                    'valid' => false,
                    'errors' => ['File appears to be empty or corrupted.'],
                ];
            }

            // Check if headers are present
            $headers = $data[0] ?? [];
            $requiredHeaders = ['name', 'email', 'phone', 'gender'];
            $missingHeaders = array_diff($requiredHeaders, $headers);

            if (! empty($missingHeaders)) {
                return [
                    'valid' => false,
                    'errors' => ['Missing required columns: '.implode(', ', $missingHeaders)],
                ];
            }

            return [
                'valid' => true,
                'message' => 'File is valid for import',
                'preview' => [
                    'total_rows' => count($data) - 1, // Exclude header row
                    'headers' => $headers,
                    'sample_data' => array_slice($data, 1, 5), // First 5 data rows
                ],
            ];

        } catch (\Exception $e) {
            return [
                'valid' => false,
                'errors' => ['File validation failed: '.$e->getMessage()],
            ];
        }
    }

    /**
     * Clean up old export files.
     */
    public function cleanupOldExports(int $daysOld = 7): array
    {
        try {
            $files = Storage::disk('public')->files('exports');
            $deletedCount = 0;
            $cutoffTime = now()->subDays($daysOld);

            foreach ($files as $file) {
                $timestamp = Storage::disk('public')->lastModified($file);
                if ($timestamp < $cutoffTime->timestamp) {
                    Storage::disk('public')->delete($file);
                    $deletedCount++;
                }
            }

            Log::info('Export cleanup completed', [
                'deleted_files' => $deletedCount,
                'cutoff_days' => $daysOld,
            ]);

            return [
                'success' => true,
                'message' => "Cleaned up {$deletedCount} old export files",
                'deleted_count' => $deletedCount,
            ];

        } catch (\Exception $e) {
            Log::error('Export cleanup failed', ['error' => $e->getMessage()]);

            return [
                'success' => false,
                'message' => 'Cleanup failed: '.$e->getMessage(),
                'errors' => [$e->getMessage()],
            ];
        }
    }

    /**
     * Format error details for frontend display.
     */
    private function formatErrorDetails(array $errors): string
    {
        if (empty($errors)) {
            return '';
        }

        $formattedErrors = [];
        foreach ($errors as $error) {
            $row = $error['row'] ?? 'unknown';
            $message = $error['message'] ?? 'Unknown error';
            $type = $error['type'] ?? 'error';

            $formattedErrors[] = "Row {$row}: {$message}";
        }

        return implode("\n", $formattedErrors);
    }
}
