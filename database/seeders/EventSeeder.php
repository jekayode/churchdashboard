<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Event;
use Illuminate\Database\Seeder;

final class EventSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $eventsData = [
            [
                'name' => 'Sunday Service',
                'description' => 'Weekly worship and teaching service.',
                'location' => 'Main Auditorium',
                'frequency' => 'weekly',
                'registration_type' => 'custom_form',
                'status' => 'published',
            ],
            [
                'name' => 'Youth Hangout',
                'description' => 'Monthly youth fellowship and games.',
                'location' => 'Youth Center',
                'frequency' => 'monthly',
                'registration_type' => 'custom_form',
                'status' => 'published',
            ],
            [
                'name' => 'Quarterly Outreach',
                'description' => 'Community outreach and evangelism.',
                'location' => 'City Park',
                'frequency' => 'quarterly',
                'registration_type' => 'link',
                'registration_link' => 'https://example.com/outreach',
                'status' => 'published',
            ],
        ];

        $branches = Branch::all();
        foreach ($branches as $branch) {
            foreach ($eventsData as $eventData) {
                $startDate = now()->addDays(rand(1, 30))->setTime(rand(8, 18), 0);
                $endDate = (clone $startDate)->addHours(2);
                Event::firstOrCreate([
                    'branch_id' => $branch->id,
                    'name' => $eventData['name'],
                ], array_merge($eventData, [
                    'branch_id' => $branch->id,
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                ]));
            }
        }

        $this->command->info('Events seeded successfully!');
    }
}
