<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\ChurchServiceManager;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

final class GenerateRecurringInstances extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'events:generate-recurring {--weeks=12 : Number of weeks ahead to generate instances}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate recurring event instances for church services and events';

    /**
     * Create a new command instance.
     */
    public function __construct(private readonly ChurchServiceManager $serviceManager)
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $weeksAhead = (int) $this->option('weeks');
        
        $this->info("Generating recurring event instances for {$weeksAhead} weeks ahead...");
        
        try {
            $created = $this->serviceManager->generateAllRecurringInstances($weeksAhead);
            
            $this->info("Successfully generated {$created} recurring event instances.");
            
            Log::info('Recurring event instances generated via command', [
                'instances_created' => $created,
                'weeks_ahead' => $weeksAhead,
            ]);
            
            return Command::SUCCESS;
            
        } catch (\Exception $e) {
            $this->error('Failed to generate recurring instances: ' . $e->getMessage());
            
            Log::error('Failed to generate recurring instances via command', [
                'error' => $e->getMessage(),
                'weeks_ahead' => $weeksAhead,
            ]);
            
            return Command::FAILURE;
        }
    }
}
