<?php

declare(strict_types=1);

namespace App\Imports;

use App\Models\Branch;
use App\Models\Department;
use App\Models\Ministry;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;

final class DepartmentsImport implements ToCollection, WithHeadingRow, WithValidation, WithBatchInserts, WithChunkReading
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
     * Process the collection of department data.
     */
    public function collection(Collection $rows): void
    {
        foreach ($rows as $index => $row) {
            try {
                $this->processRow($row->toArray(), $index + 2); // +2 for header and 0-index
            } catch (\Exception $e) {
                $this->addError($index + 2, 'general', $e->getMessage());
                $this->failureCount++;
            }
        }
    }

    /**
     * Process a single row of department data.
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

        // Find ministry by name if provided
        $ministryId = null;
        if (!empty($data['ministry_name'])) {
            $ministry = Ministry::where('name', $data['ministry_name'])
                ->where('branch_id', $this->branchId)
                ->first();
            if ($ministry) {
                $ministryId = $ministry->id;
            } else {
                $this->addError($rowNumber, 'ministry_not_found', "Ministry not found: {$data['ministry_name']}");
                $this->failureCount++;
                return;
            }
        }

        // Check if department already exists
        $existingDepartment = Department::where('name', $data['name'])
            ->where('ministry_id', $ministryId)
            ->first();

        if ($existingDepartment) {
            $this->addError($rowNumber, 'duplicate', "Department already exists: {$data['name']}");
            $this->failureCount++;
            return;
        }

        // Find leader by email if provided
        $leaderId = null;
        if (!empty($data['leader_email'])) {
            $leader = User::where('email', $data['leader_email'])->first();
            if ($leader) {
                $leaderId = $leader->id;
            } else {
                $this->addError($rowNumber, 'leader_not_found', "Leader not found with email: {$data['leader_email']}");
            }
        }

        // Create department record
        $departmentData = $this->prepareDepartmentData($data, $ministryId, $leaderId);
        $department = Department::create($departmentData);

        $this->successCount++;
        $this->successes[] = [
            'row' => $rowNumber,
            'department_id' => $department->id,
            'name' => $department->name,
            'ministry_name' => $data['ministry_name'] ?? null,
            'leader_email' => $data['leader_email'] ?? null,
        ];
        
        Log::info('Department imported successfully', [
            'department_id' => $department->id,
            'name' => $department->name,
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
            'name' => ['name', 'department_name'],
            'description' => ['description', 'department_description'],
            'ministry_name' => ['ministry_name', 'ministry'],
            'leader_email' => ['leader_email', 'leader', 'department_leader'],
        ];

        foreach ($fieldMap as $dbField => $possibleHeaders) {
            foreach ($possibleHeaders as $header) {
                if (isset($row[$header]) && !empty($row[$header])) {
                    $cleanData[$dbField] = trim($row[$header]);
                    break;
                }
            }
        }

        return $cleanData;
    }

    /**
     * Get validation rules for a single row.
     */
    private function getRowValidationRules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'ministry_name' => ['required', 'string', 'max:255'],
            'leader_email' => ['nullable', 'email', 'max:255'],
        ];
    }

    /**
     * Prepare department data for database insertion.
     */
    private function prepareDepartmentData(array $data, int $ministryId, ?int $leaderId): array
    {
        return [
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'ministry_id' => $ministryId,
            'leader_id' => $leaderId,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    /**
     * Add an error to the collection.
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
     * Get validation rules for the entire file.
     */
    public function rules(): array
    {
        return [
            '*.name' => ['required', 'string', 'max:255'],
            '*.description' => ['nullable', 'string', 'max:1000'],
            '*.ministry_name' => ['required', 'string', 'max:255'],
            '*.leader_email' => ['nullable', 'email', 'max:255'],
        ];
    }

    /**
     * Custom validation messages.
     */
    public function customValidationMessages(): array
    {
        return [
            'name.required' => 'Department name is required.',
            'name.string' => 'Department name must be a string.',
            'name.max' => 'Department name cannot exceed 255 characters.',
            'ministry_name.required' => 'Ministry name is required.',
            'leader_email.email' => 'Leader email must be a valid email address.',
        ];
    }

    /**
     * Batch size for processing.
     */
    public function batchSize(): int
    {
        return 100;
    }

    /**
     * Chunk size for reading.
     */
    public function chunkSize(): int
    {
        return 100;
    }

    /**
     * Get all errors.
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * Get all successes.
     */
    public function getSuccesses(): array
    {
        return $this->successes;
    }

    /**
     * Get import summary.
     */
    public function getImportSummary(): array
    {
        return [
            'total_processed' => $this->successCount + $this->failureCount,
            'successful_imports' => $this->successCount,
            'failed_imports' => $this->failureCount,
            'errors' => $this->errors,
            'successes' => $this->successes,
        ];
    }
} 