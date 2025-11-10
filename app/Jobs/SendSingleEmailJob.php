<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\Branch;
use App\Models\MessageTemplate;
use App\Models\User;
use App\Services\CommunicationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

final class SendSingleEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $timeout = 60;

    public int $backoff = 30;

    public function __construct(
        public Branch $branch,
        public string $recipient,
        public string $subject,
        public string $content,
        public ?int $templateId = null,
        public ?int $userId = null,
        public array $variables = []
    ) {}

    public function handle(CommunicationService $communicationService): void
    {
        try {
            $template = $this->templateId ? MessageTemplate::find($this->templateId) : null;
            $user = $this->userId ? User::find($this->userId) : null;

            $communicationService->sendEmail(
                $this->branch,
                $this->recipient,
                $this->subject,
                $this->content,
                $template,
                $user,
                $this->variables
            );

            Log::info('Email sent successfully via queue', [
                'recipient' => $this->recipient,
                'branch_id' => $this->branch->id,
                'template_id' => $this->templateId,
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to send email via queue', [
                'recipient' => $this->recipient,
                'branch_id' => $this->branch->id,
                'error' => $e->getMessage(),
                'attempt' => $this->attempts(),
            ]);

            throw $e;
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('Email job failed permanently', [
            'recipient' => $this->recipient,
            'branch_id' => $this->branch->id,
            'error' => $exception->getMessage(),
            'attempts' => $this->attempts(),
        ]);
    }
}



