<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\EmailCampaignService;
use Illuminate\Console\Command;

final class ProcessEmailCampaignsAsync extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'campaigns:process-async {--dry-run : Show what would be processed without actually sending}';

    /**
     * The console command description.
     */
    protected $description = 'Process due email campaign steps asynchronously using queue jobs';

    /**
     * Execute the console command.
     */
    public function handle(EmailCampaignService $campaignService): int
    {
        $this->info('Processing email campaigns asynchronously...');

        if ($this->option('dry-run')) {
            $this->warn('DRY RUN MODE - No emails will be sent');

            // TODO: Implement dry run logic
            return self::SUCCESS;
        }

        try {
            $batch = $campaignService->processDueCampaignEmailsAsync();

            $this->info("Successfully dispatched batch job with ID: {$batch->id}");
            $this->info("Total jobs in batch: {$batch->totalJobs}");

            return self::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Failed to process campaigns: '.$e->getMessage());

            return self::FAILURE;
        }
    }
}



