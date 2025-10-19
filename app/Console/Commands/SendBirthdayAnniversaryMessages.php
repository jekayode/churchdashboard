<?php

namespace App\Console\Commands;

use App\Models\CommunicationSetting;
use App\Models\Member;
use App\Services\CommunicationService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SendBirthdayAnniversaryMessages extends Command
{
    protected $signature = 'messages:send-birthday-anniversary {--dry-run : Show what would be sent without actually sending}';

    protected $description = 'Send birthday and anniversary messages to members';

    public function handle()
    {
        $isDryRun = $this->option('dry-run');

        if ($isDryRun) {
            $this->info('🔍 DRY RUN MODE - No messages will be sent');
        }

        $this->info('🎂 Processing birthday and anniversary messages...');

        // Get all branches with communication settings
        $branches = CommunicationSetting::with('branch')->get();

        foreach ($branches as $setting) {
            $this->info("Processing branch: {$setting->branch->name}");

            // Process birthdays
            $this->processBirthdays($setting, $isDryRun);

            // Process anniversaries
            $this->processAnniversaries($setting, $isDryRun);
        }

        $this->info('✅ Birthday and anniversary processing completed!');
    }

    private function processBirthdays($setting, $isDryRun)
    {
        $branchId = $setting->branch_id;
        $today = now()->format('Y-m-d');

        // Find members with birthdays today
        $birthdayMembers = Member::where('branch_id', $branchId)
            ->whereNotNull('date_of_birth')
            ->whereRaw("DATE_FORMAT(date_of_birth, '%m-%d') = ?", [now()->format('m-d')])
            ->whereNotNull('email')
            ->where('email', '!=', '')
            ->get();

        if ($birthdayMembers->isEmpty()) {
            $this->line('  No birthday members found for today');

            return;
        }

        $this->info("  Found {$birthdayMembers->count()} birthday members");

        // Get birthday template
        $birthdayTemplate = $this->getBirthdayTemplate($setting);
        if (! $birthdayTemplate) {
            $this->warn("  No birthday template configured for branch {$setting->branch->name}");

            return;
        }

        foreach ($birthdayMembers as $member) {
            $this->line("  🎂 {$member->name} - Birthday today!");

            if (! $isDryRun) {
                $this->sendBirthdayMessage($member, $birthdayTemplate, $setting);
            }
        }
    }

    private function processAnniversaries($setting, $isDryRun)
    {
        $branchId = $setting->branch_id;
        $today = now()->format('Y-m-d');

        // Find members with anniversaries today
        $anniversaryMembers = Member::where('branch_id', $branchId)
            ->whereNotNull('anniversary')
            ->whereRaw("DATE_FORMAT(anniversary, '%m-%d') = ?", [now()->format('m-d')])
            ->whereNotNull('email')
            ->where('email', '!=', '')
            ->get();

        if ($anniversaryMembers->isEmpty()) {
            $this->line('  No anniversary members found for today');

            return;
        }

        $this->info("  Found {$anniversaryMembers->count()} anniversary members");

        // Get anniversary template
        $anniversaryTemplate = $this->getAnniversaryTemplate($setting);
        if (! $anniversaryTemplate) {
            $this->warn("  No anniversary template configured for branch {$setting->branch->name}");

            return;
        }

        foreach ($anniversaryMembers as $member) {
            $this->line("  💒 {$member->name} - Anniversary today!");

            if (! $isDryRun) {
                $this->sendAnniversaryMessage($member, $anniversaryTemplate, $setting);
            }
        }
    }

    private function getBirthdayTemplate($setting)
    {
        $birthdayTemplateId = $setting->birthday_template_id ?? null;
        if (! $birthdayTemplateId) {
            return null;
        }

        return \App\Models\MessageTemplate::find($birthdayTemplateId);
    }

    private function getAnniversaryTemplate($setting)
    {
        $anniversaryTemplateId = $setting->anniversary_template_id ?? null;
        if (! $anniversaryTemplateId) {
            return null;
        }

        return \App\Models\MessageTemplate::find($anniversaryTemplateId);
    }

    private function sendBirthdayMessage($member, $template, $setting)
    {
        try {
            $communicationService = app(CommunicationService::class);

            // Process template variables
            $subject = $this->processTemplate($template->subject, $member);
            $content = $this->processTemplate($template->content, $member);

            // Send email
            if ($member->email) {
                $communicationService->sendEmail(
                    $member->email,
                    $subject,
                    $content,
                    $setting->branch_id
                );
                $this->line("    ✅ Email sent to {$member->email}");
            }

            // Send SMS if phone available
            if ($member->phone) {
                $communicationService->sendSMS(
                    $member->phone,
                    $content,
                    $setting->branch_id
                );
                $this->line("    ✅ SMS sent to {$member->phone}");
            }

            Log::info("Birthday message sent to member {$member->id}", [
                'member_id' => $member->id,
                'member_name' => $member->name,
                'template_id' => $template->id,
                'branch_id' => $setting->branch_id,
            ]);

        } catch (\Exception $e) {
            $this->error("    ❌ Failed to send birthday message to {$member->name}: {$e->getMessage()}");
            Log::error('Failed to send birthday message', [
                'member_id' => $member->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    private function sendAnniversaryMessage($member, $template, $setting)
    {
        try {
            $communicationService = app(CommunicationService::class);

            // Process template variables
            $subject = $this->processTemplate($template->subject, $member);
            $content = $this->processTemplate($template->content, $member);

            // Send email
            if ($member->email) {
                $communicationService->sendEmail(
                    $member->email,
                    $subject,
                    $content,
                    $setting->branch_id
                );
                $this->line("    ✅ Email sent to {$member->email}");
            }

            // Send SMS if phone available
            if ($member->phone) {
                $communicationService->sendSMS(
                    $member->phone,
                    $content,
                    $setting->branch_id
                );
                $this->line("    ✅ SMS sent to {$member->phone}");
            }

            Log::info("Anniversary message sent to member {$member->id}", [
                'member_id' => $member->id,
                'member_name' => $member->name,
                'template_id' => $template->id,
                'branch_id' => $setting->branch_id,
            ]);

        } catch (\Exception $e) {
            $this->error("    ❌ Failed to send anniversary message to {$member->name}: {$e->getMessage()}");
            Log::error('Failed to send anniversary message', [
                'member_id' => $member->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    private function processTemplate($template, $member)
    {
        // Replace template variables
        $variables = [
            'member_name' => $member->name,
            'member_first_name' => explode(' ', $member->name)[0] ?? $member->name,
            'member_email' => $member->email,
            'member_phone' => $member->phone,
            'member_birthday' => $member->date_of_birth ? $member->date_of_birth->format('F j') : '',
            'member_anniversary' => $member->anniversary ? $member->anniversary->format('F j') : '',
            'church_name' => $member->branch->name ?? 'Our Church',
            'current_date' => now()->format('F j, Y'),
            'current_year' => now()->year,
        ];

        $processed = $template;
        foreach ($variables as $key => $value) {
            $processed = str_replace("{{$key}}", $value, $processed);
        }

        return $processed;
    }
}

