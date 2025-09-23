<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\EmailCampaign;
use App\Models\EmailCampaignStep;
use App\Models\MessageTemplate;
use App\Models\User;
use App\Services\EmailCampaignService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

final class EmailCampaignController extends Controller
{
    public function __construct(
        private readonly EmailCampaignService $campaignService
    ) {}

    /**
     * Display a listing of email campaigns.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $branchId = $request->integer('branch_id');
        $perPage = min($request->integer('per_page', 15), 100);

        // Authorization
        if (! $user->isSuperAdmin() && (! $branchId || $user->getActiveBranchId() !== $branchId)) {
            $branchId = $user->getActiveBranchId();
        }

        if (! $branchId) {
            return response()->json(['error' => 'Branch not specified'], 400);
        }

        $branch = Branch::findOrFail($branchId);
        Gate::authorize('view', $branch);

        $query = EmailCampaign::where('branch_id', $branch->id)
            ->withCount(['steps', 'enrollments', 'activeEnrollments', 'completedEnrollments']);

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where('name', 'like', "%{$search}%");
        }

        if ($request->filled('trigger_event')) {
            $query->where('trigger_event', $request->input('trigger_event'));
        }

        if ($request->filled('active')) {
            $query->where('is_active', $request->boolean('active'));
        }

        $campaigns = $query->orderBy('created_at', 'desc')->paginate($perPage);

        return response()->json([
            'campaigns' => $campaigns->items(),
            'pagination' => [
                'current_page' => $campaigns->currentPage(),
                'last_page' => $campaigns->lastPage(),
                'per_page' => $campaigns->perPage(),
                'total' => $campaigns->total(),
            ],
            'branch' => [
                'id' => $branch->id,
                'name' => $branch->name,
            ],
            'trigger_events' => [
                'guest-registration' => 'Guest Registration',
                'member-created' => 'New Member Created',
                'custom' => 'Manual Trigger',
            ],
        ]);
    }

    /**
     * Store a newly created email campaign.
     */
    public function store(Request $request): JsonResponse
    {
        $user = $request->user();
        $branchId = $request->integer('branch_id');

        // Authorization
        if (! $user->isSuperAdmin() && (! $branchId || $user->getActiveBranchId() !== $branchId)) {
            $branchId = $user->getActiveBranchId();
        }

        if (! $branchId) {
            return response()->json(['error' => 'Branch not specified'], 400);
        }

        $branch = Branch::findOrFail($branchId);
        Gate::authorize('manageSettings', $branch);

        // Validation
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'trigger_event' => 'required|in:guest-registration,member-created,custom',
            'is_active' => 'boolean',
            'steps' => 'nullable|array|min:1',
            'steps.*.step_order' => 'required|integer|min:1',
            'steps.*.delay_days' => 'required|integer|min:0',
            'steps.*.template_id' => 'required|exists:message_templates,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Check for duplicate names
        $existingCampaign = EmailCampaign::where('branch_id', $branch->id)
            ->where('name', $request->input('name'))
            ->first();

        if ($existingCampaign) {
            return response()->json(['errors' => ['name' => ['Campaign name already exists']]], 422);
        }

        // Validate templates belong to the branch
        if ($request->filled('steps')) {
            $templateIds = collect($request->input('steps'))->pluck('template_id');
            $validTemplates = MessageTemplate::where('branch_id', $branch->id)
                ->where('type', 'email')
                ->whereIn('id', $templateIds)
                ->count();

            if ($validTemplates !== $templateIds->count()) {
                return response()->json(['errors' => ['steps' => ['Some templates do not belong to this branch or are not email templates']]], 422);
            }
        }

        try {
            DB::beginTransaction();

            $campaign = EmailCampaign::create([
                'branch_id' => $branch->id,
                'name' => $request->input('name'),
                'trigger_event' => $request->input('trigger_event'),
                'is_active' => $request->boolean('is_active', false),
            ]);

            // Create campaign steps
            if ($request->filled('steps')) {
                foreach ($request->input('steps') as $stepData) {
                    EmailCampaignStep::create([
                        'campaign_id' => $campaign->id,
                        'step_order' => $stepData['step_order'],
                        'delay_days' => $stepData['delay_days'],
                        'template_id' => $stepData['template_id'],
                    ]);
                }
            }

            DB::commit();

            Log::info('Email campaign created', [
                'campaign_id' => $campaign->id,
                'branch_id' => $branch->id,
                'user_id' => $user->id,
                'name' => $campaign->name,
                'trigger_event' => $campaign->trigger_event,
            ]);

            return response()->json([
                'message' => 'Campaign created successfully',
                'campaign' => $campaign->load('steps.template'),
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Failed to create email campaign', [
                'branch_id' => $branch->id,
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json(['error' => 'Failed to create campaign'], 500);
        }
    }

    /**
     * Display the specified email campaign.
     */
    public function show(EmailCampaign $campaign): JsonResponse
    {
        Gate::authorize('view', $campaign->branch);

        $campaign->load(['steps.template', 'branch']);
        $campaign->loadCount(['enrollments', 'activeEnrollments', 'completedEnrollments']);

        $statistics = $this->campaignService->getCampaignStatistics($campaign);

        return response()->json([
            'campaign' => $campaign,
            'statistics' => $statistics,
        ]);
    }

    /**
     * Update the specified email campaign.
     */
    public function update(Request $request, EmailCampaign $campaign): JsonResponse
    {
        Gate::authorize('manage', $campaign->branch);

        // Validation
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'trigger_event' => 'required|in:guest-registration,member-created,custom',
            'is_active' => 'boolean',
            'steps' => 'nullable|array|min:1',
            'steps.*.step_order' => 'required|integer|min:1',
            'steps.*.delay_days' => 'required|integer|min:0',
            'steps.*.template_id' => 'required|exists:message_templates,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Check for duplicate names (excluding current campaign)
        $existingCampaign = EmailCampaign::where('branch_id', $campaign->branch_id)
            ->where('name', $request->input('name'))
            ->where('id', '!=', $campaign->id)
            ->first();

        if ($existingCampaign) {
            return response()->json(['errors' => ['name' => ['Campaign name already exists']]], 422);
        }

        // Validate templates belong to the branch
        if ($request->filled('steps')) {
            $templateIds = collect($request->input('steps'))->pluck('template_id');
            $validTemplates = MessageTemplate::where('branch_id', $campaign->branch_id)
                ->where('type', 'email')
                ->whereIn('id', $templateIds)
                ->count();

            if ($validTemplates !== $templateIds->count()) {
                return response()->json(['errors' => ['steps' => ['Some templates do not belong to this branch or are not email templates']]], 422);
            }
        }

        try {
            DB::beginTransaction();

            $campaign->update([
                'name' => $request->input('name'),
                'trigger_event' => $request->input('trigger_event'),
                'is_active' => $request->boolean('is_active', false),
            ]);

            // Update campaign steps
            if ($request->filled('steps')) {
                // Delete existing steps
                $campaign->steps()->delete();

                // Create new steps
                foreach ($request->input('steps') as $stepData) {
                    EmailCampaignStep::create([
                        'campaign_id' => $campaign->id,
                        'step_order' => $stepData['step_order'],
                        'delay_days' => $stepData['delay_days'],
                        'template_id' => $stepData['template_id'],
                    ]);
                }
            }

            DB::commit();

            Log::info('Email campaign updated', [
                'campaign_id' => $campaign->id,
                'branch_id' => $campaign->branch_id,
                'user_id' => $request->user()->id,
                'name' => $campaign->name,
            ]);

            return response()->json([
                'message' => 'Campaign updated successfully',
                'campaign' => $campaign->fresh()->load('steps.template'),
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Failed to update email campaign', [
                'campaign_id' => $campaign->id,
                'user_id' => $request->user()->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json(['error' => 'Failed to update campaign'], 500);
        }
    }

    /**
     * Remove the specified email campaign.
     */
    public function destroy(EmailCampaign $campaign): JsonResponse
    {
        Gate::authorize('manage', $campaign->branch);

        // Check if campaign has active enrollments
        $activeEnrollments = $campaign->enrollments()->whereNull('completed_at')->count();
        if ($activeEnrollments > 0) {
            return response()->json([
                'error' => "Cannot delete campaign. It has {$activeEnrollments} active enrollment(s).",
            ], 422);
        }

        try {
            $campaignName = $campaign->name;
            $branchId = $campaign->branch_id;
            $campaign->delete();

            Log::info('Email campaign deleted', [
                'campaign_name' => $campaignName,
                'branch_id' => $branchId,
                'user_id' => request()->user()->id,
            ]);

            return response()->json(['message' => 'Campaign deleted successfully']);

        } catch (\Exception $e) {
            Log::error('Failed to delete email campaign', [
                'campaign_id' => $campaign->id,
                'user_id' => request()->user()->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json(['error' => 'Failed to delete campaign'], 500);
        }
    }

    /**
     * Manually trigger campaign for a user.
     */
    public function triggerForUser(Request $request, EmailCampaign $campaign): JsonResponse
    {
        Gate::authorize('manage', $campaign->branch);

        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = User::findOrFail($request->integer('user_id'));

        // Ensure user belongs to the same branch
        if ($user->getActiveBranchId() !== $campaign->branch_id) {
            return response()->json(['error' => 'User does not belong to this branch'], 422);
        }

        try {
            $enrollment = $this->campaignService->triggerCampaignForUser($campaign, $user);

            return response()->json([
                'message' => 'Campaign triggered successfully for user',
                'enrollment' => $enrollment,
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    /**
     * Stop campaign for a user.
     */
    public function stopForUser(Request $request, EmailCampaign $campaign): JsonResponse
    {
        Gate::authorize('manage', $campaign->branch);

        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = User::findOrFail($request->integer('user_id'));

        try {
            $stopped = $this->campaignService->stopCampaignForUser($campaign, $user);

            if ($stopped) {
                return response()->json(['message' => 'Campaign stopped for user']);
            } else {
                return response()->json(['error' => 'User is not enrolled in this campaign'], 422);
            }

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Preview campaign step.
     */
    public function previewStep(Request $request, EmailCampaign $campaign): JsonResponse
    {
        Gate::authorize('view', $campaign->branch);

        $validator = Validator::make($request->all(), [
            'step_order' => 'required|integer|min:1',
            'user_id' => 'nullable|exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = $request->filled('user_id')
            ? User::find($request->integer('user_id'))
            : $request->user();

        try {
            $preview = $this->campaignService->previewCampaignStep(
                $campaign,
                $request->integer('step_order'),
                $user
            );

            return response()->json(['preview' => $preview]);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 404);
        }
    }

    /**
     * Clone a campaign.
     */
    public function clone(Request $request, EmailCampaign $campaign): JsonResponse
    {
        Gate::authorize('manage', $campaign->branch);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Check for duplicate names
        $existingCampaign = EmailCampaign::where('branch_id', $campaign->branch_id)
            ->where('name', $request->input('name'))
            ->first();

        if ($existingCampaign) {
            return response()->json(['errors' => ['name' => ['Campaign name already exists']]], 422);
        }

        try {
            $clonedCampaign = $this->campaignService->cloneCampaign(
                $campaign,
                $request->input('name')
            );

            Log::info('Email campaign cloned', [
                'original_campaign_id' => $campaign->id,
                'cloned_campaign_id' => $clonedCampaign->id,
                'branch_id' => $campaign->branch_id,
                'user_id' => $request->user()->id,
            ]);

            return response()->json([
                'message' => 'Campaign cloned successfully',
                'campaign' => $clonedCampaign->load('steps.template'),
            ], 201);

        } catch (\Exception $e) {
            Log::error('Failed to clone email campaign', [
                'campaign_id' => $campaign->id,
                'user_id' => $request->user()->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json(['error' => 'Failed to clone campaign'], 500);
        }
    }

    /**
     * Get campaign enrollments.
     */
    public function enrollments(Request $request, EmailCampaign $campaign): JsonResponse
    {
        Gate::authorize('view', $campaign->branch);

        $perPage = min($request->integer('per_page', 15), 100);

        $query = $campaign->enrollments()->with('user');

        if ($request->filled('status')) {
            $status = $request->input('status');
            if ($status === 'active') {
                $query->whereNull('completed_at');
            } elseif ($status === 'completed') {
                $query->whereNotNull('completed_at');
            }
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $enrollments = $query->orderBy('created_at', 'desc')->paginate($perPage);

        return response()->json([
            'enrollments' => $enrollments->items(),
            'pagination' => [
                'current_page' => $enrollments->currentPage(),
                'last_page' => $enrollments->lastPage(),
                'per_page' => $enrollments->perPage(),
                'total' => $enrollments->total(),
            ],
        ]);
    }
}
