<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\MessageTemplate;
use App\Services\CommunicationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

final class MessageTemplateController extends Controller
{
    public function __construct(
        private readonly CommunicationService $communicationService
    ) {}

    /**
     * Display a listing of message templates.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $branchId = $request->integer('branch_id');
        $type = $request->input('type'); // 'email' or 'sms'
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

        $query = MessageTemplate::where('branch_id', $branch->id);

        if ($type) {
            $query->where('type', $type);
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('subject', 'like', "%{$search}%")
                    ->orWhere('content', 'like', "%{$search}%");
            });
        }

        if ($request->filled('active')) {
            $query->where('is_active', $request->boolean('active'));
        }

        $templates = $query->orderBy('name')->paginate($perPage);

        return response()->json([
            'data' => $templates->items(),
            'templates' => $templates->items(), // Keep both for compatibility
            'pagination' => [
                'current_page' => $templates->currentPage(),
                'last_page' => $templates->lastPage(),
                'per_page' => $templates->perPage(),
                'total' => $templates->total(),
            ],
            'branch' => [
                'id' => $branch->id,
                'name' => $branch->name,
            ],
        ]);
    }

    /**
     * Store a newly created message template.
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
            'type' => 'required|in:email,sms',
            'subject' => 'nullable|string|max:255|required_if:type,email',
            'content' => 'required|string',
            'variables' => 'nullable|array',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Check for duplicate names
        $existingTemplate = MessageTemplate::where('branch_id', $branch->id)
            ->where('name', $request->input('name'))
            ->where('type', $request->input('type'))
            ->first();

        if ($existingTemplate) {
            return response()->json(['errors' => ['name' => ['Template name already exists for this type']]], 422);
        }

        try {
            $template = MessageTemplate::create([
                'branch_id' => $branch->id,
                'name' => $request->input('name'),
                'type' => $request->input('type'),
                'subject' => $request->input('subject'),
                'content' => $request->input('content'),
                'variables' => $request->input('variables', []),
                'is_active' => $request->boolean('is_active', true),
            ]);

            Log::info('Message template created', [
                'template_id' => $template->id,
                'branch_id' => $branch->id,
                'user_id' => $user->id,
                'name' => $template->name,
                'type' => $template->type,
            ]);

            return response()->json([
                'message' => 'Template created successfully',
                'template' => $template,
            ], 201);

        } catch (\Exception $e) {
            Log::error('Failed to create message template', [
                'branch_id' => $branch->id,
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json(['error' => 'Failed to create template'], 500);
        }
    }

    /**
     * Display the specified message template.
     */
    public function show(MessageTemplate $template): JsonResponse
    {
        Gate::authorize('view', $template->branch);

        return response()->json([
            'template' => $template,
            'branch' => [
                'id' => $template->branch->id,
                'name' => $template->branch->name,
            ],
        ]);
    }

