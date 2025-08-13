<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\ChurchServiceManager;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

final class GenerateRecurringServices extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'services:generate-recurring {--weeks=12 : Number of weeks ahead to generate} {--force : Force regeneration even if instances exist}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate recurring service instances for all branches';

    public function __construct(
        private readonly ChurchServiceManager $serviceManager
    ) {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $weeksAhead = (int) $this->option('weeks');
        $force = $this->option('force');

        $this->info("Generating recurring service instances for {$weeksAhead} weeks ahead...");

        try {
            $startTime = microtime(true);
            $created = $this->serviceManager->generateAllRecurringInstances($weeksAhead);
            $endTime = microtime(true);

            $executionTime = round($endTime - $startTime, 2);

            if ($created > 0) {
                $this->info("✅ Successfully generated {$created} recurring service instances.");
                $this->line("   Execution time: {$executionTime} seconds");
                
                Log::info('Recurring services generated via command', [
                    'instances_created' => $created,
                    'weeks_ahead' => $weeksAhead,
                    'execution_time' => $executionTime,
                ]);
            } else {
                $this->comment("ℹ️  No new service instances were created. All instances may already exist.");
            }

            return self::SUCCESS;

        } catch (\Exception $e) {
            $this->error("❌ Failed to generate recurring service instances: " . $e->getMessage());
            
            Log::error('Failed to generate recurring services via command', [
                'error' => $e->getMessage(),
                'weeks_ahead' => $weeksAhead,
                'trace' => $e->getTraceAsString(),
            ]);

            return self::FAILURE;
        }
    }
}
