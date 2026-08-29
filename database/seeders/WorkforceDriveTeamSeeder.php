<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\WorkforceDriveTeam;
use Illuminate\Database\Seeder;

final class WorkforceDriveTeamSeeder extends Seeder
{
    /**
     * Seed the Workforce Drive teams.
     *
     * Keyed on name so re-running is safe. Colours are LifePointe-palette hues
     * used to tint each leaderboard card; edit these (and add teams) freely.
     */
    public function run(): void
    {
        // Each unit's sponsor is the person championing it, and the core value
        // is the value that person represents.
        $teams = [
            ['name' => 'Facility', 'core_value' => 'Integrity', 'sponsor_name' => 'Galvin', 'color' => '#DD5D20'],
            ['name' => 'Leadership Development', 'core_value' => 'Service', 'sponsor_name' => 'Victor Unachukwu', 'color' => '#F79000'],
            ['name' => 'Membership Care', 'core_value' => 'Love', 'sponsor_name' => 'Aanu-Faith Jerome', 'color' => '#16A34A'],
            ['name' => 'Graphics Design', 'core_value' => 'Excellence', 'sponsor_name' => 'Godshield', 'color' => '#6B3FA0'],
            ['name' => 'Counseling', 'core_value' => 'Humility', 'sponsor_name' => 'Tolani', 'color' => '#2563EB'],
            ['name' => 'Outreaches (Missions)', 'core_value' => 'Accountability', 'sponsor_name' => 'Jennifer Eyaefe', 'color' => '#D5341A'],
        ];

        foreach ($teams as $index => $team) {
            WorkforceDriveTeam::updateOrCreate(
                ['name' => $team['name']],
                [
                    'core_value' => $team['core_value'],
                    'sponsor_name' => $team['sponsor_name'],
                    'color' => $team['color'],
                    'status' => 'active',
                    'sort_order' => $index,
                ]
            );
        }
    }
}
