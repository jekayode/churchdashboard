<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Department;
use App\Models\Event;
use App\Models\Member;
use App\Models\MessageTemplate;
use App\Models\Ministry;
use App\Models\SmallGroup;
use App\Services\CommunicationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

final class MassCommunicationController extends Controller
{
    public function __construct(
        private readonly CommunicationService $communicationService
    ) {}

    /**
     * Get available filters for mass communication.
     */
    public function getFilters(Request $request): JsonResponse
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
        Gate::authorize('view', $branch);

        // Get filter options
        $departments = Department::whereHas('ministry', function ($query) use ($branchId) {
            $query->where('branch_id', $branchId);
        })
            ->select('id', 'name')
            ->orderBy('name')
            ->get();

        $ministries = Ministry::where('branch_id', $branchId)
            ->select('id', 'name')
            ->orderBy('name')
            ->get();

        $smallGroups = SmallGroup::where('branch_id', $branchId)
            ->select('id', 'name')
            ->orderBy('name')
            ->get();

        $events = Event::where('branch_id', $branchId)
            ->where('start_date', '>=', now()->subMonths(6)) // Events from last 6 months
            ->select('id', 'name', 'start_date')
            ->orderBy('start_date', 'desc')
            ->get();

        return response()->json([
            'filters' => [
                'member_status' => [
                    'all' => 'All Members',
                    'member' => 'Members Only',
                    'volunteer' => 'Volunteers',
                    'leader' => 'Leaders',
                    'minister' => 'Ministers',
                    'visitor' => 'Visitors',
                ],
                'departments' => $departments->mapWithKeys(fn ($dept) => [$dept->id => $dept->name]),
                'ministries' => $ministries->mapWithKeys(fn ($ministry) => [$ministry->id => $ministry->name]),
                'small_groups' => $smallGroups->mapWithKeys(fn ($group) => [$group->id => $group->name]),
                'events' => $events->mapWithKeys(fn ($event) => [$event->id => $event->name.' ('.$event->start_date->format('M j, Y').')']),
                'gender' => [
                    'all' => 'All Genders',
                    'male' => 'Male',
                    'female' => 'Female',
                ],
                'age_groups' => [
                    'all' => 'All Ages',
                    'children' => 'Children (0-12)',
                    'youth' => 'Youth (13-18)',
                    'young_adults' => 'Young Adults (19-35)',
                    'adults' => 'Adults (36-64)',
                    'seniors' => 'Seniors (65+)',
                ],
                'teci_status' => [
                    'all' => 'All TECI Levels',
                    'not_started' => 'Not Started',
                    '200_level' => '200 Level',
                    '300_level' => '300 Level',
                    'graduated' => 'Graduated',
                ],
                'marital_status' => [
                    'all' => 'All Marital Status',
                    'single' => 'Single',
                    'married' => 'Married',
                    'in_a_relationship' => 'In a Relationship',
                    'separated' => 'Separated',
                ],
                'growth_level' => [
                    'all' => 'All Growth Levels',
                    'seeker' => 'Seeker',
                    'new_believer' => 'New Believer',
                    'growing' => 'Growing',
                    'mature' => 'Mature',
                    'leader' => 'Leader',
                ],
            ],
            'branch' => [
                'id' => $branch->id,
                'name' => $branch->name,
            ],
        ]);
    }

    /**
     * Get recipients based on filters.
     */
    public function getRecipients(Request $request): JsonResponse
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
        // Gate::authorize('view', $branch); // Temporarily disabled for debugging

        // Validation
        $validator = Validator::make($request->all(), [
            'filters' => 'required|array',
            'filters.member_status' => 'nullable|string',
            'filters.departments' => 'nullable|array',
            'filters.ministries' => 'nullable|array',
            'filters.small_groups' => 'nullable|array',
            'filters.events' => 'nullable|array',
            'filters.gender' => 'nullable|string',
            'filters.age_groups' => 'nullable|array',
            'message_type' => 'required|in:email,sms',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $filters = $request->input('filters');
        $messageType = $request->input('message_type');

        try {
            $limit = min(max((int) $request->input('limit', 1000), 1), 10000);
            $recipients = array_slice(
                $this->buildRecipientsQuery($branchId, $filters, $messageType),
                0,
                $limit
            );

            return response()->json([
                'recipients' => $recipients,
                'total' => count($recipients),
                'filters_applied' => $filters,
                'limit' => $limit,
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to get mass communication recipients', [
                'branch_id' => $branchId,
                'filters' => $filters,
                'error' => $e->getMessage(),
            ]);

            return response()->json(['error' => 'Failed to load recipients'], 500);
        }
    }

    /**
     * Send mass communication.
     */
    public function send(Request $request): JsonResponse
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
            'type' => 'required|in:email,sms',
            'filters' => 'required|array',
            'template_id' => 'nullable|exists:message_templates,id',
            'subject' => 'nullable|string|max:255|required_if:type,email',
            'content' => 'required|string',
            'custom_variables' => 'nullable|array',
            'send_immediately' => 'boolean',
            'schedule_date' => 'nullable|date|after:now',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Get template if specified
        $template = null;
        if ($request->filled('template_id')) {
            $template = MessageTemplate::where('id', $request->integer('template_id'))
                ->where('branch_id', $branch->id)
                ->where('type', $request->input('type'))
                ->first();

            if (! $template) {
                return response()->json(['error' => 'Template not found or does not belong to this branch'], 422);
            }
        }

        try {
            $recipients = $this->buildRecipientsQuery(
                $branchId,
                $request->input('filters'),
                $request->input('type')
            );

            if (empty($recipients)) {
                return response()->json(['error' => 'No recipients found with the specified filters'], 422);
            }

            // Check if scheduled send
            if ($request->boolean('schedule_date') && $request->filled('schedule_date')) {
                // TODO: Implement scheduled sending (queue job)
                return response()->json(['error' => 'Scheduled sending not yet implemented'], 422);
            }

            $results = [];
            $successCount = 0;
            $failureCount = 0;

            foreach ($recipients as $recipient) {
                try {
                    // Prepare variables
                    $variables = array_merge(
                        $request->input('custom_variables', []),
                        [
                            'recipient_name' => $recipient['name'],
                            'recipient_email' => $recipient['email'] ?? '',
                            'recipient_phone' => $recipient['phone'] ?? '',
                        ]
                    );

                    // Send message
                    if ($request->input('type') === 'email') {
                        $log = $this->communicationService->sendEmail(
                            $branch,
                            $recipient['email'],
                            $request->input('subject', ''),
                            $request->input('content'),
                            $template,
                            $user,
                            $variables
                        );
                    } else {
                        $log = $this->communicationService->sendSms(
                            $branch,
                            $recipient['phone'],
                            $request->input('content'),
                            $template,
                            $user,
                            $variables
                        );
                    }

                    $results[] = [
                        'recipient' => $recipient['name'],
                        'contact' => $recipient[$request->input('type') === 'email' ? 'email' : 'phone'],
                        'status' => 'success',
                        'log_id' => $log->id,
                    ];
                    $successCount++;

                } catch (\Exception $e) {
                    $results[] = [
                        'recipient' => $recipient['name'],
                        'status' => 'failed',
                        'error' => $e->getMessage(),
                    ];
                    $failureCount++;

                    Log::error('Mass communication send failed', [
                        'user_id' => $user->id,
                        'branch_id' => $branch->id,
                        'recipient' => $recipient,
                        'type' => $request->input('type'),
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            Log::info('Mass communication completed', [
                'user_id' => $user->id,
                'branch_id' => $branch->id,
                'type' => $request->input('type'),
                'total_recipients' => count($recipients),
                'success_count' => $successCount,
                'failure_count' => $failureCount,
                'filters' => $request->input('filters'),
            ]);

            return response()->json([
                'message' => "Mass communication sent: {$successCount} successful, {$failureCount} failed",
                'summary' => [
                    'total' => count($recipients),
                    'success' => $successCount,
                    'failed' => $failureCount,
                ],
                'results' => $results,
            ]);

        } catch (\Exception $e) {
            Log::error('Mass communication failed', [
                'user_id' => $user->id,
                'branch_id' => $branch->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json(['error' => 'Failed to send mass communication'], 500);
        }
    }

    /**
     * Build recipients query based on filters.
     */
    private function buildRecipientsQuery(int $branchId, array $filters, string $messageType): array
    {
        // Start with base query for all members in the branch
        $query = Member::where('branch_id', $branchId);

        // Filter by member status
        if (! empty($filters['member_status']) && $filters['member_status'] !== 'all') {
            $query->where('member_status', $filters['member_status']);
        }

        // Filter by gender
        if (! empty($filters['gender']) && $filters['gender'] !== 'all') {
            $query->where('gender', $filters['gender']);
        }

        // Filter by age groups (using date_of_birth)
        if (! empty($filters['age_groups']) && ! in_array('all', $filters['age_groups'])) {
            $query->where(function ($q) use ($filters) {
                foreach ($filters['age_groups'] as $ageGroup) {
                    switch ($ageGroup) {
                        case 'children':
                            $q->orWhere('date_of_birth', '>=', now()->subYears(12));
                            break;
                        case 'youth':
                            $q->orWhere(function ($subQ) {
                                $subQ->where('date_of_birth', '<=', now()->subYears(13))
                                    ->where('date_of_birth', '>=', now()->subYears(18));
                            });
                            break;
                        case 'young_adults':
                            $q->orWhere(function ($subQ) {
                                $subQ->where('date_of_birth', '<=', now()->subYears(19))
                                    ->where('date_of_birth', '>=', now()->subYears(35));
                            });
                            break;
                        case 'adults':
                            $q->orWhere(function ($subQ) {
                                $subQ->where('date_of_birth', '<=', now()->subYears(36))
                                    ->where('date_of_birth', '>=', now()->subYears(64));
                            });
                            break;
                        case 'seniors':
                            $q->orWhere('date_of_birth', '<=', now()->subYears(65));
                            break;
                    }
                }
            });
        }

        // Filter by TECI status
        if (! empty($filters['teci_status']) && $filters['teci_status'] !== 'all') {
            $query->where('teci_status', $filters['teci_status']);
        }

        // Filter by marital status
        if (! empty($filters['marital_status']) && $filters['marital_status'] !== 'all') {
            $query->where('marital_status', $filters['marital_status']);
        }

        // Filter by growth level
        if (! empty($filters['growth_level']) && $filters['growth_level'] !== 'all') {
            $query->where('growth_level', $filters['growth_level']);
        }

        // Filter by membership date range
        if (! empty($filters['membership_date_from'])) {
            $query->where('date_joined', '>=', $filters['membership_date_from']);
        }
        if (! empty($filters['membership_date_to'])) {
            $query->where('date_joined', '<=', $filters['membership_date_to']);
        }

        // Filter by anniversary date range (for married members)
        if (! empty($filters['anniversary_month'])) {
            $query->whereMonth('anniversary', $filters['anniversary_month']);
        }

        // Filter by birthday month
        if (! empty($filters['birthday_month'])) {
            $query->whereMonth('date_of_birth', $filters['birthday_month']);
        }

        // Filter by departments
        if (! empty($filters['departments'])) {
            $query->whereHas('departments', function ($q) use ($filters) {
                $q->whereIn('departments.id', $filters['departments']);
            });
        }

        // Filter by ministries (through their departments since there's no direct member-ministry relationship)
        if (! empty($filters['ministries'])) {
            $query->whereHas('departments.ministry', function ($q) use ($filters) {
                $q->whereIn('ministries.id', $filters['ministries']);
            });
        }

        // Filter by small groups
        if (! empty($filters['small_groups'])) {
            $query->whereHas('smallGroups', function ($q) use ($filters) {
                $q->whereIn('small_groups.id', $filters['small_groups']);
            });
        }

        // Filter by event attendance
        if (! empty($filters['events'])) {
            $query->whereHas('eventRegistrations', function ($q) use ($filters) {
                $q->whereIn('event_id', $filters['events'])
                    ->where('status', 'registered');
            });
        }

        // Ensure they have the required contact method
        if ($messageType === 'email') {
            $query->whereNotNull('email')->where('email', '!=', '');
        } else {
            $query->whereNotNull('phone')->where('phone', '!=', '');
        }

        // Get the results
        $members = $query->select('id', 'name', 'email', 'phone', 'member_status')
            ->distinct()
            ->get();

        return $members->map(function ($member) {
            return [
                'id' => $member->id,
                'name' => $member->name,
                'email' => $member->email,
                'phone' => $member->phone,
                'type' => 'member',
                'status' => $member->member_status,
            ];
        })->toArray();
    }

    /**
     * Preview mass communication.
     */
    public function preview(Request $request): JsonResponse
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
        Gate::authorize('view', $branch);

        // Validation
        $validator = Validator::make($request->all(), [
            'type' => 'required|in:email,sms',
            'subject' => 'nullable|string',
            'content' => 'required|string',
            'custom_variables' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            // Sample variables for preview
            $sampleVariables = array_merge(
                [
                    'recipient_name' => 'John Doe',
                    'recipient_email' => 'john@example.com',
                    'recipient_phone' => '+1234567890',
                ],
                $request->input('custom_variables', [])
            );

            // Process subject if provided
            $processedSubject = null;
            if ($request->filled('subject')) {
                $processedSubject = $this->communicationService->processTemplateVariables(
                    $request->input('subject'),
                    $sampleVariables,
                    $branch,
                    $user
                );
            }

            // Process content
            $processedContent = $this->communicationService->processTemplateVariables(
                $request->input('content'),
                $sampleVariables,
                $branch,
                $user
            );

            return response()->json([
                'preview' => [
                    'type' => $request->input('type'),
                    'subject' => $processedSubject,
                    'content' => $processedContent,
                    'variables_used' => $sampleVariables,
                ],
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to generate preview: '.$e->getMessage()], 500);
        }
    }
}
