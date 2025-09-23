<?php

declare(strict_types=1);

namespace App\Services;

use App\Jobs\SendBulkEmailJob;
use App\Jobs\SendBulkSMSJob;
use App\Jobs\SendSingleEmailJob;
use App\Jobs\SendSingleSMSJob;
use App\Models\Branch;
use App\Models\CommunicationLog;
use App\Models\CommunicationSetting;
use App\Models\MessageTemplate;
use App\Models\User;
use Illuminate\Bus\Batch;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

final class CommunicationService
{
    /**
     * Send an email using the branch's configured provider.
     */
    public function sendEmail(
        Branch $branch,
        string $recipient,
        string $subject,
        string $content,
        ?MessageTemplate $template = null,
        ?User $user = null,
        array $variables = []
    ): CommunicationLog {
        $setting = $branch->communicationSetting;

        if (! $setting || ! $setting->is_active) {
            throw new \Exception('Communication not configured for this branch');
        }

        // Process template variables
        $processedContent = $this->processTemplateVariables($content, $variables, $branch, $user);
        $processedSubject = $this->processTemplateVariables($subject, $variables, $branch, $user);

        // Convert to HTML and wrap in email template
        $htmlContent = $this->convertToHtml($processedContent);
        $wrappedContent = $this->wrapInHtmlTemplate($htmlContent, $processedSubject, $branch, $user);

        // Create communication log
        $log = CommunicationLog::create([
            'user_id' => $user?->id,
            'branch_id' => $branch->id,
            'type' => 'email',
            'recipient' => $recipient,
            'subject' => $processedSubject,
            'content' => $processedContent,
            'template_id' => $template?->id,
            'status' => 'pending',
        ]);

        $startTime = microtime(true);

        try {
            $messageId = match ($setting->email_provider) {
                'smtp' => $this->sendEmailViaSMTP($setting, $recipient, $processedSubject, $wrappedContent),
                'resend' => $this->sendEmailViaResend($setting, $recipient, $processedSubject, $wrappedContent),
                'mailgun' => $this->sendEmailViaMailgun($setting, $recipient, $processedSubject, $wrappedContent),
                'ses' => $this->sendEmailViaSES($setting, $recipient, $processedSubject, $wrappedContent),
                'postmark' => $this->sendEmailViaPostmark($setting, $recipient, $processedSubject, $wrappedContent),
                default => throw new \Exception("Unsupported email provider: {$setting->email_provider}"),
            };

            $executionTime = round((microtime(true) - $startTime) * 1000, 2); // Convert to milliseconds

            $log->update([
                'status' => 'sent',
                'provider_message_id' => $messageId,
                'sent_at' => now(),
            ]);

            Log::info('Email sent successfully', [
                'branch_id' => $branch->id,
                'recipient' => $recipient,
                'provider' => $setting->email_provider,
                'execution_time_ms' => $executionTime,
                'message_id' => $messageId,
            ]);

        } catch (\Exception $e) {
            $executionTime = round((microtime(true) - $startTime) * 1000, 2);

            Log::error('Email sending failed', [
                'branch_id' => $branch->id,
                'recipient' => $recipient,
                'provider' => $setting->email_provider,
                'execution_time_ms' => $executionTime,
                'error' => $e->getMessage(),
            ]);

            $log->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
            ]);