    /**
     * Update the specified message template.
     */
    public function update(Request $request, MessageTemplate $template): JsonResponse
    {
        Gate::authorize('manage', $template->branch);

        // Validation
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'type' => 'required|in:email,sms',
            'subject' => 'nullable|string|max:255|required_if:type,email',
            'content' => 'required|string',
            'variables' => 'nullable|array',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Check for duplicate names (excluding current template)
        $existingTemplate = MessageTemplate::where('branch_id', $template->branch_id)
            ->where('name', $request->input('name'))
            ->where('type', $request->input('type'))
            ->where('id', '!=', $template->id)
            ->first();

        if ($existingTemplate) {
            return response()->json(['errors' => ['name' => ['Template name already exists for this type']]], 422);
        }

        try {
            $template->update([
                'name' => $request->input('name'),
                'type' => $request->input('type'),
                'subject' => $request->input('subject'),
                'content' => $request->input('content'),
                'variables' => $request->input('variables', []),
                'is_active' => $request->boolean('is_active', true),
            ]);

            Log::info('Message template updated', [
                'template_id' => $template->id,
                'branch_id' => $template->branch_id,
                'user_id' => $request->user()->id,
                'name' => $template->name,
                'type' => $template->type,
            ]);

            return response()->json([
                'message' => 'Template updated successfully',
                'template' => $template->fresh(),
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to update message template', [
                'template_id' => $template->id,
                'user_id' => $request->user()->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json(['error' => 'Failed to update template'], 500);
        }
    }

    /**
     * Remove the specified message template.
     */
    public function destroy(MessageTemplate $template): JsonResponse
    {
        Gate::authorize('manage', $template->branch);

        // Check if template is being used in campaigns
        $campaignStepsCount = $template->campaignSteps()->count();
        if ($campaignStepsCount > 0) {
            return response()->json([
                'error' => "Cannot delete template. It is being used in {$campaignStepsCount} campaign step(s).",
            ], 422);
        }

        try {
            $templateName = $template->name;
            $branchId = $template->branch_id;
            $template->delete();

            Log::info('Message template deleted', [
                'template_name' => $templateName,
                'branch_id' => $branchId,
                'user_id' => request()->user()->id,
            ]);

            return response()->json(['message' => 'Template deleted successfully']);

        } catch (\Exception $e) {
            Log::error('Failed to delete message template', [
                'template_id' => $template->id,
                'user_id' => request()->user()->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json(['error' => 'Failed to delete template'], 500);
        }
    }

    /**
     * Preview template with sample data.
     */
    public function preview(Request $request, MessageTemplate $template): JsonResponse
    {
        Gate::authorize('view', $template->branch);

        $user = $request->user();
        $branch = $template->branch;

        // Sample variables for preview
        $sampleVariables = [
            'recipient_name' => 'John Doe',
            'recipient_email' => 'john@example.com',
            'event_name' => 'Sunday Service',
            'event_date' => 'Sunday, December 25, 2024',
            'custom_message' => 'This is a sample custom message',
        ];

        try {
            // Process subject if it exists
            $processedSubject = null;
            if ($template->subject) {
                $processedSubject = $this->communicationService->processTemplateVariables(
                    $template->subject,
                    $sampleVariables,
                    $branch,
                    $user
                );
            }

            // Process content
            $processedContent = $this->communicationService->processTemplateVariables(
                $template->content,
                $sampleVariables,
                $branch,
                $user
            );

            return response()->json([
                'preview' => [
                    'subject' => $processedSubject,
                    'content' => $processedContent,
                    'type' => $template->type,
                    'variables_used' => $template->variables ?? [],
                    'sample_variables' => $sampleVariables,
                ],
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to generate preview: '.$e->getMessage()], 500);
        }
    }

    /**
     * Clone a template.
     */
    public function clone(Request $request, MessageTemplate $template): JsonResponse
    {
        Gate::authorize('manage', $template->branch);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Check for duplicate names
        $existingTemplate = MessageTemplate::where('branch_id', $template->branch_id)
            ->where('name', $request->input('name'))
            ->where('type', $template->type)
            ->first();

        if ($existingTemplate) {
            return response()->json(['errors' => ['name' => ['Template name already exists for this type']]], 422);
        }

        try {
            $clonedTemplate = MessageTemplate::create([
                'branch_id' => $template->branch_id,
                'name' => $request->input('name'),
                'type' => $template->type,
                'subject' => $template->subject,
                'content' => $template->content,
                'variables' => $template->variables,
                'is_active' => false, // Start as inactive
            ]);

            Log::info('Message template cloned', [
                'original_template_id' => $template->id,
                'cloned_template_id' => $clonedTemplate->id,
                'branch_id' => $template->branch_id,
                'user_id' => $request->user()->id,
            ]);

            return response()->json([
                'message' => 'Template cloned successfully',
                'template' => $clonedTemplate,
            ], 201);

        } catch (\Exception $e) {
            Log::error('Failed to clone message template', [
                'template_id' => $template->id,
                'user_id' => $request->user()->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json(['error' => 'Failed to clone template'], 500);
        }
    }

    /**
     * Get available template variables.
     */
    public function getAvailableVariables(): JsonResponse
    {
        return response()->json([
            'variables' => [
                'member' => [
                    'member_name' => 'Member\'s full name',
                    'member_email' => 'Member\'s email address',
                    'user_name' => 'User\'s full name (alias for member_name)',
                    'user_email' => 'User\'s email address (alias for member_email)',
                    'user_phone' => 'User\'s phone number',
                ],
                'branch' => [
                    'branch_name' => 'Branch name',
                    'branch_email' => 'Branch email address',
                    'branch_phone' => 'Branch phone number',
                    'branch_venue' => 'Branch venue/location',
                ],
                'general' => [
                    'app_name' => 'Application name',
                    'current_date' => 'Current date (formatted)',
                    'current_year' => 'Current year',
                ],
                'custom' => [
                    'recipient_name' => 'Custom recipient name',
                    'recipient_email' => 'Custom recipient email',
                    'event_name' => 'Event name (for event-related templates)',
                    'event_date' => 'Event date (for event-related templates)',
                    'campaign_name' => 'Campaign name (for campaign emails)',
                    'step_number' => 'Campaign step number',
                ],
            ],
        ]);
    }
}
