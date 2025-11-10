<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\AddGuestFollowUpRequest;
use App\Http\Requests\UpdateGuestStatusRequest;
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
        Gate::authorize('viewAnyGuests', [\App\Models\Member::class]);

        $user = auth()->user();
        
        // Determine branch filter for non-super-admin users
        $branchId = null;
        if (!$user->isSuperAdmin()) {
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
        $filters = array_filter($filters, fn($value) => !is_null($value) && $value !== '');

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
     * API endpoint for AJAX guest listing.
     */
    public function getGuests(Request $request): JsonResponse
    {
        Gate::authorize('viewAnyGuests', [\App\Models\Member::class]);

        $user = auth()->user();
        
        // Determine branch filter for non-super-admin users
        $branchId = null;
        if (!$user->isSuperAdmin()) {
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
        $filters = array_filter($filters, fn($value) => !is_null($value) && $value !== '');

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

        if (!$guest) {
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

        if (!$success) {
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
        if (!$user->isSuperAdmin()) {
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
        $filters = array_filter($filters, fn($value) => !is_null($value) && $value !== '');

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
                if (!empty($formattedData)) {
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
}

