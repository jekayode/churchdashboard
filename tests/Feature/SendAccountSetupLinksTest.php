<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;

final class SendAccountSetupLinksTest extends TestCase
{
    public function test_command_fails_outside_production(): void
    {
        // Ensure we are not in production for this test
        $this->app->detectEnvironment(fn () => 'testing');

        $this->artisan('users:send-setup-links --role=branch_pastor')
            ->expectsOutput('This command can only be executed in the production environment.')
            ->assertExitCode(1);
    }
}
