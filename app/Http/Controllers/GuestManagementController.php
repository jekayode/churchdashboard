<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\AddGuestFollowUpRequest;
use App\Http\Requests\UpdateGuestStatusRequest;
use App\Models\GuestRegistrationAttempt;
use App\Models\Member;
use App\Services\GuestManagementService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class GuestManagementController extends Controller
{
    public function __construct(
        private readonly GuestManagementService $guestManagementService
    ) {}

    /**
     * Display guest list view.
     */
    public function index(Request $request): View
    {
        Gate::authorize('viewAnyGuests', [Member::class]);

        $user = auth()->user();

        // Determine branch filter for non-super-admin users
        $branchId = null;
        if (! $user->isSuperAdmin()) {
            $branchId = $user->getPrimaryBranch()?->id;
        }

        // Get filter values from request
        $filters = [
            'search' => $request->get('search'),
            'date_from' => $request->get('date_from'),
            'date_to' => $request->get('date_to'),
            'staying_intention' => $request->get('staying_intention'),
            'discovery_source' => $request->get('discovery_source'),
            'gender' => $request->get('gender'),
        ];

        // Remove empty filters
        $filters = array_filter($filters, fn ($value) => ! is_null($value) && $value !== '');

        // Get paginated guests
        $guests = $this->guestManagementService->getGuests($branchId, $filters);

        return view('admin.guests.index', [
            'guests' => $guests,
            'filters' => $filters,
            'branchId' => $branchId,
            'isSuperAdmin' => $user->isSuperAdmin(),
        ]);
    }

    /**
     * Display all guest registration attempts (including failed) for the team to recover data.
     */
    public function attempts(Request $request): View
    {
        Gate::authorize('viewAnyGuests', [Member::class]);

        $user = auth()->user();
        $branchId = null;
        if (! $user->isSuperAdmin()) {
            $branchId = $user->getPrimaryBranch()?->id;
        }

        $query = GuestRegistrationAttempt::query()->orderByDesc('created_at');

        if ($branchId !== null) {
            $query->where('branch_id', $branchId);
        }

        $status = $request->get('status');
        if ($status !== null && $status !== '') {
            $query->where('status', $status);
        }

        $attempts = $query->paginate(20)->withQueryString();

        return view('admin.guests.attempts', [
            'attempts' => $attempts,
            'branchId' => $branchId,
            'isSuperAdmin' => $user->isSuperAdmin(),
            'statusFilter' => $status,
        ]);
    }

    /**
     * API endpoint for AJAX guest listing.
     */
    public function getGuests(Request $request): JsonResponse
    {
        Gate::authorize('viewAnyGuests', [\App\Models\Member::class]);

        $user = auth()->user();

        // Determine branch filter for non-super-admin users
        $branchId = null;
        if (! $user->isSuperAdmin()) {
            $branchId = $user->getPrimaryBranch()?->id;
        }

        // Get filter values from request
        $filters = [
            'search' => $request->get('search'),
            'date_from' => $request->get('date_from'),
            'date_to' => $request->get('date_to'),
            'staying_intention' => $request->get('staying_intention'),
            'discovery_source' => $request->get('discovery_source'),
            'gender' => $request->get('gender'),
        ];

        // Remove empty filters
        $filters = array_filter($filters, fn ($value) => ! is_null($value) && $value !== '');

        // Get paginated guests
        $guests = $this->guestManagementService->getGuests($branchId, $filters, 15);

        return response()->json([
            'success' => true,
            'data' => $guests,
        ]);
    }

    /**
     * Display guest detail page.
     */
    public function show(Member $member): View
    {
        Gate::authorize('viewGuest', [$member]);

        $guest = $this->guestManagementService->getGuestDetails($member->id);

        if (! $guest) {
            abort(404, 'Guest not found');
        }

        return view('admin.guests.show', [
            'guest' => $guest,
        ]);
    }

    /**
     * Update guest status.
     */
    public function updateStatus(UpdateGuestStatusRequest $request, Member $member): JsonResponse
    {
        $success = $this->guestManagementService->updateGuestStatus(
            $member,
            $request->validated()['new_status'],
            $request->validated()['reason'] ?? null,
            $request->validated()['notes'] ?? null
        );

        if (! $success) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update guest status. Please ensure the guest is still a visitor and the new status is valid.',
            ], 422);
        }

        // Reload member to get updated data (can't use getGuestDetails since status changed)
        $member->refresh();
        $member->load(['statusHistory' => function ($query) {
            $query->orderBy('changed_at', 'desc')->with('changedBy:id,name');
        }]);

        return response()->json([
            'success' => true,
            'message' => 'Guest status updated successfully.',
            'data' => [
                'member_status' => $member->member_status,
                'status_history' => $member->statusHistory->take(5)->map(function ($history) {
                    return [
                        'previous_status' => $history->previous_status,
                        'new_status' => $history->new_status,
                        'reason' => $history->reason,
                        'notes' => $history->notes,
                        'changed_at' => $history->changed_at->format('Y-m-d H:i:s'),
                        'changed_by' => $history->changedBy?->name,
                    ];
                }),
            ],
        ]);
    }

    /**
     * Add follow-up note for a guest.
     */
    public function addFollowUp(AddGuestFollowUpRequest $request, Member $member): JsonResponse
    {
        try {
            $followUp = $this->guestManagementService->addFollowUp($member, $request->validated());

            // Load relationships
            $followUp->load(['createdBy:id,name', 'assignedTo:id,name']);

            return response()->json([
                'success' => true,
                'message' => 'Follow-up added successfully.',
                'data' => [
                    'id' => $followUp->id,
                    'follow_up_type' => $followUp->follow_up_type,
                    'contact_date' => $followUp->contact_date->format('Y-m-d'),
                    'contact_status' => $followUp->contact_status,
                    'notes' => $followUp->notes,
                    'next_follow_up_date' => $followUp->next_follow_up_date?->format('Y-m-d'),
                    'outcome' => $followUp->outcome,
                    'created_at' => $followUp->created_at->format('Y-m-d H:i:s'),
                    'created_by' => $followUp->createdBy?->name,
                    'assigned_to' => $followUp->assignedTo?->name,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to add follow-up: '.$e->getMessage(),
            ], 422);
        }
    }

    /**
     * Export guests data.
     */
    public function export(Request $request): BinaryFileResponse|StreamedResponse
    {
        Gate::authorize('viewAnyGuests', [\App\Models\Member::class]);

        $request->validate([
            'format' => 'required|in:csv,xlsx',
        ]);

        $user = auth()->user();

        // Determine branch filter for non-super-admin users
        $branchId = null;
        if (! $user->isSuperAdmin()) {
            $branchId = $user->getPrimaryBranch()?->id;
        }

        // Get filter values from request
        $filters = [
            'search' => $request->get('search'),
            'date_from' => $request->get('date_from'),
            'date_to' => $request->get('date_to'),
            'staying_intention' => $request->get('staying_intention'),
            'discovery_source' => $request->get('discovery_source'),
            'gender' => $request->get('gender'),
        ];

        // Remove empty filters
        $filters = array_filter($filters, fn ($value) => ! is_null($value) && $value !== '');

        // Get guests data
        $guests = $this->guestManagementService->exportGuests($branchId, $filters, $request->get('format'));
        $formattedData = $this->guestManagementService->formatExportData($guests);

        $format = $request->get('format');
        $filename = 'guests_export_'.now()->format('Y-m-d_His').'.'.$format;

        if ($format === 'csv') {
            // Generate CSV
            return response()->streamDownload(function () use ($formattedData) {
                $handle = fopen('php://output', 'w');

                // Add headers
                if (! empty($formattedData)) {
                    fputcsv($handle, array_keys($formattedData[0]));
                }

                // Add data rows
                foreach ($formattedData as $row) {
                    fputcsv($handle, $row);
                }

                fclose($handle);
            }, $filename, [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            ]);
        } else {
            // Generate Excel using Laravel Excel
            $export = new class($formattedData) implements \Maatwebsite\Excel\Concerns\FromArray, \Maatwebsite\Excel\Concerns\WithHeadings
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

                public function headings(): array
                {
                    if (empty($this->data)) {
                        return [];
                    }

                    return array_keys($this->data[0]);
                }
            };

            return Excel::download($export, $filename);
        }
    }

    /**
     * Import guests from uploaded file.
     */
    public function import(Request $request): JsonResponse
    {
        Gate::authorize('viewAnyGuests', [\App\Models\Member::class]);

        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:10240', // 10MB max
        ]);

        $user = auth()->user();

        // Determine branch ID for non-super-admin users
        $branchId = null;
        if (! $user->isSuperAdmin()) {
            $branchId = $user->getPrimaryBranch()?->id;
        } else {
            // For super admin, require branch_id in request
            $request->validate([
                'branch_id' => 'required|integer|exists:branches,id',
            ]);
            $branchId = $request->get('branch_id');
        }

        if (! $branchId) {
            return response()->json([
                'success' => false,
                'message' => 'Branch ID is required for guest import.',
            ], 422);
        }

        try {
            $result = $this->guestManagementService->importGuests(
                $request->file('file'),
                $branchId
            );

            $statusCode = $result['success'] ? 200 : 422;

            return response()->json($result, $statusCode);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Guest import API error', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred during import: '.$e->getMessage(),
                'errors' => ['system' => 'Import operation failed'],
            ], 500);
        }
    }

    /**
     * Send account setup emails to guests.
     */
    public function sendAccountSetupEmails(Request $request): JsonResponse
    {
        Gate::authorize('viewAnyGuests', [\App\Models\Member::class]);

        $request->validate([
            'member_ids' => 'nullable|array',
            'member_ids.*' => 'integer|exists:members,id',
            'send_to_all' => 'nullable|boolean',
        ]);

        $user = auth()->user();

        // Determine branch filter for non-super-admin users
        $branchId = null;
        if (! $user->isSuperAdmin()) {
            $branchId = $user->getPrimaryBranch()?->id;
        } else {
            // For super admin, branch_id is optional
            if ($request->has('branch_id')) {
                $request->validate([
                    'branch_id' => 'required|integer|exists:branches,id',
                ]);
                $branchId = $request->get('branch_id');
            }
        }

        try {
            $memberIds = null;
            if ($request->get('send_to_all') !== true) {
                $memberIds = $request->get('member_ids');
                if (empty($memberIds)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Please select guests to send emails to, or use "send_to_all" option.',
                    ], 422);
                }
            }

            $result = $this->guestManagementService->sendAccountSetupEmailsToGuests($branchId, $memberIds);

            return response()->json([
                'success' => true,
                'message' => "Account setup emails queued successfully. {$result['sent']} email(s) will be sent.",
                'data' => $result,
            ]);

        } catch (\Exception $e) {
            \Log::error('Failed to send account setup emails', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while sending account setup emails: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Download guest import template.
     */
    public function downloadTemplate(): \Symfony\Component\HttpFoundation\BinaryFileResponse|JsonResponse
    {
        Gate::authorize('viewAnyGuests', [\App\Models\Member::class]);

        try {
            $result = $this->guestManagementService->getGuestImportTemplate();

            if (! $result['success']) {
                return response()->json($result, 500);
            }

            // Get the file path from the service result
            $filePath = storage_path('app/public/'.$result['file_path']);

            if (! file_exists($filePath)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Template file not found',
                ], 404);
            }

            return response()->download($filePath, $result['filename'])->deleteFileAfterSend();

        } catch (\Exception $e) {
            \Log::error('Get guest import template API error', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while generating template',
            ], 500);
        }
    }
}
