<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\EmailCampaignEnrollment;
use App\Services\EmailCampaignService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

final class ProcessCampaignStepJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $timeout = 120;

    public int $backoff = 30;

    public function __construct(
        public EmailCampaignEnrollment $enrollment
    ) {}

    public function handle(EmailCampaignService $campaignService): void
    {
        try {
            // Use reflection to call the private method
            $reflection = new \ReflectionClass($campaignService);
            $method = $reflection->getMethod('processCampaignStep');
            $method->setAccessible(true);
            $method->invoke($campaignService, $this->enrollment);

            Log::info('Campaign step processed successfully via queue', [
                'enrollment_id' => $this->enrollment->id,
                'user_id' => $this->enrollment->user_id,
                'campaign_id' => $this->enrollment->campaign_id,
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to process campaign step via queue', [
                'enrollment_id' => $this->enrollment->id,
                'user_id' => $this->enrollment->user_id,
                'campaign_id' => $this->enrollment->campaign_id,
                'error' => $e->getMessage(),
                'attempt' => $this->attempts(),
            ]);

            throw $e;
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('Campaign step job failed permanently', [
            'enrollment_id' => $this->enrollment->id,
            'user_id' => $this->enrollment->user_id,
            'campaign_id' => $this->enrollment->campaign_id,
            'error' => $exception->getMessage(),
            'attempts' => $this->attempts(),
        ]);
    }
}

