<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

/**
 * Answers "did that email actually go?" — which nothing else here can.
 *
 * The password reset endpoint replies the same way whether or not an address is
 * registered, deliberately, so that it cannot be used to find out who attends
 * the church. The cost is that it also cannot tell anyone whether delivery
 * worked. Laravel is no more forthcoming: with MAIL_MAILER set to "log" it
 * reports a perfectly successful send, straight into a file nobody reads.
 *
 * So this prints the configuration first, then tries a real send and shows
 * whatever comes back.
 */
final class TestMail extends Command
{
    protected $signature = 'mail:test {email : Where to send the test}';

    protected $description = 'Send a test email and report exactly what happened';

    public function handle(): int
    {
        $to = (string) $this->argument('email');
        $mailer = config('mail.default');

        $this->newLine();
        $this->line('  Configuration');
        $this->table(['Setting', 'Value'], [
            ['MAIL_MAILER', $mailer],
            ['host', config("mail.mailers.{$mailer}.host") ?? '—'],
            ['port', config("mail.mailers.{$mailer}.port") ?? '—'],
            ['username', config("mail.mailers.{$mailer}.username") ? 'set' : 'NOT SET'],
            ['password', config("mail.mailers.{$mailer}.password") ? 'set' : 'NOT SET'],
            ['from address', config('mail.from.address') ?? '—'],
            ['from name', config('mail.from.name') ?? '—'],
        ]);

        if ($mailer === 'log') {
            $this->warn('  MAIL_MAILER is "log", so nothing is delivered — mail goes to the log file.');
            $this->line('  That alone explains a reset link that never arrives.');
            $this->newLine();
        }

        if (in_array(config('mail.from.address'), [null, '', 'hello@example.com'], true)) {
            $this->warn('  The "from" address is unset or still the framework default.');
            $this->line('  Most providers reject mail from an address they do not recognise.');
            $this->newLine();
        }

        $this->line("  Sending to {$to} …");

        try {
            Mail::raw(
                "This is a test from the LifePointe dashboard.\n\n"
                    ."If you are reading it, password reset emails can reach you too.\n"
                    .'Sent '.now()->toDayDateTimeString().'.',
                fn ($message) => $message->to($to)->subject('LifePointe mail test'),
            );
        } catch (\Throwable $e) {
            $this->newLine();
            $this->error('  Sending failed: '.$e->getMessage());
            $this->line('  '.get_class($e));

            return self::FAILURE;
        }

        $this->newLine();
        $this->info('  Handed to the mailer without error.');

        if ($mailer === 'log') {
            $this->line('  But the mailer is "log", so check storage/logs — nothing was posted.');
        } else {
            $this->line('  Check the inbox, and the spam folder. If it never turns up, the');
            $this->line('  provider accepted and then dropped it — look at their dashboard.');
        }

        return self::SUCCESS;
    }
}
