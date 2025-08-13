<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\SmallGroup;
use App\Models\SmallGroupMeetingReport;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

final class SmallGroupMeetingReportSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get existing small groups and users
        $smallGroups = SmallGroup::with('leader.user')->get();
        $users = User::all();

        if ($smallGroups->isEmpty() || $users->isEmpty()) {
            $this->command->warn('No small groups or users found. Please run SmallGroupSeeder and UserSeeder first.');
            return;
        }

        $this->command->info('Creating small group meeting reports...');

        // Create reports for each small group
        foreach ($smallGroups as $smallGroup) {
            // Get the leader user for this small group
            $leaderUser = $smallGroup->leader?->user ?? $users->random();

            // Create 8-12 reports per group (covering last 3 months)
            $numberOfReports = fake()->numberBetween(8, 12);
            
            for ($i = 0; $i < $numberOfReports; $i++) {
                $status = fake()->randomElement(['approved', 'approved', 'approved', 'submitted', 'rejected']);
                
                $reportData = [
                    'small_group_id' => $smallGroup->id,
                    'reported_by' => $leaderUser->id,
                    'meeting_date' => fake()->dateTimeBetween('-3 months', '-1 week')->format('Y-m-d'),
                    'meeting_time' => fake()->time('H:i'),
                    'meeting_location' => fake()->randomElement([
                        'Church Hall',
                        'Community Center',
                        $smallGroup->leader?->first_name . '\'s Home',
                        'Park Pavilion',
                        'School Classroom',
                        'Online Meeting',
                        'Member\'s Home',
                    ]),
                    'status' => $status,
                    'submitted_at' => fake()->dateTimeBetween('-3 months', 'now'),
                ];

                // Set attendance based on group capacity
                $maxAttendance = min($smallGroup->capacity ?? 25, 30);
                $maleAttendance = fake()->numberBetween(0, intval($maxAttendance * 0.4));
                $femaleAttendance = fake()->numberBetween(0, intval($maxAttendance * 0.5));
                $childrenAttendance = fake()->numberBetween(0, intval($maxAttendance * 0.3));
                
                $totalAttendance = $maleAttendance + $femaleAttendance + $childrenAttendance;
                $firstTimeGuests = fake()->numberBetween(0, min(5, $totalAttendance));
                $converts = fake()->numberBetween(0, min(3, $firstTimeGuests));

                $reportData = array_merge($reportData, [
                    'male_attendance' => $maleAttendance,
                    'female_attendance' => $femaleAttendance,
                    'children_attendance' => $childrenAttendance,
                    'first_time_guests' => $firstTimeGuests,
                    'converts' => $converts,
                    'total_attendance' => $totalAttendance,
                    'meeting_notes' => fake()->optional(0.8)->paragraphs(2, true),
                    'prayer_requests' => fake()->optional(0.6)->paragraphs(1, true),
                    'testimonies' => fake()->optional(0.4)->paragraphs(1, true),
                    'attendee_names' => $this->generateAttendeeNames($totalAttendance),
                ]);

                // Set approval data based on status
                if ($status === 'approved') {
                    $pastor = $users->filter(function ($user) {
                        return $user->isBranchPastor() || $user->isSuperAdmin();
                    })->random();
                    
                    $reportData['approved_by'] = $pastor->id;
                    $reportData['approved_at'] = fake()->dateTimeBetween($reportData['submitted_at'], 'now');
                } elseif ($status === 'rejected') {
                    $reportData['rejection_reason'] = fake()->randomElement([
                        'Attendance numbers seem inconsistent with previous reports.',
                        'Please provide more details about the converts.',
                        'Meeting notes are incomplete.',
                        'Guest information needs verification.',
                        'Please clarify the meeting location.',
                    ]);
                }

                SmallGroupMeetingReport::create($reportData);
            }
        }

        // Create some additional recent reports (last 2 weeks)
        $this->command->info('Creating recent meeting reports...');
        
        foreach ($smallGroups->take(5) as $smallGroup) {
            $leaderUser = $smallGroup->leader?->user ?? $users->random();
            
            SmallGroupMeetingReport::create([
                'small_group_id' => $smallGroup->id,
                'reported_by' => $leaderUser->id,
                'meeting_date' => fake()->dateTimeBetween('-2 weeks', '-3 days')->format('Y-m-d'),
                'meeting_time' => fake()->time('H:i'),
                'meeting_location' => 'Church Hall',
                'male_attendance' => fake()->numberBetween(8, 15),
                'female_attendance' => fake()->numberBetween(10, 18),
                'children_attendance' => fake()->numberBetween(3, 8),
                'first_time_guests' => fake()->numberBetween(1, 4),
                'converts' => fake()->numberBetween(0, 2),
                'total_attendance' => 0, // Will be calculated by model
                'meeting_notes' => 'Great discussion about faith and community. Everyone was engaged.',
                'prayer_requests' => 'Prayers for healing, job opportunities, and family unity.',
                'testimonies' => fake()->optional(0.7)->sentence(),
                'attendee_names' => $this->generateAttendeeNames(fake()->numberBetween(15, 25)),
                'status' => 'submitted',
                'submitted_at' => fake()->dateTimeBetween('-1 week', 'now'),
            ]);
        }

        $totalReports = SmallGroupMeetingReport::count();
        $this->command->info("Created {$totalReports} small group meeting reports successfully!");
    }

    /**
     * Generate realistic attendee names.
     */
    private function generateAttendeeNames(int $count): array
    {
        $names = [];
        for ($i = 0; $i < $count; $i++) {
            $names[] = fake()->name();
        }
        return $names;
    }
}
