<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Role;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Role>
 */
final class RoleFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Role::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $roleName = $this->faker->randomElement([
            'super_admin',
            'branch_pastor',
            'ministry_leader',
            'department_leader',
            'church_member',
            'public_user',
        ]);

        return [
            'name' => $roleName,
            'display_name' => ucwords(str_replace('_', ' ', $roleName)),
            'description' => $this->faker->sentence(),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    /**
     * Indicate that the role is a super admin.
     */
    public function superAdmin(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'super_admin',
            'display_name' => 'Super Admin',
            'description' => 'Super Administrator with full system access',
        ]);
    }

    /**
     * Indicate that the role is a branch pastor.
     */
    public function branchPastor(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'branch_pastor',
            'display_name' => 'Branch Pastor',
            'description' => 'Branch Pastor with administrative access to their branch',
        ]);
    }

    /**
     * Indicate that the role is a ministry leader.
     */
    public function ministryLeader(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'ministry_leader',
            'display_name' => 'Ministry Leader',
            'description' => 'Ministry Leader with access to their ministry',
        ]);
    }

    /**
     * Indicate that the role is a department leader.
     */
    public function departmentLeader(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'department_leader',
            'display_name' => 'Department Leader',
            'description' => 'Department Leader with access to their department',
        ]);
    }

    /**
     * Indicate that the role is a church member.
     */
    public function churchMember(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'church_member',
            'display_name' => 'Church Member',
            'description' => 'Church Member with basic access',
        ]);
    }

    /**
     * Indicate that the role is a public user.
     */
    public function publicUser(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'public_user',
            'display_name' => 'Public User',
            'description' => 'Public User with limited access',
        ]);
    }
} 