<?php

declare(strict_types=1);

namespace App\Http\Controllers\Directory\Admin;

use App\Enums\BusinessStatus;
use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Notifications\BusinessStatusChanged;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class DirectoryBusinessController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        abort_unless($request->user()?->isDirectoryAdmin(), 403);

        $businesses = Business::query()
            ->with(['owner:id,name,email', 'categories:id,name'])
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->when($request->filled('search'), function ($q) use ($request) {
                $search = $request->search;
                $q->where('name', 'like', "%{$search}%");
            })
            ->latest()
            ->paginate($request->integer('per_page', 20));

        return response()->json(['success' => true, 'data' => $businesses]);
    }

    public function approve(Request $request, Business $business): JsonResponse
    {
        abort_unless($request->user()?->can('approve', $business), 403);

        $business->update([
            'status' => BusinessStatus::Active,
            'approved_at' => now(),
            'approved_by_user_id' => $request->user()->id,
            'rejection_reason' => null,
        ]);

        $business->owner->notify(new BusinessStatusChanged($business, 'approved'));

        return response()->json(['success' => true, 'data' => $business, 'message' => 'Business approved.']);
    }

    public function reject(Request $request, Business $business): JsonResponse
    {
        abort_unless($request->user()?->can('approve', $business), 403);

        $reason = $request->input('reason');
        abort_unless(is_string($reason) && trim($reason) !== '', 422);

        $business->update([
            'status' => BusinessStatus::Rejected,
            'rejection_reason' => trim($reason),
        ]);
        $business->owner->notify(new BusinessStatusChanged($business, 'rejected', trim($reason)));

        return response()->json(['success' => true, 'data' => $business, 'message' => 'Business rejected.']);
    }

    public function toggleActive(Request $request, Business $business): JsonResponse
    {
        abort_unless($request->user()?->isDirectoryAdmin(), 403);

        $newStatus = $business->status === BusinessStatus::Active
            ? BusinessStatus::Inactive
            : BusinessStatus::Active;

        $business->update(['status' => $newStatus]);
        $business->owner->notify(new BusinessStatusChanged($business, $newStatus->value));

        return response()->json(['success' => true, 'data' => $business, 'message' => 'Business status updated.']);
    }

    public function toggleFeatured(Request $request, Business $business): JsonResponse
    {
        abort_unless($request->user()?->can('feature', $business), 403);

        $business->update([
            'is_featured' => ! $business->is_featured,
            'featured_until' => $request->input('featured_until'),
        ]);

        return response()->json(['success' => true, 'data' => $business, 'message' => 'Featured status updated.']);
    }

    public function deactivateOwner(Request $request, int $userId): JsonResponse
    {
        abort_unless($request->user()?->isDirectoryAdmin(), 403);

        Business::query()
            ->where('owner_user_id', $userId)
            ->update(['owner_deactivated' => true, 'status' => BusinessStatus::Inactive]);

        return response()->json(['success' => true, 'message' => 'Owner businesses deactivated.']);
    }

    public function stats(Request $request): JsonResponse
    {
        abort_unless($request->user()?->isDirectoryAdmin(), 403);

        return response()->json([
            'success' => true,
            'data' => [
                'total' => Business::count(),
                'pending' => Business::where('status', BusinessStatus::PendingReview)->count(),
                'active' => Business::where('status', BusinessStatus::Active)->count(),
                'featured' => Business::featured()->count(),
                'pending_reviews' => \App\Models\BusinessReview::where('status', 'pending')->count(),
            ],
        ]);
    }
}
