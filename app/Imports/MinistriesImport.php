<?php

declare(strict_types=1);

namespace App\Imports;

use App\Models\Branch;
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

final class MinistriesImport implements ToCollection, WithHeadingRow, WithValidation, WithBatchInserts, WithChunkReading
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
     * Process the collection of ministry data.
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
     * Process a single row of ministry data.
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

        // Check if ministry already exists
        $existingMinistry = Ministry::where('name', $data['name'])
            ->where('branch_id', $this->branchId)
            ->first();

        if ($existingMinistry) {
            $this->addError($rowNumber, 'duplicate', "Ministry already exists: {$data['name']}");
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

        // Create ministry record
        $ministryData = $this->prepareMinistryData($data, $leaderId);
        $ministry = Ministry::create($ministryData);

        $this->successCount++;
        $this->successes[] = [
            'row' => $rowNumber,
            'ministry_id' => $ministry->id,
            'name' => $ministry->name,
            'leader_email' => $data['leader_email'] ?? null,
        ];
        
        Log::info('Ministry imported successfully', [
            'ministry_id' => $ministry->id,
            'name' => $ministry->name,
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
            'name' => ['name', 'ministry_name'],
            'description' => ['description', 'ministry_description'],
            'leader_email' => ['leader_email', 'leader', 'ministry_leader'],
            'meeting_day' => ['meeting_day', 'day'],
            'meeting_time' => ['meeting_time', 'time'],
            'meeting_location' => ['meeting_location', 'location'],
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
            'leader_email' => ['nullable', 'email', 'max:255'],
            'meeting_day' => ['nullable', 'string', 'max:50'],
            'meeting_time' => ['nullable', 'string', 'max:50'],
            'meeting_location' => ['nullable', 'string', 'max:255'],
        ];
    }

    /**
     * Prepare ministry data for database insertion.
     */
    private function prepareMinistryData(array $data, ?int $leaderId): array
    {
        return [
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'branch_id' => $this->branchId,
            'leader_id' => $leaderId,
            'meeting_day' => $data['meeting_day'] ?? null,
            'meeting_time' => $data['meeting_time'] ?? null,
            'meeting_location' => $data['meeting_location'] ?? null,
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
            '*.leader_email' => ['nullable', 'email', 'max:255'],
            '*.meeting_day' => ['nullable', 'string', 'max:50'],
            '*.meeting_time' => ['nullable', 'string', 'max:50'],
            '*.meeting_location' => ['nullable', 'string', 'max:255'],
        ];
    }

    /**
     * Custom validation messages.
     */
    public function customValidationMessages(): array
    {
        return [
            'name.required' => 'Ministry name is required.',
            'name.string' => 'Ministry name must be a string.',
            'name.max' => 'Ministry name cannot exceed 255 characters.',
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