<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\Branch;
use App\Models\User;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

final class SendBulkWelcomeEmailsJob implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 2;

    public int $timeout = 300; // 5 minutes for bulk operations

    public int $backoff = 60;

    public function __construct(
        public Branch $branch,
        public array $userData, // [['user_id' => 1, 'temporary_password' => 'Church1234'], ...]
        public int $batchSize = 5, // Process 5 emails at a time
        public int $delayBetweenBatches = 30 // 30 seconds between batches
    ) {}

    public function handle(): void
    {
        if ($this->batch()?->cancelled()) {
            return;
        }

        $processed = 0;
        $failed = 0;

        // Process in smaller batches to prevent email provider flagging
        $chunks = array_chunk($this->userData, $this->batchSize);

        foreach ($chunks as $chunkIndex => $chunk) {
            Log::info('Processing welcome email batch', [
                'batch_index' => $chunkIndex + 1,
                'total_batches' => count($chunks),
                'chunk_size' => count($chunk),
                'branch_id' => $this->branch->id,
            ]);

            foreach ($chunk as $userData) {
                try {
                    $user = User::find($userData['user_id']);

                    if (! $user) {
                        Log::warning('User not found for welcome email', [
                            'user_id' => $userData['user_id'],
                        ]);
                        $failed++;

                        continue;
                    }

                    // Dispatch individual welcome email job with delay
                    SendWelcomeEmailJob::dispatch(
                        $user,
                        $userData['temporary_password'],
                        $this->branch
                    )->delay(now()->addSeconds($chunkIndex * $this->delayBetweenBatches));

                    $processed++;

                } catch (\Exception $e) {
                    $failed++;
                    Log::error('Failed to dispatch welcome email job', [
                        'user_id' => $userData['user_id'] ?? 'unknown',
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            // Add delay between chunks to prevent overwhelming email providers
            if ($chunkIndex < count($chunks) - 1) {
                Log::info('Waiting before next batch to prevent email flagging', [
                    'delay_seconds' => $this->delayBetweenBatches,
                ]);

                // Use sleep for the delay between batches
                sleep($this->delayBetweenBatches);
            }
        }

        Log::info('Bulk welcome email job completed', [
            'branch_id' => $this->branch->id,
            'total_users' => count($this->userData),
            'processed' => $processed,
            'failed' => $failed,
            'batches_processed' => count($chunks),
        ]);
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('Bulk welcome email job failed permanently', [
            'branch_id' => $this->branch->id,
            'users_count' => count($this->userData),
            'error' => $exception->getMessage(),
        ]);
    }
}



