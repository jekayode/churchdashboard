<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Event;
use Illuminate\Database\Seeder;

/**
 * The church's current programme, as announced on 19 July 2026.
 *
 * Seeded rather than hand-entered so the same calendar can be recreated on
 * another environment. Re-running updates the existing rows instead of
 * duplicating them.
 */
final class LifePointeEventsSeeder extends Seeder
{
    private const BRANCH_ID = 1;

    private const VENUE = 'Screen 4, Nova Cinema, Novare Mall, Sangotedo';

    public function run(): void
    {
        foreach ($this->events() as $event) {
            Event::updateOrCreate(
                ['branch_id' => self::BRANCH_ID, 'name' => $event['name']],
                $event + [
                    'branch_id' => self::BRANCH_ID,
                    'status' => 'active',
                    'is_public' => true,
                ],
            );
        }
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function events(): array
    {
        return [
            [
                'name' => 'LifeGroup Sunday',
                'description' => 'LifeGroups are where community really happens — come ready to connect, and bring someone along. Two services: 8:15 AM and 10:00 AM.',
                'type' => 'service',
                'service_type' => 'Sunday Service',
                'start_date' => '2026-07-26 08:15:00',
                'end_date' => '2026-07-26 11:30:00',
                'location' => self::VENUE,
                'has_multiple_services' => true,
                'service_time' => '08:15:00',
                'service_end_time' => '09:45:00',
                'service_name' => 'First Service',
                'second_service_time' => '10:00:00',
                'second_service_end_time' => '11:30:00',
                'second_service_name' => 'Second Service',
                'frequency' => 'monthly',
                'is_recurring' => true,
                'day_of_week' => 0,
                'registration_type' => 'none',
            ],
            [
                'name' => 'Verse by Verse Bible Study',
                'description' => 'Working through Scripture verse by verse. Every 4th Sunday of the month at 8:15 AM.',
                'type' => 'service',
                'service_type' => 'MidWeek',
                'start_date' => '2026-07-26 08:15:00',
                'end_date' => '2026-07-26 09:45:00',
                'location' => self::VENUE,
                'frequency' => 'monthly',
                'is_recurring' => true,
                'day_of_week' => 0,
                'registration_type' => 'none',
            ],
            [
                'name' => 'Jesus Is King Walk',
                'description' => 'Who is excited to make heaven rejoice by bringing souls to the glorious light? The Jesus Is King Walk is here again, and we are all expected to partake. Let’s reach our city together.',
                'type' => 'outreach',
                'service_type' => 'Evangelism (Beautiful Feet)',
                'start_date' => '2026-07-25 08:00:00',
                'end_date' => '2026-07-25 11:00:00',
                'location' => 'Meeting point: Novare Mall, Shoprite',
                'frequency' => 'once',
                'is_recurring' => false,
                'registration_type' => 'simple',
            ],
            [
                'name' => 'Singles Café Beach Hangout',
                'description' => 'Relax, connect, have fun. Immediately after Second Service. Bring a snack to share, a blanket or mat, board games, good vibes and an open heart. Directions shared in the group chat.',
                'type' => 'social',
                'service_type' => 'other',
                'start_date' => '2026-07-19 16:00:00',
                'end_date' => '2026-07-19 18:00:00',
                'location' => 'Beach — directions shared in the group chat',
                'frequency' => 'once',
                'is_recurring' => false,
                'registration_type' => 'simple',
            ],
            [
                'name' => 'Unfiltered Thursday: Holding it together is getting exhausting',
                'description' => 'An honest X Space conversation with Funmto Ogunbanwo, Clinical Mental Health Therapist. Real, raw, and deeply needed. Submit your questions before the session.',
                'type' => 'other',
                'service_type' => 'other',
                'start_date' => '2026-07-30 20:30:00',
                'end_date' => '2026-07-30 22:00:00',
                'location' => 'Online — LifePointe GL X Spaces',
                'is_online' => true,
                'online_platform' => 'X Spaces',
                'frequency' => 'once',
                'is_recurring' => false,
                'registration_type' => 'none',
            ],
            [
                'name' => 'Know Your Church (KYC)',
                'description' => 'Discover who we are. Find where you belong. Are you a guest or volunteer looking to become a registered member of this community? Attend a KYC session. Every 3rd Sunday of the month.',
                'type' => 'workshop',
                'service_type' => 'Membership Class',
                // The 3rd Sunday of July has passed; next occurrence is August.
                'start_date' => '2026-08-16 09:00:00',
                'end_date' => '2026-08-16 10:30:00',
                'location' => self::VENUE,
                'frequency' => 'monthly',
                'is_recurring' => true,
                'day_of_week' => 0,
                'registration_type' => 'simple',
            ],
            [
                'name' => 'MidDay Declaration',
                'description' => 'Every Wednesday we observe Midweek Fasting all through the day, and the LifePointe family gathers for MidDay Declaration from 1:00 PM to 1:30 PM.',
                'type' => 'service',
                'service_type' => 'MidWeek',
                'start_date' => '2026-07-22 13:00:00',
                'end_date' => '2026-07-22 13:30:00',
                'location' => 'Online — Zoom',
                'is_online' => true,
                'online_platform' => 'Zoom',
                'online_url' => 'https://elevationng-org.zoom.us/j/82386028140',
                'online_passcode' => 'pray',
                'frequency' => 'weekly',
                'is_recurring' => true,
                'day_of_week' => 3,
                'registration_type' => 'none',
            ],
            [
                'name' => 'Midweek Service — 6:18',
                'description' => '6:18! 6:18!! 6:18!!! Our midweek service holds online every Wednesday at 6:18 PM via the LifePointe YouTube page. The link is shared every Wednesday on the Light Up group.',
                'type' => 'service',
                'service_type' => 'MidWeek',
                'start_date' => '2026-07-22 18:18:00',
                'end_date' => '2026-07-22 19:30:00',
                'location' => 'Online — LifePointe YouTube',
                'is_online' => true,
                'online_platform' => 'YouTube',
                'online_url' => 'https://www.youtube.com/@TheLifePointeChurch/streams',
                'frequency' => 'weekly',
                'is_recurring' => true,
                'day_of_week' => 3,
                'registration_type' => 'none',
            ],
            [
                'name' => 'Friday Night Prayer',
                'description' => 'We meet every Friday at 9:00 PM to pray. If you have a prayer request, send it in — we are standing with you.',
                'type' => 'service',
                'service_type' => 'MidWeek',
                'start_date' => '2026-07-24 21:00:00',
                'end_date' => '2026-07-24 22:30:00',
                'location' => 'Online — Google Meet',
                'is_online' => true,
                'online_platform' => 'Google Meet',
                'online_url' => 'https://meet.google.com/umi-ddza-hng',
                'frequency' => 'weekly',
                'is_recurring' => true,
                'day_of_week' => 5,
                'registration_type' => 'none',
            ],
            [
                'name' => 'Marriage Preparatory Course',
                'description' => 'For engaged and intending couples, run by The Elevation Church. 14 weeks, online via Zoom. This is the last cohort for 2026 — pre-registration closes Monday, 27 July 2026.',
                'type' => 'workshop',
                'service_type' => 'other',
                'start_date' => '2026-08-27 18:00:00',
                'end_date' => '2026-08-27 20:00:00',
                'location' => 'Online via Zoom',
                'is_online' => true,
                'online_platform' => 'Zoom',
                'frequency' => 'once',
                'is_recurring' => false,
                'registration_type' => 'link',
                'registration_link' => 'https://bit.ly/tecpremarriagecourse',
            ],
        ];
    }
}
