<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Member;
use App\Models\MessageTemplate;
use App\Models\User;
use App\Services\CommunicationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

final class QuickSendController extends Controller
{
    public function __construct(
        private readonly CommunicationService $communicationService
    ) {}

    /**
     * Send a quick message to individual recipient(s).
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
            'type' => 'required|in:email,sms,whatsapp',
            'recipients' => 'required|array|min:1',
            'recipients.*.id' => 'required|exists:users,id',
            'recipients.*.type' => 'required|in:user,member',
            'template_id' => 'nullable|exists:message_templates,id',
            'subject' => 'nullable|string|max:255|required_if:type,email',
            'content' => 'required|string',
            'custom_variables' => 'nullable|array',
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

        $results = [];
        $successCount = 0;
        $failureCount = 0;

        foreach ($request->input('recipients') as $recipientData) {
            try {
                // Get recipient details
                if ($recipientData['type'] === 'user') {
                    $recipient = User::findOrFail($recipientData['id']);
                    $recipientName = $recipient->name;

                    // For SMS/WhatsApp, try to get phone from associated member record first
                    if (in_array($request->input('type'), ['sms', 'whatsapp'])) {
                        $member = $recipient->member()->where('branch_id', $branch->id)->first();
                        $recipientContact = $member?->phone ?? $recipient->phone;
                    } else {
                        $recipientContact = $recipient->email;
                    }
                } else {
                    $member = Member::findOrFail($recipientData['id']);
                    $recipientContact = $request->input('type') === 'email' ? $member->email : $member->phone;
                    $recipientName = $member->name;
                    $recipient = $member->user; // Member might have associated user
                }

                if (! $recipientContact) {
                    $errorMessage = match ($request->input('type')) {
                        'sms' => "This recipient doesn't have a sms address.",
                        'whatsapp' => "This recipient doesn't have a whatsapp address.",
                        default => "This recipient doesn't have an email address.",
                    };

                    $results[] = [
                        'recipient' => $recipientName,
                        'status' => 'failed',
                        'error' => $errorMessage,
                    ];
                    $failureCount++;

                    continue;
                }

                // Prepare variables
                $variables = array_merge(
                    $request->input('custom_variables', []),
                    [
                        'recipient_name' => $recipientName,
                        'recipient_email' => $request->input('type') === 'email' ? $recipientContact : '',
                        'recipient_phone' => in_array($request->input('type'), ['sms', 'whatsapp']) ? $recipientContact : '',
                    ]
                );

                // Send message
                if ($request->input('type') === 'email') {
                    $log = $this->communicationService->sendEmail(
                        $branch,
                        $recipientContact,
                        $request->input('subject', ''),
                        $request->input('content'),
                        $template,
                        $user,
                        $variables
                    );
                } elseif ($request->input('type') === 'sms') {
                    $log = $this->communicationService->sendSMS(
                        $branch,
                        $recipientContact,
                        $request->input('content'),
                        $template,
                        $user,
                        $variables
                    );
                } else { // whatsapp
                    $log = $this->communicationService->sendWhatsApp(
                        $branch,
                        $recipientContact,
                        $request->input('content'),
                        $template,
                        $user,
                        $variables
                    );
                }

                $results[] = [
                    'recipient' => $recipientName,
                    'contact' => $recipientContact,
                    'status' => 'success',
                    'log_id' => $log->id,
                ];
                $successCount++;

            } catch (\Exception $e) {
                $results[] = [
                    'recipient' => $recipientName ?? 'Unknown',
                    'status' => 'failed',
                    'error' => $e->getMessage(),
                ];
                $failureCount++;

                Log::error('Quick send failed', [
                    'user_id' => $user->id,
                    'branch_id' => $branch->id,
                    'recipient_id' => $recipientData['id'],
                    'type' => $request->input('type'),
                    'error' => $e->getMessage(),
                ]);
            }
        }

        Log::info('Quick send completed', [
            'user_id' => $user->id,
            'branch_id' => $branch->id,
            'type' => $request->input('type'),
            'total_recipients' => count($request->input('recipients')),
            'success_count' => $successCount,
            'failure_count' => $failureCount,
        ]);

        return response()->json([
            'success' => true,
            'message' => "Messages sent: {$successCount} successful, {$failureCount} failed",
            'data' => [
                'summary' => [
                    'total' => count($request->input('recipients')),
                    'successful' => $successCount,
                    'failed' => $failureCount,
                ],
                'results' => $results,
            ],
        ]);
    }

    /**
     * Get available recipients for quick send.
     */
    public function getRecipients(Request $request): JsonResponse
    {
        $user = $request->user();
        $branchId = $request->integer('branch_id');
        $search = $request->input('search', $request->input('query', ''));
        $type = $request->input('type', $request->input('recipient_type', 'all')); // 'user', 'member', or 'all'

        // Authorization
        if (! $user->isSuperAdmin() && (! $branchId || $user->getActiveBranchId() !== $branchId)) {
            $branchId = $user->getActiveBranchId();
        }

        if (! $branchId) {
            return response()->json(['error' => 'Branch not specified'], 400);
        }

        $branch = Branch::findOrFail($branchId);
        Gate::authorize('view', $branch);

        $recipients = [];

        $limit = min(max((int) $request->input('limit', 500), 1), 5000);

        // Get users if requested
        if (in_array($type, ['user', 'all'])) {
            $usersQuery = User::whereHas('roles', function ($query) use ($branchId) {
                $query->where('user_roles.branch_id', $branchId);
            });

            if ($search) {
                $usersQuery->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            }

            $users = $usersQuery->with(['member' => function ($query) use ($branchId) {
                $query->where('branch_id', $branchId);
            }])->limit($limit)->get(['id', 'name', 'email', 'phone']);

            foreach ($users as $user) {
                // Get phone from associated member record if available
                $phone = $user->member?->phone ?? $user->phone;

                $recipients[] = [
                    'id' => $user->id,
                    'type' => 'user',
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $phone,
                    'label' => $user->name.' ('.$user->email.')',
                ];
            }
        }

        // Get members if requested
        if (in_array($type, ['member', 'all'])) {
            $membersQuery = Member::where('branch_id', $branchId);

            if ($search) {
                $membersQuery->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                });
            }

            $members = $membersQuery->limit($limit)->get(['id', 'name', 'email', 'phone']);

            foreach ($members as $member) {
                $recipients[] = [
                    'id' => $member->id,
                    'type' => 'member',
                    'name' => $member->name,
                    'email' => $member->email,
                    'phone' => $member->phone,
                    'label' => $member->name.' ('.($member->email ?: $member->phone ?: 'No contact').')',
                ];
            }
        }

        return response()->json([
            'success' => true,
            'data' => [
                'recipients' => $recipients,
                'total' => count($recipients),
            ],
            'message' => 'Recipients retrieved successfully.',
        ]);
    }

    /**
     * Preview message with template variables processed.
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
