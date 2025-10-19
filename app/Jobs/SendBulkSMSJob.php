<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\Branch;
use App\Models\MessageTemplate;
use App\Models\User;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

final class SendBulkSMSJob implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 2;

    public int $timeout = 300; // 5 minutes for bulk operations

    public int $backoff = 60;

    public function __construct(
        public Branch $branch,
        public array $recipients, // ['phone' => '+1234567890', 'name' => 'John Doe']
        public string $content,
        public ?int $templateId = null,
        public ?int $senderId = null
    ) {}

    public function handle(): void
    {
        if ($this->batch()?->cancelled()) {
            return;
        }

        $template = $this->templateId ? MessageTemplate::find($this->templateId) : null;
        $sender = $this->senderId ? User::find($this->senderId) : null;

        $processed = 0;
        $failed = 0;

        // Process in chunks to avoid memory issues
        $chunks = array_chunk($this->recipients, 10); // Process 10 at a time

        foreach ($chunks as $chunk) {
            foreach ($chunk as $recipient) {
                try {
                    $variables = [
                        'recipient_name' => $recipient['name'] ?? 'Member',
                        'recipient_email' => $recipient['email'] ?? '',
                        'recipient_phone' => $recipient['phone'] ?? '',
                    ];

                    // Dispatch individual SMS job
                    SendSingleSMSJob::dispatch(
                        $this->branch,
                        $recipient['phone'],
                        $this->content,
                        $this->templateId,
                        $this->senderId,
                        $variables
                    );

                    $processed++;

                } catch (\Exception $e) {
                    $failed++;
                    Log::error('Failed to dispatch SMS job', [
                        'recipient' => $recipient,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            // Small delay between chunks to prevent overwhelming the queue
            usleep(100000); // 0.1 second
        }

        Log::info('Bulk SMS job completed', [
            'branch_id' => $this->branch->id,
            'total_recipients' => count($this->recipients),
            'processed' => $processed,
            'failed' => $failed,
        ]);
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('Bulk SMS job failed permanently', [
            'branch_id' => $this->branch->id,
            'recipients_count' => count($this->recipients),
            'error' => $exception->getMessage(),
        ]);
    }
}