            throw $e;
        }

        return $log;
    }

    /**
     * Send WhatsApp message using the branch's configured provider.
     */
    public function sendWhatsApp(
        Branch $branch,
        string $recipient,
        string $content,
        ?MessageTemplate $template = null,
        ?User $user = null,
        array $variables = []
    ): CommunicationLog {
        $setting = $branch->communicationSetting;

        if (! $setting || ! $setting->is_active || ! $setting->whatsapp_provider) {
            throw new \Exception('WhatsApp not configured for this branch');
        }

        // Process template variables
        $processedContent = $this->processTemplateVariables($content, $variables, $branch, $user);

        // Create communication log
        $log = CommunicationLog::create([
            'user_id' => $user?->id,
            'branch_id' => $branch->id,
            'type' => 'whatsapp',
            'recipient' => $recipient,
            'content' => $processedContent,
            'template_id' => $template?->id,
            'status' => 'pending',
        ]);

        $startTime = microtime(true);

        try {
            $messageId = match ($setting->whatsapp_provider) {
                'twilio' => $this->sendWhatsAppViaTwilio($setting, $recipient, $processedContent),
                'meta' => $this->sendWhatsAppViaMeta($setting, $recipient, $processedContent),
                default => throw new \Exception("Unsupported WhatsApp provider: {$setting->whatsapp_provider}"),
            };

            $executionTime = round((microtime(true) - $startTime) * 1000, 2); // Convert to milliseconds

            $log->update([
                'status' => 'sent',
                'provider_message_id' => $messageId,
                'sent_at' => now(),
            ]);

            Log::info('WhatsApp sent successfully', [
                'branch_id' => $branch->id,
                'recipient' => $recipient,
                'provider' => $setting->whatsapp_provider,
                'execution_time_ms' => $executionTime,
                'message_id' => $messageId,
            ]);

        } catch (\Exception $e) {
            $executionTime = round((microtime(true) - $startTime) * 1000, 2);

            Log::error('WhatsApp sending failed', [
                'branch_id' => $branch->id,
                'recipient' => $recipient,
                'provider' => $setting->whatsapp_provider,
                'execution_time_ms' => $executionTime,
                'error' => $e->getMessage(),
            ]);

            $log->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
            ]);

            throw $e;
        }

        return $log;
    }

    /**
     * Send SMS using the branch's configured provider.
     */
    public function sendSMS(
        Branch $branch,
        string $recipient,
        string $content,
        ?MessageTemplate $template = null,
        ?User $user = null,
        array $variables = []
    ): CommunicationLog {
        $setting = $branch->communicationSetting;

        if (! $setting || ! $setting->is_active || ! $setting->sms_provider) {
            throw new \Exception('SMS not configured for this branch');
        }

        // Process template variables
        $processedContent = $this->processTemplateVariables($content, $variables, $branch, $user);

        // Create communication log
        $log = CommunicationLog::create([
            'user_id' => $user?->id,
            'branch_id' => $branch->id,
            'type' => 'sms',
            'recipient' => $recipient,
            'content' => $processedContent,
            'template_id' => $template?->id,
            'status' => 'pending',
        ]);

        $startTime = microtime(true);

        try {
            $messageId = match ($setting->sms_provider) {
                'twilio' => $this->sendSMSViaTwilio($setting, $recipient, $processedContent),
                'africas-talking' => $this->sendSMSViaAfricasTalking($setting, $recipient, $processedContent),
                'jusibe' => $this->sendSMSViaJusibe($setting, $recipient, $processedContent),
                'bulksmsnigeria' => $this->sendSMSViaBulksmsnigeria($setting, $recipient, $processedContent),
                default => throw new \Exception("Unsupported SMS provider: {$setting->sms_provider}"),
            };

            $executionTime = round((microtime(true) - $startTime) * 1000, 2); // Convert to milliseconds

            $log->update([
                'status' => 'sent',
                'provider_message_id' => $messageId,
                'sent_at' => now(),
            ]);

            Log::info('SMS sent successfully', [
                'branch_id' => $branch->id,
                'recipient' => $recipient,
                'provider' => $setting->sms_provider,
                'execution_time_ms' => $executionTime,
                'message_id' => $messageId,
            ]);

        } catch (\Exception $e) {
            $executionTime = round((microtime(true) - $startTime) * 1000, 2);

            Log::error('SMS sending failed', [
                'branch_id' => $branch->id,
                'recipient' => $recipient,
                'provider' => $setting->sms_provider,
                'execution_time_ms' => $executionTime,
                'error' => $e->getMessage(),
            ]);

            $log->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
            ]);

            throw $e;
        }

        return $log;
    }

    /**
     * Send bulk messages to multiple recipients (synchronous - for small batches).
     */
    public function sendBulkMessages(
        Branch $branch,
        array $recipients, // ['email' => 'user@example.com', 'name' => 'John Doe']
        string $type, // 'email' or 'sms'
        string $subject,
        string $content,
        ?MessageTemplate $template = null,
        ?User $sender = null
    ): array {
        $results = [];

        foreach ($recipients as $recipient) {
            try {
                $variables = [
                    'recipient_name' => $recipient['name'] ?? 'Member',
                    'recipient_email' => $recipient['email'] ?? '',
                    'recipient_phone' => $recipient['phone'] ?? '',
                ];

                if ($type === 'email') {
                    $log = $this->sendEmail(
                        $branch,
                        $recipient['email'],
                        $subject,
                        $content,
                        $template,
                        $sender,
                        $variables
                    );
                } else {
                    $log = $this->sendSMS(
                        $branch,
                        $recipient['phone'],
                        $content,
                        $template,
                        $sender,
                        $variables
                    );
                }

                $results[] = [
                    'recipient' => $recipient,
                    'status' => 'success',
                    'log_id' => $log->id,
                ];

            } catch (\Exception $e) {
                $results[] = [
                    'recipient' => $recipient,
                    'status' => 'failed',
                    'error' => $e->getMessage(),
                ];
            }
        }

        return $results;
    }

    /**
     * Send bulk messages asynchronously using queue jobs.
     */
    public function sendBulkMessagesAsync(
        Branch $branch,
        array $recipients,
        string $type,
        string $subject,
        string $content,
        ?MessageTemplate $template = null,
        ?User $sender = null
    ): Batch {
        if ($type === 'email') {
            $job = new SendBulkEmailJob(
                $branch,
                $recipients,
                $subject,
                $content,
                $template?->id,
                $sender?->id
            );
        } else {
            $job = new SendBulkSMSJob(
                $branch,
                $recipients,
                $content,
                $template?->id,
                $sender?->id
            );
        }

        return Bus::batch([$job])
            ->name("Bulk {$type} - {$branch->name}")
            ->then(function (Batch $batch) use ($branch, $type) {
                Log::info("Bulk {$type} batch completed", [
                    'branch_id' => $branch->id,
                    'total_jobs' => $batch->totalJobs,
                    'processed_jobs' => $batch->processedJobs(),
                ]);
            })
            ->catch(function (Batch $batch, \Throwable $e) use ($branch, $type) {
                Log::error("Bulk {$type} batch failed", [
                    'branch_id' => $branch->id,
                    'error' => $e->getMessage(),
                    'failed_jobs' => $batch->failedJobs,
                ]);
            })
            ->dispatch();
    }

    /**
     * Send single email asynchronously using queue job.
     */
    public function sendEmailAsync(
        Branch $branch,
        string $recipient,
        string $subject,
        string $content,
        ?MessageTemplate $template = null,
        ?User $user = null,
        array $variables = []
    ): void {
        SendSingleEmailJob::dispatch(
            $branch,
            $recipient,
            $subject,
            $content,
            $template?->id,
            $user?->id,
            $variables
        );
    }

    /**
     * Send single SMS asynchronously using queue job.
     */
    public function sendSMSAsync(
        Branch $branch,
        string $recipient,
        string $content,
        ?MessageTemplate $template = null,
        ?User $user = null,
        array $variables = []
    ): void {
        SendSingleSMSJob::dispatch(
            $branch,
            $recipient,
            $content,
            $template?->id,
            $user?->id,
            $variables
        );
    }

    /**
     * Process template variables in content.
     */
    public function processTemplateVariables(
        string $content,
        array $variables,
        Branch $branch,
        ?User $user = null
    ): string {
        // Default variables
        $defaultVariables = [
            'branch_name' => $branch->name,
            'branch_email' => $branch->email,
            'branch_phone' => $branch->phone,
            'branch_venue' => $branch->venue,
            'branch_address' => $branch->venue,
            'branch_logo_url' => $this->getBranchLogoUrl($branch),
            'app_name' => config('app.name'),
            'current_date' => now()->format('F j, Y'),
            'current_year' => now()->format('Y'),
        ];

        // User-specific variables
        if ($user) {
            // Extract first name from full name
            $firstName = explode(' ', $user->name)[0] ?? $user->name;

            $defaultVariables = array_merge($defaultVariables, [
                'user_name' => $user->name,
                'first_name' => $firstName,
                'user_email' => $user->email,
                'user_phone' => $user->phone,
                'member_name' => $user->name,
                'member_first_name' => $firstName,
                'member_email' => $user->email,
            ]);
        }

        // Merge with provided variables
        $allVariables = array_merge($defaultVariables, $variables);

        // Replace variables in content
        foreach ($allVariables as $key => $value) {
            $content = str_replace(['{'.$key.'}', '{{'.$key.'}}'], (string) $value, $content);
        }

        return $content;
    }

    /**
     * Send email via SMTP.
     */
    private function sendEmailViaSMTP(
        CommunicationSetting $setting,
        string $recipient,
        string $subject,
        string $content
    ): string {
        // Use Laravel's built-in mail functionality
        Mail::html($content, function ($message) use ($recipient, $subject, $setting) {
            $message->to($recipient)
                ->subject($subject);

            if ($setting->from_email) {
                $message->from($setting->from_email, $setting->from_name ?? config('app.name'));
            }
        });

        return 'smtp-'.uniqid();
    }

    /**
     * Send email via Resend.
     */
    private function sendEmailViaResend(
        CommunicationSetting $setting,
        string $recipient,
        string $subject,
        string $content
    ): string {
        $config = $setting->email_config;
        $apiKey = $config['api_key'] ?? null;

        if (! $apiKey) {
            throw new \Exception('Resend API key not configured');
        }

        $fromEmail = $setting->from_email ?? 'noreply@'.config('app.url');
        $fromName = $setting->from_name ?? config('app.name');

        $response = Http::retry(3, 1000) // Retry 3 times with 1s delay
            ->timeout(30) // 30 second timeout
            ->withHeaders([
                'Authorization' => "Bearer {$apiKey}",
                'Content-Type' => 'application/json',
            ])->post('https://api.resend.com/emails', [
                'from' => "{$fromName} <{$fromEmail}>",
                'to' => [$recipient],
                'subject' => $subject,
                'html' => $content,
            ]);

        if (! $response->successful()) {
            throw new \Exception('Resend API error: '.$response->body());
        }

        $data = $response->json();

        return $data['id'] ?? 'resend-unknown';
    }

    /**
     * Send email via Mailgun.
     */
    private function sendEmailViaMailgun(
        CommunicationSetting $setting,
        string $recipient,
        string $subject,
        string $content
    ): string {
        $config = $setting->email_config;
        $apiKey = $config['api_key'] ?? null;
        $domain = $config['domain'] ?? null;

        if (! $apiKey || ! $domain) {
            throw new \Exception('Mailgun API key or domain not configured');
        }

        $fromEmail = $setting->from_email ?? 'noreply@'.$domain;
        $fromName = $setting->from_name ?? config('app.name');

        $response = Http::retry(3, 1000) // Retry 3 times with 1s delay
            ->timeout(30) // 30 second timeout
            ->withBasicAuth('api', $apiKey)
            ->asForm()
            ->post("https://api.mailgun.net/v3/{$domain}/messages", [
                'from' => "{$fromName} <{$fromEmail}>",
                'to' => $recipient,
                'subject' => $subject,
                'html' => $content,
            ]);

        if (! $response->successful()) {
            throw new \Exception('Mailgun API error: '.$response->body());
        }

        $data = $response->json();

        return $data['id'] ?? 'mailgun-unknown';
    }

    /**
     * Send email via Amazon SES.
     */
    private function sendEmailViaSES(
        CommunicationSetting $setting,
        string $recipient,
        string $subject,
        string $content
    ): string {
        // This would use AWS SDK - simplified version
        throw new \Exception('SES implementation not yet available');
    }

    /**
     * Send email via Postmark.
     */
    private function sendEmailViaPostmark(
        CommunicationSetting $setting,
        string $recipient,
        string $subject,
        string $content
    ): string {
        $config = $setting->email_config;
        $apiKey = $config['api_key'] ?? null;

        if (! $apiKey) {
            throw new \Exception('Postmark API key not configured');
        }

        $fromEmail = $setting->from_email ?? 'noreply@'.config('app.url');
        $fromName = $setting->from_name ?? config('app.name');

        $response = Http::retry(3, 1000) // Retry 3 times with 1s delay
            ->timeout(30) // 30 second timeout
            ->withHeaders([
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'X-Postmark-Server-Token' => $apiKey,
            ])->post('https://api.postmarkapp.com/email', [
                'From' => "{$fromName} <{$fromEmail}>",
                'To' => $recipient,
                'Subject' => $subject,
                'HtmlBody' => $content,
            ]);

        if (! $response->successful()) {
            throw new \Exception('Postmark API error: '.$response->body());
        }

        $data = $response->json();

        return $data['MessageID'] ?? 'postmark-unknown';
    }

    /**
     * Send SMS via Twilio.
     */
    private function sendSMSViaTwilio(
        CommunicationSetting $setting,
        string $recipient,
        string $content
    ): string {
        $config = $setting->sms_config;
        $sid = $config['account_sid'] ?? null;
        $token = $config['auth_token'] ?? null;
        $from = $config['from_number'] ?? null;

        if (! $sid || ! $token || ! $from) {
            throw new \Exception('Twilio configuration incomplete');
        }

        $response = Http::retry(3, 1000) // Retry 3 times with 1s delay
            ->timeout(30) // 30 second timeout
            ->withBasicAuth($sid, $token)
            ->asForm()
            ->post("https://api.twilio.com/2010-04-01/Accounts/{$sid}/Messages.json", [
                'From' => $from,
                'To' => $recipient,
                'Body' => $content,
            ]);

        if (! $response->successful()) {
            throw new \Exception('Twilio API error: '.$response->body());
        }

        $data = $response->json();

        return $data['sid'] ?? 'twilio-unknown';
    }

    /**
     * Send SMS via Africa's Talking.
     */
    private function sendSMSViaAfricasTalking(
        CommunicationSetting $setting,
        string $recipient,
        string $content
    ): string {
        $config = $setting->sms_config;
        $apiKey = $config['api_key'] ?? null;
        $username = $config['username'] ?? null;

        if (! $apiKey || ! $username) {
            throw new \Exception('Africa\'s Talking configuration incomplete');
        }

        // Format phone number for Africa's Talking (ensure it starts with +)
        $formattedRecipient = $recipient;
        if (! str_starts_with($recipient, '+')) {
            // If it doesn't start with +, assume it's a Nigerian number and add +234
            if (str_starts_with($recipient, '0')) {
                $formattedRecipient = '+234'.substr($recipient, 1);
            } elseif (str_starts_with($recipient, '234')) {
                $formattedRecipient = '+'.$recipient;
            } else {
                $formattedRecipient = '+'.$recipient;
            }
        }

        Log::info('Sending SMS via Africa\'s Talking', [
            'original_recipient' => $recipient,
            'formatted_recipient' => $formattedRecipient,
            'username' => $username,
            'content_length' => strlen($content),
        ]);

        $response = Http::retry(3, 1000) // Retry 3 times with 1s delay
            ->timeout(30) // 30 second timeout
            ->withHeaders([
                'Accept' => 'application/json',
                'Content-Type' => 'application/x-www-form-urlencoded',
                'apiKey' => $apiKey,
            ])->asForm()->post('https://api.africastalking.com/version1/messaging', [
                'username' => $username,
                'to' => $formattedRecipient,
                'message' => $content,
            ]);

        if (! $response->successful()) {
            Log::error('Africa\'s Talking API error', [
                'recipient' => $formattedRecipient,
                'status_code' => $response->status(),
                'response_body' => $response->body(),
            ]);
            throw new \Exception('Africa\'s Talking API error: '.$response->body());
        }

        $data = $response->json();

        // Log the full response for debugging
        Log::info('Africa\'s Talking API response', [
            'recipient' => $formattedRecipient,
            'response_status' => $response->status(),
            'response_body' => $data,
        ]);

        // Check for API errors in the response
        if (isset($data['SMSMessageData']['Recipients'][0]['status']) &&
            $data['SMSMessageData']['Recipients'][0]['status'] !== 'Success') {
            $errorMessage = $data['SMSMessageData']['Recipients'][0]['status'] ?? 'Unknown error';
            Log::error('Africa\'s Talking SMS delivery failed', [
                'recipient' => $formattedRecipient,
                'status' => $errorMessage,
                'cost' => $data['SMSMessageData']['Recipients'][0]['cost'] ?? 'Unknown',
            ]);
            throw new \Exception('SMS delivery failed: '.$errorMessage);
        }

        // Handle different response structures
        if (isset($data['SMSMessageData']['Recipients'][0]['messageId'])) {
            return $data['SMSMessageData']['Recipients'][0]['messageId'];
        } elseif (isset($data['SMSMessageData']['Recipients'][0]['status'])) {
            // If no messageId but has status, use status as identifier
            return 'at-'.$data['SMSMessageData']['Recipients'][0]['status'].'-'.uniqid();
        } elseif (isset($data['status'])) {
            return 'at-'.$data['status'].'-'.uniqid();
        } else {
            return 'at-unknown-'.uniqid();
        }
    }

    /**
     * Send SMS via Jusibe.
     */
    private function sendSMSViaJusibe(
        CommunicationSetting $setting,
        string $recipient,
        string $content
    ): string {
        $config = $setting->sms_config;
        $publicKey = $config['public_key'] ?? null;
        $accessToken = $config['access_token'] ?? null;
        $senderId = $config['sender_id'] ?? config('app.name');

        if (! $publicKey || ! $accessToken) {
            throw new \Exception('Jusibe configuration incomplete');
        }

        // Format phone number for Jusibe (Nigerian numbers only)
        $formattedRecipient = $recipient;
        if (! str_starts_with($recipient, '0') && ! str_starts_with($recipient, '+234')) {
            if (str_starts_with($recipient, '+234')) {
                $formattedRecipient = '0'.substr($recipient, 4); // +2349068719246 → 09068719246
            } else {
                $formattedRecipient = $recipient;
            }
        }

        Log::info('Sending SMS via Jusibe', [
            'original_recipient' => $recipient,
            'formatted_recipient' => $formattedRecipient,
            'sender_id' => $senderId,
            'content_length' => strlen($content),
        ]);

        $response = Http::retry(3, 1000) // Retry 3 times with 1s delay
            ->timeout(30) // 30 second timeout
            ->withBasicAuth($publicKey, $accessToken)
            ->asForm()
            ->post('https://jusibe.com/smsapi/send_sms', [
                'to' => $formattedRecipient,
                'from' => $senderId,
                'message' => $content,
            ]);

        if (! $response->successful()) {
            Log::error('Jusibe API error', [
                'recipient' => $formattedRecipient,
                'status_code' => $response->status(),
                'response_body' => $response->body(),
            ]);
            throw new \Exception('Jusibe API error: '.$response->body());
        }

        $data = $response->json();

        // Log the full response for debugging
        Log::info('Jusibe API response', [
            'recipient' => $formattedRecipient,
            'response_status' => $response->status(),
            'response_body' => $data,
        ]);

        // Check for API errors in the response
        if (isset($data['status']) && $data['status'] !== 'Sent') {
            $errorMessage = $data['status'] ?? 'Unknown error';
            Log::error('Jusibe SMS delivery failed', [
                'recipient' => $formattedRecipient,
                'status' => $errorMessage,
            ]);
            throw new \Exception('SMS delivery failed: '.$errorMessage);
        }

        return $data['message_id'] ?? 'jusibe-unknown-'.uniqid();
    }

    /**
     * Send SMS via Bulksmsnigeria.com.
     */
    private function sendSMSViaBulksmsnigeria(
        CommunicationSetting $setting,
        string $recipient,
        string $content
    ): string {
        $config = $setting->sms_config;
        $username = $config['username'] ?? null;
        $password = $config['password'] ?? null;
        $senderId = $config['sender_id'] ?? config('app.name');

        if (! $username || ! $password) {
            throw new \Exception('Bulksmsnigeria configuration incomplete');
        }

        // Format phone number for Bulksmsnigeria (Nigerian numbers)
        $formattedRecipient = $recipient;
        if (str_starts_with($recipient, '+234')) {
            $formattedRecipient = '0'.substr($recipient, 4); // +2349068719246 → 09068719246
        }

        Log::info('Sending SMS via Bulksmsnigeria', [
            'original_recipient' => $recipient,
            'formatted_recipient' => $formattedRecipient,
            'sender_id' => $senderId,
            'content_length' => strlen($content),
        ]);

        $response = Http::retry(3, 1000) // Retry 3 times with 1s delay
            ->timeout(30) // 30 second timeout
            ->asForm()
            ->post('https://www.bulksmsnigeria.com/api/v1/sms/create', [
                'from' => $senderId,
                'to' => $formattedRecipient,
                'body' => $content,
                'dnd' => 2, // Don't send to DND numbers
            ])->withBasicAuth($username, $password);

        if (! $response->successful()) {
            Log::error('Bulksmsnigeria API error', [
                'recipient' => $formattedRecipient,
                'status_code' => $response->status(),
                'response_body' => $response->body(),
            ]);
            throw new \Exception('Bulksmsnigeria API error: '.$response->body());
        }

        $data = $response->json();

        // Log the full response for debugging
        Log::info('Bulksmsnigeria API response', [
            'recipient' => $formattedRecipient,
            'response_status' => $response->status(),
            'response_body' => $data,
        ]);

        // Check for API errors in the response
        if (isset($data['status']) && $data['status'] !== 'success') {
            $errorMessage = $data['message'] ?? 'Unknown error';
            Log::error('Bulksmsnigeria SMS delivery failed', [
                'recipient' => $formattedRecipient,
                'status' => $errorMessage,
            ]);
            throw new \Exception('SMS delivery failed: '.$errorMessage);
        }

        return $data['data']['id'] ?? 'bulksmsnigeria-unknown-'.uniqid();
    }

    /**
     * Send WhatsApp via Twilio.
     */
    private function sendWhatsAppViaTwilio(
        CommunicationSetting $setting,
        string $recipient,
        string $content
    ): string {
        $config = $setting->whatsapp_config;
        $sid = $config['account_sid'] ?? null;
        $token = $config['auth_token'] ?? null;
        $from = $config['from_number'] ?? null;

        if (! $sid || ! $token || ! $from) {
            throw new \Exception('Twilio WhatsApp configuration incomplete');
        }

        // Format phone number for WhatsApp (ensure it starts with +)
        $formattedRecipient = $recipient;
        if (! str_starts_with($recipient, '+')) {
            if (str_starts_with($recipient, '0')) {
                $formattedRecipient = '+234'.substr($recipient, 1);
            } elseif (str_starts_with($recipient, '234')) {
                $formattedRecipient = '+'.$recipient;
            } else {
                $formattedRecipient = '+'.$recipient;
            }
        }

        Log::info('Sending WhatsApp via Twilio', [
            'original_recipient' => $recipient,
            'formatted_recipient' => $formattedRecipient,
            'content_length' => strlen($content),
        ]);

        $response = Http::retry(3, 1000) // Retry 3 times with 1s delay
            ->timeout(30) // 30 second timeout
            ->withBasicAuth($sid, $token)
            ->asForm()
            ->post("https://api.twilio.com/2010-04-01/Accounts/{$sid}/Messages.json", [
                'From' => "whatsapp:{$from}",
                'To' => "whatsapp:{$formattedRecipient}",
                'Body' => $content,
            ]);

        if (! $response->successful()) {
            Log::error('Twilio WhatsApp API error', [
                'recipient' => $formattedRecipient,
                'status_code' => $response->status(),
                'response_body' => $response->body(),
            ]);
            throw new \Exception('Twilio WhatsApp API error: '.$response->body());
        }

        $data = $response->json();

        return $data['sid'] ?? 'twilio-whatsapp-unknown';
    }

    /**
     * Send WhatsApp via Meta (WhatsApp Business API).
     */
    private function sendWhatsAppViaMeta(
        CommunicationSetting $setting,
        string $recipient,
        string $content
    ): string {
        $config = $setting->whatsapp_config;
        $accessToken = $config['access_token'] ?? null;
        $phoneNumberId = $config['phone_number_id'] ?? null;
        $businessAccountId = $config['business_account_id'] ?? null;

        if (! $accessToken || ! $phoneNumberId) {
            throw new \Exception('Meta WhatsApp configuration incomplete');
        }

        // Format phone number for WhatsApp (ensure it starts with +)
        $formattedRecipient = $recipient;
        if (! str_starts_with($recipient, '+')) {
            if (str_starts_with($recipient, '0')) {
                $formattedRecipient = '+234'.substr($recipient, 1);
            } elseif (str_starts_with($recipient, '234')) {
                $formattedRecipient = '+'.$recipient;
            } else {
                $formattedRecipient = '+'.$recipient;
            }
        }

        Log::info('Sending WhatsApp via Meta', [
            'original_recipient' => $recipient,
            'formatted_recipient' => $formattedRecipient,
            'phone_number_id' => $phoneNumberId,
            'content_length' => strlen($content),
        ]);

        $response = Http::retry(3, 1000) // Retry 3 times with 1s delay
            ->timeout(30) // 30 second timeout
            ->withHeaders([
                'Authorization' => "Bearer {$accessToken}",
                'Content-Type' => 'application/json',
            ])->post("https://graph.facebook.com/v18.0/{$phoneNumberId}/messages", [
                'messaging_product' => 'whatsapp',
                'to' => $formattedRecipient,
                'type' => 'text',
                'text' => [
                    'body' => $content,
                ],
            ]);

        if (! $response->successful()) {
            Log::error('Meta WhatsApp API error', [
                'recipient' => $formattedRecipient,
                'status_code' => $response->status(),
                'response_body' => $response->body(),
            ]);
            throw new \Exception('Meta WhatsApp API error: '.$response->body());
        }

        $data = $response->json();

        // Log the full response for debugging
        Log::info('Meta WhatsApp API response', [
            'recipient' => $formattedRecipient,
            'response_status' => $response->status(),
            'response_body' => $data,
        ]);

        return $data['messages'][0]['id'] ?? 'meta-whatsapp-unknown-'.uniqid();
    }

    /**
     * Test communication settings.
     */
    public function testCommunicationSettings(CommunicationSetting $setting): array
    {
        $results = [];

        // Test email
        if ($setting->email_config) {
            try {
                // Send test email to the branch email
                $testEmail = $setting->from_email ?? $setting->branch->email;
                if ($testEmail) {
                    $this->sendEmail(
                        $setting->branch,
                        $testEmail,
                        'Test Email from '.config('app.name'),
                        'This is a test email to verify your email configuration is working correctly.',
                        null,
                        null,
                        ['test_variable' => 'Test Value']
                    );
                    $results['email'] = ['status' => 'success', 'message' => 'Test email sent successfully'];
                } else {
                    $results['email'] = ['status' => 'error', 'message' => 'No test email address available'];
                }
            } catch (\Exception $e) {
                $results['email'] = ['status' => 'error', 'message' => $e->getMessage()];
            }
        }

        // Test SMS
        if ($setting->sms_config && $setting->sms_provider) {
            $results['sms'] = ['status' => 'info', 'message' => 'SMS testing requires a valid phone number'];
        }

        return $results;
    }

    /**
     * Get branch logo URL.
     */
    private function getBranchLogoUrl(Branch $branch): string
    {
        // Check if branch has a custom logo
        if (! empty($branch->logo_path) && file_exists(public_path($branch->logo_path))) {
            return url($branch->logo_path);
        }

        // Return default app logo
        return url('/img/logo.png');
    }

    /**
     * Wrap content in HTML email template.
     */
    public function wrapInHtmlTemplate(
        string $content,
        string $subject,
        Branch $branch,
        ?User $user = null
    ): string {
        $logoUrl = $this->getBranchLogoUrl($branch);
        $branchName = $branch->name;
        $currentYear = now()->format('Y');
        $appName = config('app.name');

        return "
<!DOCTYPE html>
<html lang=\"en\">
<head>
    <meta charset=\"UTF-8\">
    <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">
    <title>{$subject}</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            line-height: 1.6;
            color: #333333;
            background-color: #f8f9fa;
        }
        .email-container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        .email-header {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
            padding: 30px 40px;
            text-align: center;
        }
        .email-logo {
            max-width: 120px;
            height: auto;
            margin-bottom: 20px;
            border-radius: 8px;
        }
        .email-title {
            font-size: 24px;
            font-weight: 600;
            margin: 0;
        }
        .email-content {
            padding: 40px;
        }
        .email-content h1 {
            color: #1f2937;
            font-size: 20px;
            margin-bottom: 20px;
        }
        .email-content h2 {
            color: #374151;
            font-size: 18px;
            margin-top: 30px;
            margin-bottom: 15px;
        }
        .email-content p {
            margin-bottom: 16px;
            color: #4b5563;
        }
        .email-content ul, .email-content ol {
            margin-bottom: 16px;
            padding-left: 20px;
        }
        .email-content li {
            margin-bottom: 8px;
            color: #4b5563;
        }
        .button {
            display: inline-block;
            padding: 12px 24px;
            background-color: #10b981;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 500;
            margin: 20px 0;
        }
        .button:hover {
            background-color: #059669;
        }
        .email-footer {
            background-color: #f9fafb;
            padding: 30px 40px;
            text-align: center;
            border-top: 1px solid #e5e7eb;
        }
        .footer-text {
            font-size: 14px;
            color: #6b7280;
            margin: 0;
        }
        .footer-links {
            margin-top: 15px;
        }
        .footer-links a {
            color: #10b981;
            text-decoration: none;
            margin: 0 10px;
            font-size: 14px;
        }
        .footer-links a:hover {
            text-decoration: underline;
        }
        @media only screen and (max-width: 600px) {
            .email-header,
            .email-content,
            .email-footer {
                padding: 20px;
            }
            .email-title {
                font-size: 20px;
            }
        }
    </style>
