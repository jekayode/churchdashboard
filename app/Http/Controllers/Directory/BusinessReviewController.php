<?php

declare(strict_types=1);

namespace App\Http\Controllers\Directory;

use App\Enums\ReviewStatus;
use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\BusinessReview;
use App\Models\BusinessReviewReply;
use App\Models\DirectorySetting;
use App\Notifications\BusinessReviewModerated;
use App\Notifications\BusinessReviewReceived;
use App\Services\BusinessReviewRatingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

final class BusinessReviewController extends Controller
{
    public function __construct(private readonly BusinessReviewRatingService $ratingService) {}

    public function index(Business $business): JsonResponse
    {
        $reviews = $business->reviews()
            ->approved()
            ->with('user:id,name')
            ->latest()
            ->paginate(10);

        return response()->json(['success' => true, 'data' => $reviews]);
    }

    public function store(Request $request, Business $business): JsonResponse
    {
        $user = $request->user();
        abort_unless($user, 401);
        abort_unless($business->isPubliclyVisible(), 404);
        abort_if($business->isOwnedBy($user), 403, 'You cannot review your own business.');

        $data = $request->validate([
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'title' => ['nullable', 'string', 'max:255'],
            'body' => ['nullable', 'string'],
        ]);

        $settings = DirectorySetting::instance();
        $status = $settings->reviews_require_approval
            ? ReviewStatus::Pending
            : ReviewStatus::Approved;

        $review = BusinessReview::query()
            ->where('business_id', $business->id)
            ->where('user_id', $user->id)
            ->first();

        $isNew = ! $review;
        $priorStatus = $review?->status;

        $review = ($review ?? new BusinessReview);
        $review->fill(array_merge($data, ['status' => $status]));
        $review->business_id = $business->id;
        $review->user_id = $user->id;
        $review->save();

        if ($status === ReviewStatus::Approved || $priorStatus === ReviewStatus::Approved) {
            $this->ratingService->recalculate($business);
        }

        if ($isNew) {
            $business->owner->notify(new BusinessReviewReceived($review->loadMissing('business')));
        }

        return response()->json(['success' => true, 'data' => $review, 'message' => 'Review submitted.'], 201);
    }

    public function moderate(Request $request, BusinessReview $review): JsonResponse
    {
        Gate::authorize('moderate', $review);

        $data = $request->validate([
            'status' => ['required', 'in:approved,hidden,pending'],
        ]);

        $review->update(['status' => ReviewStatus::from($data['status'])]);
        $this->ratingService->recalculate($review->business);
        $review->user->notify(new BusinessReviewModerated($review));

        return response()->json(['success' => true, 'data' => $review, 'message' => 'Review updated.']);
    }

    public function reply(Request $request, Business $business, BusinessReview $review): JsonResponse
    {
        Gate::authorize('update', $business);
        abort_unless($review->business_id === $business->id, 404);

        $data = $request->validate([
            'body' => ['required', 'string', 'max:2000'],
        ]);

        if (BusinessReviewReply::query()->where('business_review_id', $review->id)->exists()) {
            abort(409, 'Reply already submitted for this review.');
        }

        $reply = BusinessReviewReply::query()->create([
            'business_review_id' => $review->id,
            'business_id' => $business->id,
            'owner_user_id' => $request->user()->id,
            'body' => $data['body'],
        ]);

        return response()->json(['success' => true, 'data' => $reply, 'message' => 'Reply submitted.']);
    }

    public function adminIndex(Request $request): JsonResponse
    {
        abort_unless($request->user()?->isDirectoryAdmin(), 403);

        $reviews = BusinessReview::query()
            ->with(['business:id,name,slug', 'user:id,name'])
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->latest()
            ->paginate(20);

        return response()->json(['success' => true, 'data' => $reviews]);
    }
}
