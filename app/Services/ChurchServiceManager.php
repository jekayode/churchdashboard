<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Event;
use App\Models\Branch;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

final class ChurchServiceManager
{
    /**
     * Create a Sunday service for a branch.
     */
    public function createSundayService(
        Branch $branch,
        array $serviceData,
        bool $generateInstances = true
    ): Event {
        $defaultData = [
            'service_type' => 'Sunday Service',
            'day_of_week' => 0, // Sunday
            'frequency' => 'weekly',
            'is_recurring' => true,
            'is_recurring_instance' => false,
            'registration_type' => 'simple',
            'status' => 'active',
            'is_public' => true,
        ];

        $eventData = array_merge($defaultData, $serviceData, [
            'branch_id' => $branch->id,
        ]);

        // Ensure we have a proper start_date
        if (!isset($eventData['start_date']) || !$eventData['start_date']) {
            $nextSunday = Carbon::now()->next(Carbon::SUNDAY);
            if (isset($eventData['service_time'])) {
                $serviceTime = Carbon::createFromFormat('H:i', $eventData['service_time']);
                $nextSunday->setTime($serviceTime->hour, $serviceTime->minute);
            } else {
                $nextSunday->setTime(10, 0); // Default 10 AM
            }
            $eventData['start_date'] = $nextSunday;
        }

        $event = Event::create($eventData);

        if ($generateInstances) {
            $this->generateRecurringInstances($event);
        }

        Log::info('Sunday service created', [
            'branch_id' => $branch->id,
            'event_id' => $event->id,
            'service_name' => $event->service_name ?? $event->name,
        ]);

        return $event;
    }

    /**
     * Create a midweek service for a branch.
     */
    public function createMidweekService(
        Branch $branch,
        array $serviceData,
        bool $generateInstances = true
    ): Event {
        $defaultData = [
            'service_type' => 'MidWeek',
            'day_of_week' => 3, // Wednesday (default)
            'frequency' => 'weekly',
            'is_recurring' => true,
            'is_recurring_instance' => false,
            'registration_type' => 'simple',
            'status' => 'active',
            'is_public' => true,
        ];

        $eventData = array_merge($defaultData, $serviceData, [
            'branch_id' => $branch->id,
        ]);

        // Ensure we have a proper start_date
        if (!isset($eventData['start_date']) || !$eventData['start_date']) {
            $dayOfWeek = $eventData['day_of_week'];
            $nextServiceDay = Carbon::now()->next($dayOfWeek);
            if (isset($eventData['service_time'])) {
                $serviceTime = Carbon::createFromFormat('H:i', $eventData['service_time']);
                $nextServiceDay->setTime($serviceTime->hour, $serviceTime->minute);
            } else {
                $nextServiceDay->setTime(19, 0); // Default 7 PM
            }
            $eventData['start_date'] = $nextServiceDay;
        }

        $event = Event::create($eventData);

        if ($generateInstances) {
            $this->generateRecurringInstances($event);
        }

        Log::info('Midweek service created', [
            'branch_id' => $branch->id,
            'event_id' => $event->id,
            'service_name' => $event->service_name ?? $event->name,
            'day_of_week' => $event->day_of_week,
        ]);

        return $event;
    }

    /**
     * Generate recurring instances for an event.
     */
    public function generateRecurringInstances(Event $event, int $weeksAhead = 12): int
    {
        if (!$event->is_recurring) {
            return 0;
        }

        return DB::transaction(function () use ($event, $weeksAhead) {
            return $event->createRecurringInstances($weeksAhead);
        });
    }

    /**
     * Get all services for a branch.
     */
    public function getBranchServices(Branch $branch, bool $includeInstances = false): Collection
    {
        $query = Event::where('branch_id', $branch->id)
            ->whereIn('service_type', ['Sunday Service', 'MidWeek']);

        if (!$includeInstances) {
            $query->where('is_recurring_instance', false);
        }

        return $query->orderBy('day_of_week')
            ->orderBy('service_time')
            ->get();
    }

    /**
     * Get upcoming service instances for a branch.
     */
    public function getUpcomingServices(
        Branch $branch,
        int $daysAhead = 30,
        ?string $serviceType = null
    ): Collection {
        $query = Event::where('branch_id', $branch->id)
            ->where('is_recurring_instance', true)
            ->where('start_date', '>=', now())
            ->where('start_date', '<=', now()->addDays($daysAhead))
            ->orderBy('start_date');

        if ($serviceType) {
            $query->where('service_type', $serviceType);
        }

        return $query->get();
    }

    /**
     * Setup default services for a branch.
     */
    public function setupDefaultServices(Branch $branch): array
    {
        $services = [];

        // Create Sunday Service
        $services['sunday'] = $this->createSundayService($branch, [
            'name' => 'Sunday Service',
            'description' => 'Weekly Sunday worship service',
            'service_time' => '10:00',
            'service_name' => 'Main Service',
            'location' => $branch->name . ' Sanctuary',
            'max_capacity' => 200,
        ]);

        // Create Midweek Service (Wednesday)
        $services['midweek'] = $this->createMidweekService($branch, [
            'name' => 'Midweek Service',
            'description' => 'Weekly midweek prayer and Bible study',
            'service_time' => '19:00',
            'service_name' => 'Prayer & Bible Study',
            'location' => $branch->name . ' Fellowship Hall',
            'max_capacity' => 100,
            'day_of_week' => 3, // Wednesday
        ]);

        Log::info('Default services setup completed', [
            'branch_id' => $branch->id,
            'services_created' => count($services),
        ]);

        return $services;
    }

    /**
     * Create multiple Sunday services for a branch.
     */
    public function createMultipleSundayServices(Branch $branch, array $servicesData): array
    {
        $services = [];

        foreach ($servicesData as $index => $serviceData) {
            $serviceData['service_name'] = $serviceData['service_name'] ?? 
                ($index === 0 ? 'First Service' : 'Second Service');
            
            $services[] = $this->createSundayService($branch, $serviceData);
        }

        Log::info('Multiple Sunday services created', [
            'branch_id' => $branch->id,
            'services_count' => count($services),
        ]);

        return $services;
    }

    /**
     * Generate instances for all recurring services.
     */
    public function generateAllRecurringInstances(int $weeksAhead = 12): int
    {
        $recurringEvents = Event::where('is_recurring', true)->get();
        $totalCreated = 0;

        foreach ($recurringEvents as $event) {
            $totalCreated += $this->generateRecurringInstances($event, $weeksAhead);
        }

        Log::info('All recurring instances generated', [
            'total_created' => $totalCreated,
            'weeks_ahead' => $weeksAhead,
        ]);

        return $totalCreated;
    }
} 