<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\BranchReportToken;
use App\Models\Event;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;

final class BranchReportTokenController extends Controller
{
    /**
     * Get all tokens for a branch.
     */
    public function index(Request $request): JsonResponse
    {
        Gate::authorize('viewReports', [\App\Models\User::class]);

        $user = auth()->user();
        $branchId = $user->isSuperAdmin() ? $request->get('branch_id') : $user->getActiveBranchId();

        if (! $branchId) {
            return response()->json([
                'success' => false,
                'message' => 'No branch specified.',
            ], 422);
        }

        $tokens = BranchReportToken::forBranch($branchId)
            ->with('branch:id,name')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $tokens,
        ]);
    }

    /**
     * Create a new report token.
     */
    public function store(Request $request): JsonResponse
    {
        Gate::authorize('createReports', [\App\Models\User::class]);

        $validated = $request->validate([
            'branch_id' => 'required|exists:branches,id',
            'event_id' => 'nullable|exists:events,id',
            'token_type' => 'required|in:individual,team',
            'name' => 'required_if:token_type,individual|string|max:255',
            'email' => 'nullable|email|max:255',
            'team_name' => 'required_if:token_type,team|string|max:255',
            'team_emails' => 'required_if:token_type,team|array|min:1',
            'team_emails.*' => 'email|max:255',
            'team_roles' => 'required_if:token_type,team|array|min:1',
            'team_roles.*' => 'string|max:255',
            'allowed_events' => 'nullable|array',
            'allowed_events.*' => 'exists:events,id',
            'expires_at' => 'nullable|date|after:now',
        ]);

        $user = auth()->user();

        // Check if user has access to this branch
        if (! $user->isSuperAdmin() && $user->getActiveBranchId() !== (int) $validated['branch_id']) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to create tokens for this branch.',
            ], 403);
        }

        // If event_id is provided, validate that the event belongs to the specified branch
        if ($validated['event_id']) {
            $event = Event::find($validated['event_id']);
            if (! $event || $event->branch_id !== (int) $validated['branch_id']) {
                return response()->json([
                    'success' => false,
                    'message' => 'The specified event does not belong to this branch.',
                ], 422);
            }
        }

        try {
            if ($validated['token_type'] === 'team') {
                // Create team token
                $token = BranchReportToken::createTeamTokenForBranch(
                    (int) $validated['branch_id'],
                    $validated['team_name'],
                    $validated['team_emails'],
                    $validated['team_roles'],
                    $validated['allowed_events'] ?? null,
                    $validated['expires_at'] ? new \DateTime($validated['expires_at']) : null,
                    $validated['event_id'] ? (int) $validated['event_id'] : null
                );
            } else {
                // Create individual token
                $token = BranchReportToken::createForBranch(
                    (int) $validated['branch_id'],
                    $validated['name'],
                    $validated['email'] ?? null,
                    $validated['allowed_events'] ?? null,
                    $validated['expires_at'] ? new \DateTime($validated['expires_at']) : null,
                    $validated['event_id'] ? (int) $validated['event_id'] : null
                );
            }

            Log::info('Branch report token created', [
                'token_id' => $token->id,
                'branch_id' => $validated['branch_id'],
                'created_by' => $user->id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Report submission link created successfully.',
                'data' => $token->load('branch:id,name'),
            ], 201);

        } catch (\Exception $e) {
            Log::error('Failed to create branch report token', [
                'branch_id' => $validated['branch_id'],
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to create report submission link.',
            ], 500);
        }
    }

    /**
     * Update a report token.
     */
    public function update(Request $request, BranchReportToken $token): JsonResponse
    {
        Gate::authorize('updateReports', [\App\Models\User::class]);

        $user = auth()->user();

        // Check if user has access to this token's branch
        if (! $user->isSuperAdmin() && $user->getActiveBranchId() !== $token->branch_id) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to update this token.',
            ], 403);
        }

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'email' => 'nullable|email|max:255',
            'is_active' => 'sometimes|boolean',
            'allowed_events' => 'nullable|array',
            'allowed_events.*' => 'exists:events,id',
            'expires_at' => 'nullable|date|after:now',
        ]);

        try {
            $token->update($validated);

            Log::info('Branch report token updated', [
                'token_id' => $token->id,
                'branch_id' => $token->branch_id,
                'updated_by' => $user->id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Report submission link updated successfully.',
                'data' => $token->load('branch:id,name'),
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to update branch report token', [
                'token_id' => $token->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update report submission link.',
            ], 500);
        }
    }

    /**
     * Delete a report token.
     */
    public function destroy(BranchReportToken $token): JsonResponse
    {
        Gate::authorize('deleteReports', [\App\Models\User::class]);

        $user = auth()->user();

        // Check if user has access to this token's branch
        if (! $user->isSuperAdmin() && $user->getActiveBranchId() !== $token->branch_id) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to delete this token.',
            ], 403);
        }

        try {
            $token->delete();

            Log::info('Branch report token deleted', [
                'token_id' => $token->id,
                'branch_id' => $token->branch_id,
                'deleted_by' => $user->id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Report submission link deleted successfully.',
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to delete branch report token', [
                'token_id' => $token->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete report submission link.',
            ], 500);
        }
    }

    /**
     * Get available events for a branch.
     */
    public function getAvailableEvents(Request $request): JsonResponse
    {
        Gate::authorize('viewReports', [\App\Models\User::class]);

        $user = auth()->user();
        $branchId = $user->isSuperAdmin() ? $request->get('branch_id') : $user->getActiveBranchId();

        if (! $branchId) {
            return response()->json([
                'success' => false,
                'message' => 'No branch specified.',
            ], 422);
        }

        $events = Event::where('branch_id', $branchId)
            ->where('is_published', true)
            ->where('status', 'active')
            ->orderBy('start_date', 'desc')
            ->get(['id', 'name', 'type', 'service_type', 'start_date', 'end_date']);

        return response()->json([
            'success' => true,
            'data' => $events,
        ]);
    }

    /**
     * Regenerate a token.
     */
    public function regenerate(BranchReportToken $token): JsonResponse
    {
        Gate::authorize('updateReports', [\App\Models\User::class]);

        $user = auth()->user();

        // Check if user has access to this token's branch
        if (! $user->isSuperAdmin() && $user->getActiveBranchId() !== $token->branch_id) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to regenerate this token.',
            ], 403);
        }

        try {
            $oldToken = $token->token;
            $token->update([
                'token' => BranchReportToken::generateToken(),
            ]);

            Log::info('Branch report token regenerated', [
                'token_id' => $token->id,
                'branch_id' => $token->branch_id,
                'old_token' => $oldToken,
                'new_token' => $token->token,
                'regenerated_by' => $user->id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Report submission link regenerated successfully.',
                'data' => $token->load('branch:id,name'),
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to regenerate branch report token', [
                'token_id' => $token->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to regenerate report submission link.',
            ], 500);
        }
    }

    /**
     * Generate a report token for a specific event.
     */
    public function generateEventToken(Request $request, Event $event): JsonResponse
    {
        Gate::authorize('createReports', [\App\Models\User::class]);

        $user = auth()->user();

        // Check if user has access to this event's branch
        if (! $user->isSuperAdmin() && $user->getActiveBranchId() !== $event->branch_id) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to create tokens for this event.',
            ], 403);
        }

        $validated = $request->validate([
            'token_type' => 'required|in:individual,team',
            'name' => 'required_if:token_type,individual|string|max:255',
            'email' => 'nullable|email|max:255',
            'team_name' => 'required_if:token_type,team|string|max:255',
            'team_emails' => 'required_if:token_type,team|array|min:1',
            'team_emails.*' => 'email|max:255',
            'team_roles' => 'required_if:token_type,team|array|min:1',
            'team_roles.*' => 'string|max:255',
            'expires_at' => 'nullable|date|after:now',
        ]);

        try {
            if ($validated['token_type'] === 'team') {
                // Create team token for event
                $token = BranchReportToken::createTeamTokenForBranch(
                    $event->branch_id,
                    $validated['team_name'],
                    $validated['team_emails'],
                    $validated['team_roles'],
                    null, // No additional allowed_events for event-specific tokens
                    $validated['expires_at'] ? new \DateTime($validated['expires_at']) : null,
                    $event->id
                );
            } else {
                // Create individual token for event
                $token = BranchReportToken::createForBranch(
                    $event->branch_id,
                    $validated['name'],
                    $validated['email'] ?? null,
                    null, // No additional allowed_events for event-specific tokens
                    $validated['expires_at'] ? new \DateTime($validated['expires_at']) : null,
                    $event->id
                );
            }

            Log::info('Event-specific report token created', [
                'token_id' => $token->id,
                'event_id' => $event->id,
                'branch_id' => $event->branch_id,
                'created_by' => $user->id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Event report submission link created successfully.',
                'data' => [
                    'token' => $token->load(['event:id,name', 'branch:id,name']),
                    'submission_url' => $token->getSubmissionUrl(),
                ],
            ], 201);

        } catch (\Exception $e) {
            Log::error('Failed to create event-specific report token', [
                'event_id' => $event->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to create event report submission link.',
            ], 500);
        }
    }
}
