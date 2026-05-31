<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

final class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        $allIds = Permission::query()->pluck('id', 'name');

        $this->syncRole('super_admin', $allIds->keys()->all(), $allIds);

        $branchPastor = [
            'branches.view', 'branches.update', 'branches.manage_pastors', 'branches.manage_settings',
            'members.view', 'members.create', 'members.update', 'members.delete', 'members.update_growth', 'members.update_teci', 'members.change_status',
            'guests.view', 'guests.update_status', 'guests.add_follow_up', 'guests.import', 'guests.export', 'guests.send_setup_emails',
            'ministries.view', 'ministries.create', 'ministries.update', 'ministries.delete', 'ministries.assign_leader',
            'departments.view', 'departments.create', 'departments.update', 'departments.delete', 'departments.assign_leader', 'departments.manage_members',
            'small_groups.view', 'small_groups.create', 'small_groups.update', 'small_groups.delete', 'small_groups.manage_members', 'small_groups.change_leader',
            'small_group_reports.view', 'small_group_reports.create', 'small_group_reports.update', 'small_group_reports.approve', 'small_group_reports.reject',
            'events.view', 'events.create', 'events.update', 'events.delete', 'events.view_registrations', 'events.check_in',
            'reports.view', 'reports.create', 'reports.update', 'reports.export', 'reports.manage_tokens',
            'projections.view', 'projections.create', 'projections.update', 'projections.submit', 'projections.approve', 'projections.reject', 'projections.set_current_year',
            'performance.view_branch', 'performance.view_network',
            'import_export.import', 'import_export.export', 'import_export.templates',
            'communication.view_settings', 'communication.manage_settings', 'communication.templates_view', 'communication.templates_manage',
            'communication.campaigns_view', 'communication.campaigns_manage', 'communication.logs_view', 'communication.quick_send', 'communication.mass_send',
            'directory.view', 'directory.admin_moderate', 'directory.admin_categories', 'directory.admin_settings', 'directory.admin_reviews',
            'builders.view', 'builders.admin_registrations', 'builders.admin_settings', 'builders.admin_resources',
            'users.view', 'users.assign_role', 'users.impersonate',
            'roles.view',
        ];

        $this->syncRole('branch_pastor', $branchPastor, $allIds);

        $ministryLeader = [
            'ministries.view', 'ministries.update', 'ministries.assign_leader',
            'departments.view', 'departments.create', 'departments.update', 'departments.assign_leader', 'departments.manage_members',
            'members.view', 'members.update',
            'guests.view', 'guests.update_status', 'guests.add_follow_up', 'guests.export',
            'small_groups.view', 'small_groups.create', 'small_groups.update', 'small_groups.manage_members',
            'small_group_reports.view', 'small_group_reports.create', 'small_group_reports.update',
            'events.view', 'events.create', 'events.update', 'events.delete', 'events.view_registrations',
            'reports.view', 'reports.create',
            'communication.view_settings', 'communication.quick_send',
            'directory.view', 'directory.own_business',
        ];

        $this->syncRole('ministry_leader', $ministryLeader, $allIds);

        $departmentLeader = [
            'departments.view', 'departments.update', 'departments.manage_members',
            'members.view', 'members.update',
            'small_groups.view',
            'events.view', 'events.register',
            'directory.view', 'directory.own_business',
        ];

        $this->syncRole('department_leader', $departmentLeader, $allIds);

        $churchMember = [
            'members.view',
            'events.view', 'events.register',
            'small_groups.view',
            'directory.view', 'directory.own_business',
            'builders.view',
        ];

        $this->syncRole('church_member', $churchMember, $allIds);

        $directoryAdmin = [
            'directory.view', 'directory.admin_moderate', 'directory.admin_categories', 'directory.admin_settings', 'directory.admin_reviews',
            'directory.own_business',
            'builders.view', 'builders.admin_registrations', 'builders.admin_settings', 'builders.admin_resources',
        ];

        $this->syncRole('directory_admin', $directoryAdmin, $allIds);

        $businessCareLeader = [
            'builders.view', 'builders.admin_registrations', 'builders.admin_settings', 'builders.admin_resources',
            'members.view',
        ];

        $this->syncRole('business_care_leader', $businessCareLeader, $allIds);

        $publicUser = [
            'events.view', 'events.register',
            'directory.view',
            'builders.view',
        ];

        $this->syncRole('public_user', $publicUser, $allIds);

        $this->command?->info('Role permissions seeded.');
    }

    /**
     * @param  list<string>  $permissionNames
     * @param  \Illuminate\Support\Collection<string, int>  $allIds
     */
    private function syncRole(string $roleName, array $permissionNames, $allIds): void
    {
        $role = Role::query()->where('name', $roleName)->first();

        if (! $role) {
            return;
        }

        $ids = collect($permissionNames)
            ->map(fn (string $name) => $allIds->get($name))
            ->filter()
            ->values()
            ->all();

        $role->syncPermissions($ids);
    }
}
