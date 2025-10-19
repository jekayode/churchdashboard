<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\BranchReportToken;
use App\Models\Event;
use App\Services\ReportingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

final class PublicReportController extends Controller
{
    public function __construct(
        private readonly ReportingService $reportingService
    ) {}

    /**
     * Show the public report submission form.
     */
    public function showSubmissionForm(string $token): View
    {
        $reportToken = BranchReportToken::where('token', $token)->first();

        if (! $reportToken || ! $reportToken->isValid()) {
            abort(404, 'Invalid or expired submission link.');
        }

        // Record token usage
        $reportToken->recordUsage();

        // Get events for this branch
        $eventsQuery = Event::where('branch_id', $reportToken->branch_id)
            ->where('is_published', true)
            ->where('status', 'active')
            ->orderBy('start_date', 'desc');

        // Filter events if specific events are allowed
        if ($reportToken->allowed_events && is_array($reportToken->allowed_events)) {
            $eventsQuery->whereIn('id', $reportToken->allowed_events);
        }

        $events = $eventsQuery->get();

        return view('public.reports.submit', [
            'token' => $reportToken,
            'branch' => $reportToken->branch,
            'events' => $events,
        ]);
    }

    /**
     * Submit a report via public link.
     */
    public function submitReport(Request $request, string $token): JsonResponse
    {
        $reportToken = BranchReportToken::where('token', $token)->first();

        if (! $reportToken || ! $reportToken->isValid()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired submission link.',
            ], 404);
        }

        // Validate the request
        $validated = $request->validate([
            'submitter_email' => 'nullable|email',
            'event_id' => 'required|exists:events,id',
            'event_date' => 'required|date',
            'event_type' => 'required|string|max:100',
            'service_type' => 'nullable|string|max:100',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'notes' => 'nullable|string',

            // First Service
            'male_attendance' => 'required|integer|min:0',
            'female_attendance' => 'required|integer|min:0',
            'children_attendance' => 'required|integer|min:0',
            'online_attendance' => 'nullable|integer|min:0',
            'first_time_guests' => 'required|integer|min:0',
            'converts' => 'required|integer|min:0',
            'cars' => 'required|integer|min:0',

            // Second Service (optional)
            'has_second_service' => 'boolean',
            'second_service_start_time' => 'nullable|date_format:H:i|required_if:has_second_service,true',
            'second_service_end_time' => 'nullable|date_format:H:i|after:second_service_start_time|required_if:has_second_service,true',
            'second_male_attendance' => 'nullable|integer|min:0|required_if:has_second_service,true',
            'second_female_attendance' => 'nullable|integer|min:0|required_if:has_second_service,true',
            'second_children_attendance' => 'nullable|integer|min:0|required_if:has_second_service,true',
            'second_online_attendance' => 'nullable|integer|min:0',
            'second_first_time_guests' => 'nullable|integer|min:0|required_if:has_second_service,true',
            'second_converts' => 'nullable|integer|min:0|required_if:has_second_service,true',
            'second_cars' => 'nullable|integer|min:0|required_if:has_second_service,true',
            'second_service_notes' => 'nullable|string',
        ]);

        // Verify the event belongs to the correct branch
        $event = Event::find($validated['event_id']);
        if (! $event || $event->branch_id !== $reportToken->branch_id) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid event for this branch.',
            ], 422);
        }

        // Check if specific events are allowed and this event is in the list
        if ($reportToken->allowed_events && ! in_array($event->id, $reportToken->allowed_events)) {
            return response()->json([
                'success' => false,
                'message' => 'This event is not available for reporting through this link.',
            ], 422);
        }

        // For team tokens, validate that the submitter is authorized
        if ($reportToken->isTeamToken()) {
            if (empty($validated['submitter_email'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Please select your role to submit the report.',
                ], 422);
            }

            if (! $reportToken->isEmailAuthorized($validated['submitter_email'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'You are not authorized to submit reports for this team.',
                ], 403);
            }
        }

        try {
            // Transform field names to match database schema
            $data = $this->transformReportData($validated);

            // Set the reported_by to a system user or the branch pastor
            $data['reported_by'] = $reportToken->branch->pastor_id ?? 1; // Fallback to user ID 1 if no pastor

            // Check if a report already exists for this event/date/reporter
            $existingReport = \App\Models\EventReport::where('event_id', $data['event_id'])
                ->where('report_date', $data['report_date'])
                ->where('reported_by', $data['reported_by'])
                ->first();

            if ($existingReport) {
                // Update existing report
                $existingReport->update($data);
                $report = $existingReport;
                $isUpdate = true;
            } else {
                // Create new report
                $report = $this->reportingService->createEventReport($data);
                $isUpdate = false;
            }

            Log::info('Public report submitted successfully', [
                'report_id' => $report->id,
                'event_id' => $event->id,
                'branch_id' => $reportToken->branch_id,
                'token_id' => $reportToken->id,
                'submitter_email' => $validated['submitter_email'] ?? null,
                'submitter_role' => $reportToken->isTeamToken() ? $reportToken->getRoleForEmail($validated['submitter_email']) : 'Token Owner',
                'is_team_token' => $reportToken->isTeamToken(),
                'submitted_via' => 'public_link',
                'action' => $isUpdate ? 'updated' : 'created',
            ]);

            return response()->json([
                'success' => true,
                'message' => $isUpdate
                    ? 'Report updated successfully! Thank you for your submission.'
                    : 'Report submitted successfully! Thank you for your submission.',
                'data' => [
                    'report_id' => $report->id,
                    'event_name' => $event->name,
                    'report_date' => $validated['event_date'],
                    'action' => $isUpdate ? 'updated' : 'created',
                ],
            ], 201);

        } catch (\Exception $e) {
            Log::error('Failed to submit public report', [
                'token' => $token,
                'event_id' => $validated['event_id'] ?? null,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to submit report. Please try again.',
            ], 500);
        }
    }

    /**
     * Get events for a specific token.
     */
    public function getEvents(string $token): JsonResponse
    {
        $reportToken = BranchReportToken::where('token', $token)->first();

        if (! $reportToken || ! $reportToken->isValid()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired submission link.',
            ], 404);
        }

        $eventsQuery = Event::where('branch_id', $reportToken->branch_id)
            ->where('is_published', true)
            ->where('status', 'active')
            ->orderBy('start_date', 'desc');

        // Filter events if specific events are allowed
        if ($reportToken->allowed_events && is_array($reportToken->allowed_events)) {
            $eventsQuery->whereIn('id', $reportToken->allowed_events);
        }

        $events = $eventsQuery->get(['id', 'name', 'type', 'service_type', 'start_date', 'end_date']);

        return response()->json([
            'success' => true,
            'data' => $events,
        ]);
    }

    /**
     * Transform form field names to match database schema.
     */
    private function transformReportData(array $data): array
    {
        $transformed = [
            'event_id' => $data['event_id'],
            'report_date' => $data['event_date'],
            'event_type' => $data['event_type'],
            'service_type' => $data['service_type'],
            'start_time' => $data['start_time'],
            'end_time' => $data['end_time'],
            'notes' => $data['notes'],

            // First Service
            'attendance_male' => $data['male_attendance'],
            'attendance_female' => $data['female_attendance'],
            'attendance_children' => $data['children_attendance'],
            'attendance_online' => $data['online_attendance'] ?? 0,
            'first_time_guests' => $data['first_time_guests'],
            'converts' => $data['converts'],
            'number_of_cars' => $data['cars'],

            // Second Service
            'is_multi_service' => $data['has_second_service'] ?? false,
        ];

        if ($data['has_second_service'] ?? false) {
            $transformed['second_service_start_time'] = $data['second_service_start_time'];
            $transformed['second_service_end_time'] = $data['second_service_end_time'];
            $transformed['second_service_attendance_male'] = $data['second_male_attendance'];
            $transformed['second_service_attendance_female'] = $data['second_female_attendance'];
            $transformed['second_service_attendance_children'] = $data['second_children_attendance'];
            $transformed['second_service_attendance_online'] = $data['second_online_attendance'] ?? 0;
            $transformed['second_service_first_time_guests'] = $data['second_first_time_guests'];
            $transformed['second_service_converts'] = $data['second_converts'];
            $transformed['second_service_number_of_cars'] = $data['second_cars'];
            $transformed['second_service_notes'] = $data['second_service_notes'];
        }

        return $transformed;
    }
}