</head>
<body>
    <div class=\"email-container\">
        <div class=\"email-header\">
            <img src=\"{$logoUrl}\" alt=\"{$branchName} Logo\" class=\"email-logo\">
            <h1 class=\"email-title\">{$branchName}</h1>
        </div>
        
        <div class=\"email-content\">
            {$content}
        </div>
        
        <div class=\"email-footer\">
            <p class=\"footer-text\">
                © {$currentYear} {$branchName}. All rights reserved.
            </p>
            <div class=\"footer-links\">
                <a href=\"mailto:{$branch->email}\">Contact Us</a>
                <a href=\"#\">Unsubscribe</a>
                <a href=\"#\">Privacy Policy</a>
            </div>
            <p class=\"footer-text\" style=\"margin-top: 15px; font-size: 12px;\">
                {$branch->venue}<br>
                Powered by {$appName}
            </p>
        </div>
    </div>
</body>
</html>";
    }

    /**
     * Convert plain text to HTML with basic formatting.
     */
    public function convertToHtml(string $content): string
    {
        // Convert line breaks to paragraphs
        $content = trim($content);
        $paragraphs = explode("\n\n", $content);

        $html = '';
        foreach ($paragraphs as $paragraph) {
            $paragraph = trim($paragraph);
            if ($paragraph) {
                // Convert single line breaks to <br>
                $paragraph = nl2br(htmlspecialchars($paragraph));
                $html .= "<p>{$paragraph}</p>\n";
            }
        }

        return $html;
    }
}
