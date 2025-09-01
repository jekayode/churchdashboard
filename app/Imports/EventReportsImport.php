<?php

declare(strict_types=1);

namespace App\Imports;

use App\Models\Event;
use App\Models\EventReport;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
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

final class EventReportsImport implements ToCollection, WithHeadingRow, WithValidation, WithBatchInserts, WithChunkReading, SkipsOnFailure
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
     * Process the collection of event report data.
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
     * Process a single row of event report data.
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

        // Check if event report already exists for this date and event
        $existingReport = EventReport::where('event_id', $data['event_id'])
            ->where('report_date', $data['report_date'])
            ->first();

        if ($existingReport) {
            $this->addError($rowNumber, 'duplicate', "Event report already exists for this event on {$data['report_date']}");
            $this->failureCount++;
            return;
        }

        // Create event report record
        $reportData = $this->prepareEventReportData($data);
        $eventReport = EventReport::create($reportData);

        $this->successCount++;
        $this->successes[] = [
            'row' => $rowNumber,
            'event_report_id' => $eventReport->id,
            'event_type' => $eventReport->event_type,
            'report_date' => $eventReport->report_date->format('Y-m-d'),
            'total_attendance' => $eventReport->combined_total_attendance,
        ];
        
        Log::info('Event report imported successfully', [
            'event_report_id' => $eventReport->id,
            'event_type' => $eventReport->event_type,
            'report_date' => $eventReport->report_date->format('Y-m-d'),
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
            'event_type' => ['event_type', 'type'],
            'service_type' => ['service_type', 'service_category', 'service_kind'],
            'report_date' => ['report_date', 'date', 'service_date'],
            'attendance_male' => ['attendance_male', 'male', 'men'],
            'attendance_female' => ['attendance_female', 'female', 'women'],
            'attendance_children' => ['attendance_children', 'children', 'kids'],
            'attendance_online' => ['attendance_online', 'online', 'online_attendance'],
            'first_time_guests' => ['first_time_guests', 'guests', 'new_visitors'],
            'converts' => ['converts', 'new_converts', 'salvations'],
            'start_time' => ['start_time', 'service_start_time'],
            'end_time' => ['end_time', 'service_end_time'],
            'number_of_cars' => ['number_of_cars', 'cars', 'vehicles'],
            'notes' => ['notes', 'comments', 'remarks'],
            'is_multi_service' => ['is_multi_service', 'multi_service', 'two_services'],
            'second_service_attendance_male' => ['second_service_attendance_male', 'second_male', 'evening_male'],
            'second_service_attendance_female' => ['second_service_attendance_female', 'second_female', 'evening_female'],
            'second_service_attendance_children' => ['second_service_attendance_children', 'second_children', 'evening_children'],
            'second_service_first_time_guests' => ['second_service_first_time_guests', 'second_guests', 'evening_guests'],
            'second_service_converts' => ['second_service_converts', 'second_converts', 'evening_converts'],
            'second_service_number_of_cars' => ['second_service_number_of_cars', 'second_cars', 'evening_cars'],
            'second_service_start_time' => ['second_service_start_time', 'evening_start_time'],
            'second_service_end_time' => ['second_service_end_time', 'evening_end_time'],
            'second_service_notes' => ['second_service_notes', 'evening_notes'],
        ];

        foreach ($fieldMap as $dbField => $possibleHeaders) {
            foreach ($possibleHeaders as $header) {
                if (isset($row[$header]) && !empty($row[$header])) {
                    $value = $row[$header];
                    
                    // Convert numeric fields
                    if (in_array($dbField, ['attendance_male', 'attendance_female', 'attendance_children', 'attendance_online', 'first_time_guests', 'converts', 'number_of_cars', 'second_service_attendance_male', 'second_service_attendance_female', 'second_service_attendance_children', 'second_service_first_time_guests', 'second_service_converts', 'second_service_number_of_cars'])) {
                        $value = (int) $value;
                    }
                    
                    // Convert boolean fields
                    if ($dbField === 'is_multi_service') {
                        $value = in_array(strtolower($value), ['true', '1', 'yes', 'y']);
                    }
                    
                    $cleanData[$dbField] = $value;
                    break;
                }
            }
        }

        // Parse dates
        foreach (['report_date'] as $dateField) {
            if (isset($cleanData[$dateField])) {
                $cleanData[$dateField] = $this->parseDate($cleanData[$dateField]);
            }
        }

        // Parse times
        foreach (['start_time', 'end_time', 'second_service_start_time', 'second_service_end_time'] as $timeField) {
            if (isset($cleanData[$timeField])) {
                $cleanData[$timeField] = $this->parseDateTime($cleanData[$timeField], $cleanData['report_date'] ?? null);
            }
        }

        // Handle backward compatibility for service types
        // If event_type is a service type but service_type is not provided, fix it
        $serviceTypes = ['Sunday Service', 'Mid-Week Service'];
        if (isset($cleanData['event_type']) && in_array($cleanData['event_type'], $serviceTypes) && !isset($cleanData['service_type'])) {
            $cleanData['service_type'] = $cleanData['event_type'];
            $cleanData['event_type'] = 'service';
        }

        // Set defaults for required fields
        $cleanData['attendance_male'] = $cleanData['attendance_male'] ?? 0;
        $cleanData['attendance_female'] = $cleanData['attendance_female'] ?? 0;
        $cleanData['attendance_children'] = $cleanData['attendance_children'] ?? 0;
        $cleanData['attendance_online'] = $cleanData['attendance_online'] ?? 0;
        $cleanData['first_time_guests'] = $cleanData['first_time_guests'] ?? 0;
        $cleanData['converts'] = $cleanData['converts'] ?? 0;
        $cleanData['number_of_cars'] = $cleanData['number_of_cars'] ?? 0;
        $cleanData['is_multi_service'] = $cleanData['is_multi_service'] ?? false;

        // Set second service defaults if multi-service
        if ($cleanData['is_multi_service']) {
            $cleanData['second_service_attendance_male'] = $cleanData['second_service_attendance_male'] ?? 0;
            $cleanData['second_service_attendance_female'] = $cleanData['second_service_attendance_female'] ?? 0;
            $cleanData['second_service_attendance_children'] = $cleanData['second_service_attendance_children'] ?? 0;
            $cleanData['second_service_first_time_guests'] = $cleanData['second_service_first_time_guests'] ?? 0;
            $cleanData['second_service_converts'] = $cleanData['second_service_converts'] ?? 0;
            $cleanData['second_service_number_of_cars'] = $cleanData['second_service_number_of_cars'] ?? 0;
        }

        return $cleanData;
    }

    /**
     * Parse date string into Y-m-d format.
     */
    private function parseDate(?string $dateString): ?string
    {
        if (empty($dateString)) {
            return null;
        }

        try {
            return Carbon::parse($dateString)->format('Y-m-d');
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Parse time string into datetime format.
     */
    private function parseDateTime(?string $timeString, ?string $dateString): ?string
    {
        if (empty($timeString)) {
            return null;
        }

        try {
            $date = $dateString ? Carbon::parse($dateString) : Carbon::today();
            $time = Carbon::parse($timeString);
            
            return $date->setTime($time->hour, $time->minute, $time->second)->format('Y-m-d H:i:s');
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Get validation rules for a single row.
     */
    private function getRowValidationRules(): array
    {
        return [
            'event_type' => ['required', 'string', Rule::in(EventReport::EVENT_TYPES)],
            'service_type' => ['nullable', 'string', 'max:100', 'required_if:event_type,service'],
            'report_date' => ['required', 'date'],
            'attendance_male' => ['required', 'integer', 'min:0'],
            'attendance_female' => ['required', 'integer', 'min:0'],
            'attendance_children' => ['required', 'integer', 'min:0'],
            'attendance_online' => ['integer', 'min:0'],
            'first_time_guests' => ['integer', 'min:0'],
            'converts' => ['integer', 'min:0'],
            'number_of_cars' => ['integer', 'min:0'],
            'is_multi_service' => ['boolean'],
            'second_service_attendance_male' => ['integer', 'min:0'],
            'second_service_attendance_female' => ['integer', 'min:0'],
            'second_service_attendance_children' => ['integer', 'min:0'],
            'second_service_first_time_guests' => ['integer', 'min:0'],
            'second_service_converts' => ['integer', 'min:0'],
            'second_service_number_of_cars' => ['integer', 'min:0'],
        ];
    }

    /**
     * Find or create event for the report.
     */
    private function findOrCreateEvent(array $data): Event
    {
        // Try to find existing event for this type and branch
        $query = Event::where('event_type', $data['event_type'])
            ->where('branch_id', $this->branchId)
            ->where('is_recurring', true);
            
        // If service type is provided, also match on service_type
        if (!empty($data['service_type'])) {
            $query->where('service_type', $data['service_type']);
        }
        
        $event = $query->first();

        if (!$event) {
            // Create a new recurring event
            $eventName = !empty($data['service_type']) ? $data['service_type'] : $data['event_type'];
            $event = Event::create([
                'name' => $eventName,
                'event_type' => $data['event_type'],
                'service_type' => $data['service_type'] ?? null,
                'branch_id' => $this->branchId,
                'is_recurring' => true,
                'created_by' => Auth::id(),
                'start_date' => $data['report_date'],
                'end_date' => $data['report_date'],
                'start_time' => $data['start_time'] ?? '09:00:00',
                'end_time' => $data['end_time'] ?? '11:00:00',
                'description' => "Auto-created from import for {$eventName}",
            ]);
        }

        return $event;
    }

    /**
     * Prepare event report data for creation.
     */
    private function prepareEventReportData(array $data): array
    {
        // Find or create event
        $event = $this->findOrCreateEvent($data);

        return [
            'event_id' => $event->id,
            'reported_by' => Auth::id(),
            'event_type' => $data['event_type'],
            'service_type' => $data['service_type'] ?? null,
            'report_date' => $data['report_date'],
            'attendance_male' => $data['attendance_male'],
            'attendance_female' => $data['attendance_female'],
            'attendance_children' => $data['attendance_children'],
            'attendance_online' => $data['attendance_online'],
            'first_time_guests' => $data['first_time_guests'],
            'converts' => $data['converts'],
            'start_time' => $data['start_time'],
            'end_time' => $data['end_time'],
            'number_of_cars' => $data['number_of_cars'],
            'notes' => $data['notes'] ?? null,
            'is_multi_service' => $data['is_multi_service'],
            'second_service_attendance_male' => $data['second_service_attendance_male'] ?? null,
            'second_service_attendance_female' => $data['second_service_attendance_female'] ?? null,
            'second_service_attendance_children' => $data['second_service_attendance_children'] ?? null,
            'second_service_first_time_guests' => $data['second_service_first_time_guests'] ?? null,
            'second_service_converts' => $data['second_service_converts'] ?? null,
            'second_service_number_of_cars' => $data['second_service_number_of_cars'] ?? null,
            'second_service_start_time' => $data['second_service_start_time'] ?? null,
            'second_service_end_time' => $data['second_service_end_time'] ?? null,
            'second_service_notes' => $data['second_service_notes'] ?? null,
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
            'successes' => $this->successes,
        ];
    }

    /**
     * Validation rules for the entire import.
     */
    public function rules(): array
    {
        return [
            '*.event_type' => ['required', 'string'],
            '*.service_type' => ['nullable', 'string'],
            '*.report_date' => ['required'],
            '*.attendance_male' => ['required', 'numeric', 'min:0'],
            '*.attendance_female' => ['required', 'numeric', 'min:0'],
            '*.attendance_children' => ['required', 'numeric', 'min:0'],
            '*.attendance_online' => ['numeric', 'min:0'],
        ];
    }

    /**
     * Custom validation messages.
     */
    public function customValidationMessages(): array
    {
        return [
            '*.event_type.required' => 'Event type is required',
            '*.service_type.required_if' => 'Service type is required when event type is service',
            '*.report_date.required' => 'Report date is required',
            '*.attendance_male.required' => 'Male attendance is required',
            '*.attendance_female.required' => 'Female attendance is required',
            '*.attendance_children.required' => 'Children attendance is required',
            '*.attendance_online.numeric' => 'Online attendance must be a valid number',
        ];
    }

    /**
     * Batch size for processing.
     */
    public function batchSize(): int
    {
        return config('import.batch_sizes.event_reports', 50);
    }

    /**
     * Chunk size for reading.
     */
    public function chunkSize(): int
    {
        return config('import.chunk_sizes.event_reports', 100);
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
        return $this->errors;
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
            'total_processed' => $this->successCount + $this->failureCount,
            'successful_imports' => $this->successCount,
            'failed_imports' => $this->failureCount,
            'success_rate' => $this->successCount + $this->failureCount > 0 
                ? round(($this->successCount / ($this->successCount + $this->failureCount)) * 100, 2) 
                : 0,
        ];
    }
} 