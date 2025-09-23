<?php

declare(strict_types=1);

namespace App\Services;

use App\Jobs\ProcessCampaignStepJob;
use App\Models\EmailCampaign;
use App\Models\EmailCampaignEnrollment;
use App\Models\User;
use Illuminate\Bus\Batch;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Log;

final class EmailCampaignService
{
    public function __construct(
        private readonly CommunicationService $communicationService
    ) {}

    /**
     * Enroll a user in campaigns based on trigger event.
     */
    public function enrollUserInCampaigns(User $user, string $triggerEvent): array
    {
        $branch = $user->getPrimaryBranch();
        if (! $branch) {
            return [];
        }

        $campaigns = EmailCampaign::active()
            ->forBranch($branch->id)
            ->byTrigger($triggerEvent)
            ->get();

        $enrollments = [];

        foreach ($campaigns as $campaign) {
            try {
                // Check if user is already enrolled
                if (! $user->isEnrolledInCampaign($campaign)) {
                    $enrollment = $user->enrollInCampaign($campaign);
                    $enrollments[] = $enrollment;

                    Log::info('User enrolled in email campaign', [
                        'user_id' => $user->id,
                        'campaign_id' => $campaign->id,
                        'trigger_event' => $triggerEvent,
                    ]);
                }
            } catch (\Exception $e) {
                Log::error('Failed to enroll user in campaign', [
                    'user_id' => $user->id,
                    'campaign_id' => $campaign->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $enrollments;
    }

    /**
     * Process due campaign emails (synchronous - for small batches).
     */
    public function processDueCampaignEmails(): int
    {
        $dueEnrollments = EmailCampaignEnrollment::dueForSending()
            ->with(['user', 'campaign.branch', 'campaign.steps.template'])
            ->get();

        $processed = 0;

        foreach ($dueEnrollments as $enrollment) {
            try {
                $this->processCampaignStep($enrollment);
                $processed++;
            } catch (\Exception $e) {
                Log::error('Failed to process campaign step', [
                    'enrollment_id' => $enrollment->id,
                    'user_id' => $enrollment->user_id,
                    'campaign_id' => $enrollment->campaign_id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $processed;
    }

    /**
     * Process due campaign emails asynchronously using batch jobs.
     */
    public function processDueCampaignEmailsAsync(): Batch
    {
        $dueEnrollments = EmailCampaignEnrollment::dueForSending()
            ->with(['user', 'campaign.branch', 'campaign.steps.template'])
            ->get();

        $jobs = $dueEnrollments->map(function ($enrollment) {
            return new ProcessCampaignStepJob($enrollment);
        });

        return Bus::batch($jobs)
            ->name('Process Email Campaigns')
            ->then(function (Batch $batch) {
                Log::info('Campaign batch completed', [
                    'total_jobs' => $batch->totalJobs,
                    'processed_jobs' => $batch->processedJobs(),
                ]);
            })
            ->catch(function (Batch $batch, \Throwable $e) {
                Log::error('Campaign batch failed', [
                    'error' => $e->getMessage(),
                    'failed_jobs' => $batch->failedJobs,
                ]);
            })
            ->dispatch();
    }

    /**
     * Process a single campaign step.
     */
    private function processCampaignStep(EmailCampaignEnrollment $enrollment): void
    {
        $currentStep = $enrollment->getCurrentStepModel();

        if (! $currentStep || ! $currentStep->template) {
            throw new \Exception('Campaign step or template not found');
        }

        $template = $currentStep->template;
        $user = $enrollment->user;
        $branch = $enrollment->campaign->branch;

        // Prepare template variables
        $variables = [
            'campaign_name' => $enrollment->campaign->name,
            'step_number' => $currentStep->step_order,
            'user_name' => $user->name,
            'member_name' => $user->name,
        ];

        // Send the email
        $this->communicationService->sendEmail(
            $branch,
            $user->email,
            $template->subject ?? 'Message from '.$branch->name,
            $template->content,
            $template,
            $user,
            $variables
        );

        // Advance to next step or complete
        $enrollment->advanceToNextStep();

        Log::info('Campaign step processed', [
            'enrollment_id' => $enrollment->id,
            'user_id' => $user->id,
            'campaign_id' => $enrollment->campaign_id,
            'step_order' => $currentStep->step_order,
        ]);
    }

    /**
     * Manually trigger a campaign for a user.
     */
    public function triggerCampaignForUser(EmailCampaign $campaign, User $user): EmailCampaignEnrollment
    {
        // Check if user is already enrolled
        if ($user->isEnrolledInCampaign($campaign)) {
            throw new \Exception('User is already enrolled in this campaign');
        }

        $enrollment = $user->enrollInCampaign($campaign);

        Log::info('Campaign manually triggered for user', [
            'user_id' => $user->id,
            'campaign_id' => $campaign->id,
            'enrollment_id' => $enrollment->id,
        ]);

        return $enrollment;
    }

    /**
     * Stop a user's enrollment in a campaign.
     */
    public function stopCampaignForUser(EmailCampaign $campaign, User $user): bool
    {
        $enrollment = $user->emailCampaignEnrollments()
            ->where('campaign_id', $campaign->id)
            ->whereNull('completed_at')
            ->first();

        if (! $enrollment) {
            return false;
        }

        $enrollment->markAsCompleted();

        Log::info('Campaign stopped for user', [
            'user_id' => $user->id,
            'campaign_id' => $campaign->id,
            'enrollment_id' => $enrollment->id,
        ]);

        return true;
    }

    /**
     * Get campaign statistics.
     */
    public function getCampaignStatistics(EmailCampaign $campaign): array
    {
        $enrollments = $campaign->enrollments();

        return [
            'total_enrollments' => $enrollments->count(),
            'active_enrollments' => $enrollments->whereNull('completed_at')->count(),
            'completed_enrollments' => $enrollments->whereNotNull('completed_at')->count(),
            'completion_rate' => $this->calculateCompletionRate($campaign),
            'average_completion_time' => $this->calculateAverageCompletionTime($campaign),
        ];
    }

    /**
     * Calculate completion rate for a campaign.
     */
    private function calculateCompletionRate(EmailCampaign $campaign): float
    {
        $total = $campaign->enrollments()->count();
        $completed = $campaign->enrollments()->whereNotNull('completed_at')->count();

        return $total > 0 ? round(($completed / $total) * 100, 2) : 0.0;
    }

    /**
     * Calculate average completion time for a campaign.
     */
    private function calculateAverageCompletionTime(EmailCampaign $campaign): ?float
    {
        $completedEnrollments = $campaign->enrollments()
            ->whereNotNull('completed_at')
            ->get();

        if ($completedEnrollments->isEmpty()) {
            return null;
        }

        $totalDays = 0;
        foreach ($completedEnrollments as $enrollment) {
            $totalDays += $enrollment->created_at->diffInDays($enrollment->completed_at);
        }

        return round($totalDays / $completedEnrollments->count(), 1);
    }

    /**
     * Preview campaign content for a user.
     */
    public function previewCampaignStep(EmailCampaign $campaign, int $stepOrder, User $user): array
    {
        $step = $campaign->steps()->where('step_order', $stepOrder)->first();

        if (! $step || ! $step->template) {
            throw new \Exception('Campaign step or template not found');
        }

        $template = $step->template;
        $branch = $campaign->branch;

        // Prepare template variables
        $variables = [
            'campaign_name' => $campaign->name,
            'step_number' => $step->step_order,
            'user_name' => $user->name,
            'member_name' => $user->name,
        ];

        // Process template content
        $processedSubject = $this->communicationService->processTemplateVariables(
            $template->subject ?? '',
            $variables,
            $branch,
            $user
        );

        $processedContent = $this->communicationService->processTemplateVariables(
            $template->content,
            $variables,
            $branch,
            $user
        );

        return [
            'step_order' => $step->step_order,
            'delay_days' => $step->delay_days,
            'template_name' => $template->name,
            'subject' => $processedSubject,
            'content' => $processedContent,
            'variables_used' => $variables,
        ];
    }

    /**
     * Clone a campaign.
     */
    public function cloneCampaign(EmailCampaign $campaign, string $newName): EmailCampaign
    {
        $newCampaign = EmailCampaign::create([
            'branch_id' => $campaign->branch_id,
            'name' => $newName,
            'trigger_event' => $campaign->trigger_event,
            'is_active' => false, // Start as inactive
        ]);

        // Clone steps
        foreach ($campaign->steps as $step) {
            $newCampaign->steps()->create([
                'step_order' => $step->step_order,
                'delay_days' => $step->delay_days,
                'template_id' => $step->template_id,
            ]);
        }

        return $newCampaign;
    }
}
