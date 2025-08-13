<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Event;
use App\Models\Branch;
use App\Models\User;
use Carbon\Carbon;

final class ChurchServiceSeeder extends Seeder
{
    public function run(): void
    {
        // Get branches and super admin user
        $branches = Branch::all();
        $superAdmin = User::whereHas('roles', function ($query) {
            $query->where('name', 'super_admin');
        })->first();
        
        if (!$superAdmin) {
            $this->command->error('No super admin user found. Please create a super admin user first.');
            return;
        }

        // Branch service configurations
        $branchConfigs = [
            'LifePointe Lekki' => [
                'venue' => 'Pistis Annex',
                'address' => '3 Remi Olowude St, Eti-Osa 105102, Lagos',
                'services' => [
                    [
                        'name' => 'Sunday Service',
                        'service_type' => 'Sunday Service',
                        'day_of_week' => 0, // Sunday
                        'service_time' => '10:00',
                        'service_end_time' => '12:30',
                        'service_name' => 'Sunday Service',
                        'has_multiple_services' => false,
                    ],
                    [
                        'name' => 'MidWeek Service',
                        'service_type' => 'MidWeek',
                        'day_of_week' => 3, // Wednesday
                        'service_time' => '18:18',
                        'service_end_time' => '20:00',
                        'service_name' => 'MidWeek Service',
                        'has_multiple_services' => false,
                    ]
                ]
            ],
            'LifePointe Yaba' => [
                'venue' => 'Chapel Street',
                'address' => '2/8 Chapel Street, Sabo yaba, Lagos, Nigeria',
                'services' => [
                    [
                        'name' => 'Sunday Service',
                        'service_type' => 'Sunday Service',
                        'day_of_week' => 0, // Sunday
                        'service_time' => '10:00',
                        'service_end_time' => '12:30',
                        'service_name' => 'Sunday Service',
                        'has_multiple_services' => false,
                    ],
                    [
                        'name' => 'MidWeek Service',
                        'service_type' => 'MidWeek',
                        'day_of_week' => 3, // Wednesday
                        'service_time' => '18:18',
                        'service_end_time' => '20:00',
                        'service_name' => 'MidWeek Service',
                        'has_multiple_services' => false,
                    ]
                ]
            ],
            'LifePointe Ojo' => [
                'venue' => 'Choice and Choices Event Centre',
                'address' => 'Choice and Choices Event Centre, Iyana School, Ojo',
                'services' => [
                    [
                        'name' => 'Sunday Service',
                        'service_type' => 'Sunday Service',
                        'day_of_week' => 0, // Sunday
                        'service_time' => '10:00',
                        'service_end_time' => '12:30',
                        'service_name' => 'Sunday Service',
                        'has_multiple_services' => false,
                    ]
                ]
            ],
            'LifePointe Greater Lekki' => [
                'venue' => 'Nova Cinema',
                'address' => 'Screen 4, Nova Cinema, Novare Mall, Sangotedo, Lagos',
                'services' => [
                    [
                        'name' => 'Sunday Service',
                        'service_type' => 'Sunday Service',
                        'day_of_week' => 0, // Sunday
                        'service_time' => '08:15',
                        'service_end_time' => '09:55',
                        'service_name' => 'First Service',
                        'has_multiple_services' => true,
                        'second_service_time' => '10:00',
                        'second_service_end_time' => '12:00',
                        'second_service_name' => 'Second Service',
                    ]
                ]
            ]
        ];

        // Create services for each branch
        foreach ($branches as $branch) {
            $config = $branchConfigs[$branch->name] ?? null;
            
            if (!$config) {
                $this->command->warn("No configuration found for branch: {$branch->name}");
                continue;
            }
            
            foreach ($config['services'] as $serviceConfig) {
                $event = Event::create([
                    'name' => $serviceConfig['name'],
                    'description' => "Regular {$serviceConfig['service_type']} service at {$branch->name}",
                    'type' => 'service',
                    'service_type' => $serviceConfig['service_type'],
                    'branch_id' => $branch->id,
                    'venue' => $config['venue'],
                    'address' => $config['address'],
                    'location' => $config['venue'],
                    'day_of_week' => $serviceConfig['day_of_week'],
                    'service_time' => $serviceConfig['service_time'],
                    'service_end_time' => $serviceConfig['service_end_time'],
                    'service_name' => $serviceConfig['service_name'],
                    'has_multiple_services' => $serviceConfig['has_multiple_services'],
                    'second_service_time' => $serviceConfig['second_service_time'] ?? null,
                    'second_service_end_time' => $serviceConfig['second_service_end_time'] ?? null,
                    'second_service_name' => $serviceConfig['second_service_name'] ?? null,
                    'is_recurring' => true,
                    'start_date' => now()->startOfWeek()->addDays($serviceConfig['day_of_week']),
                    'end_date' => null, // Ongoing
                    'max_capacity' => 500, // Default capacity
                    'registration_type' => 'form',
                    'status' => 'active',
                    'is_public' => true,
                ]);
                
                $this->command->info("Created service: {$event->name} for {$branch->name}");
            }
        }

        // Create some additional service types for the first branch
        $firstBranch = $branches->first();
        if ($firstBranch) {
            $additionalServices = [
                [
                    'name' => 'Water Baptism Service',
                    'service_type' => 'Water Baptism',
                    'day_of_week' => 0, // Sunday
                    'service_time' => '15:00',
                    'service_end_time' => '17:00',
                    'is_recurring' => false,
                ],
                [
                    'name' => 'TECi Program',
                    'service_type' => 'TECi',
                    'day_of_week' => 6, // Saturday
                    'service_time' => '09:00',
                    'service_end_time' => '11:00',
                    'is_recurring' => true,
                ],
                [
                    'name' => 'Membership Class',
                    'service_type' => 'Membership Class',
                    'day_of_week' => 6, // Saturday
                    'service_time' => '14:00',
                    'service_end_time' => '16:00',
                    'is_recurring' => true,
                ],
                [
                    'name' => 'Beautiful Feet Evangelism',
                    'service_type' => 'Evangelism (Beautiful Feet)',
                    'day_of_week' => 6, // Saturday
                    'service_time' => '08:00',
                    'service_end_time' => '12:00',
                    'is_recurring' => true,
                ],
                [
                    'name' => 'LifeGroup Meeting',
                    'service_type' => 'LifeGroup Meeting',
                    'day_of_week' => 4, // Thursday
                    'service_time' => '19:00',
                    'service_end_time' => '21:00',
                    'is_recurring' => true,
                ]
            ];

            foreach ($additionalServices as $serviceConfig) {
                $startDate = $serviceConfig['is_recurring'] 
                    ? now()->startOfWeek()->addDays($serviceConfig['day_of_week'])
                    : now()->addMonth();

                Event::create([
                    'name' => $serviceConfig['name'],
                    'description' => "Special {$serviceConfig['service_type']} program at {$firstBranch->name}",
                    'type' => 'service',
                    'service_type' => $serviceConfig['service_type'],
                    'branch_id' => $firstBranch->id,
                    'venue' => 'Pistis Annex',
                    'address' => '3 Remi Olowude St, Eti-Osa 105102, Lagos',
                    'location' => 'Pistis Annex',
                    'day_of_week' => $serviceConfig['day_of_week'],
                    'service_time' => $serviceConfig['service_time'],
                    'service_end_time' => $serviceConfig['service_end_time'],
                    'service_name' => $serviceConfig['name'],
                    'has_multiple_services' => false,
                    'is_recurring' => $serviceConfig['is_recurring'],
                    'start_date' => $startDate,
                    'end_date' => null,
                    'max_capacity' => 100,
                    'registration_type' => 'form',
                    'status' => 'active',
                    'is_public' => true,
                ]);
                
                $this->command->info("Created special service: {$serviceConfig['name']} for {$firstBranch->name}");
            }
        }
        
        $this->command->info('Church services seeded successfully!');
    }
} 