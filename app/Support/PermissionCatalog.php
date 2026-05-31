<?php

declare(strict_types=1);

namespace App\Support;

final class PermissionCatalog
{
    /**
     * @return list<array{name: string, group: string, label: string, description: string|null, is_dangerous: bool}>
     */
    public static function all(): array
    {
        $items = [];

        foreach (self::definitions() as $group => $permissions) {
            foreach ($permissions as $permission) {
                $items[] = [
                    'name' => $group.'.'.$permission['key'],
                    'group' => $group,
                    'label' => $permission['label'],
                    'description' => $permission['description'] ?? null,
                    'is_dangerous' => $permission['dangerous'] ?? false,
                ];
            }
        }

        return $items;
    }

    /**
     * @return list<string>
     */
    public static function names(): array
    {
        return array_column(self::all(), 'name');
    }

    public static function isPermissionAbility(string $ability): bool
    {
        return str_contains($ability, '.') && in_array($ability, self::names(), true);
    }

    /**
     * @return array<string, list<array{key: string, label: string, description: string|null, dangerous: bool}>>
     */
    private static function definitions(): array
    {
        return [
            'branches' => [
                ['key' => 'view', 'label' => 'View branches'],
                ['key' => 'create', 'label' => 'Create branches'],
                ['key' => 'update', 'label' => 'Update branches'],
                ['key' => 'delete', 'label' => 'Delete branches', 'dangerous' => true],
                ['key' => 'manage_pastors', 'label' => 'Assign branch pastors'],
                ['key' => 'manage_settings', 'label' => 'Manage branch settings'],
            ],
            'members' => [
                ['key' => 'view', 'label' => 'View members'],
                ['key' => 'create', 'label' => 'Create members'],
                ['key' => 'update', 'label' => 'Update members'],
                ['key' => 'delete', 'label' => 'Delete members', 'dangerous' => true],
                ['key' => 'update_growth', 'label' => 'Update growth level'],
                ['key' => 'update_teci', 'label' => 'Update TECI progress'],
                ['key' => 'change_status', 'label' => 'Change member status'],
            ],
            'guests' => [
                ['key' => 'view', 'label' => 'View guests'],
                ['key' => 'update_status', 'label' => 'Update guest status'],
                ['key' => 'add_follow_up', 'label' => 'Add guest follow-ups'],
                ['key' => 'import', 'label' => 'Import guests'],
                ['key' => 'export', 'label' => 'Export guests'],
                ['key' => 'send_setup_emails', 'label' => 'Send guest setup emails'],
            ],
            'ministries' => [
                ['key' => 'view', 'label' => 'View ministries'],
                ['key' => 'create', 'label' => 'Create ministries'],
                ['key' => 'update', 'label' => 'Update ministries'],
                ['key' => 'delete', 'label' => 'Delete ministries', 'dangerous' => true],
                ['key' => 'assign_leader', 'label' => 'Assign ministry leaders'],
            ],
            'departments' => [
                ['key' => 'view', 'label' => 'View departments'],
                ['key' => 'create', 'label' => 'Create departments'],
                ['key' => 'update', 'label' => 'Update departments'],
                ['key' => 'delete', 'label' => 'Delete departments', 'dangerous' => true],
                ['key' => 'assign_leader', 'label' => 'Assign department leaders'],
                ['key' => 'manage_members', 'label' => 'Manage department members'],
            ],
            'small_groups' => [
                ['key' => 'view', 'label' => 'View small groups'],
                ['key' => 'create', 'label' => 'Create small groups'],
                ['key' => 'update', 'label' => 'Update small groups'],
                ['key' => 'delete', 'label' => 'Delete small groups', 'dangerous' => true],
                ['key' => 'manage_members', 'label' => 'Manage group members'],
                ['key' => 'change_leader', 'label' => 'Change group leader'],
            ],
            'small_group_reports' => [
                ['key' => 'view', 'label' => 'View meeting reports'],
                ['key' => 'create', 'label' => 'Create meeting reports'],
                ['key' => 'update', 'label' => 'Update meeting reports'],
                ['key' => 'delete', 'label' => 'Delete meeting reports', 'dangerous' => true],
                ['key' => 'approve', 'label' => 'Approve meeting reports'],
                ['key' => 'reject', 'label' => 'Reject meeting reports'],
            ],
            'events' => [
                ['key' => 'view', 'label' => 'View events'],
                ['key' => 'create', 'label' => 'Create events'],
                ['key' => 'update', 'label' => 'Update events'],
                ['key' => 'delete', 'label' => 'Delete events', 'dangerous' => true],
                ['key' => 'register', 'label' => 'Register for events'],
                ['key' => 'check_in', 'label' => 'Check in attendees'],
                ['key' => 'view_registrations', 'label' => 'View registrations'],
            ],
            'reports' => [
                ['key' => 'view', 'label' => 'View reports'],
                ['key' => 'create', 'label' => 'Create reports'],
                ['key' => 'update', 'label' => 'Update reports'],
                ['key' => 'delete', 'label' => 'Delete reports', 'dangerous' => true],
                ['key' => 'export', 'label' => 'Export reports'],
                ['key' => 'view_all_branches', 'label' => 'View all branches reports'],
                ['key' => 'manage_tokens', 'label' => 'Manage public report tokens'],
            ],
            'projections' => [
                ['key' => 'view', 'label' => 'View projections'],
                ['key' => 'create', 'label' => 'Create projections'],
                ['key' => 'update', 'label' => 'Update projections'],
                ['key' => 'delete', 'label' => 'Delete projections', 'dangerous' => true],
                ['key' => 'submit', 'label' => 'Submit projections for review'],
                ['key' => 'approve', 'label' => 'Approve projections'],
                ['key' => 'reject', 'label' => 'Reject projections'],
                ['key' => 'set_current_year', 'label' => 'Set current projection year'],
            ],
            'performance' => [
                ['key' => 'view_branch', 'label' => 'View branch performance'],
                ['key' => 'view_network', 'label' => 'View network performance'],
            ],
            'import_export' => [
                ['key' => 'import', 'label' => 'Import data'],
                ['key' => 'export', 'label' => 'Export data'],
                ['key' => 'templates', 'label' => 'Download import templates'],
            ],
            'communication' => [
                ['key' => 'view_settings', 'label' => 'View communication settings'],
                ['key' => 'manage_settings', 'label' => 'Manage communication settings'],
                ['key' => 'templates_view', 'label' => 'View message templates'],
                ['key' => 'templates_manage', 'label' => 'Manage message templates'],
                ['key' => 'campaigns_view', 'label' => 'View email campaigns'],
                ['key' => 'campaigns_manage', 'label' => 'Manage email campaigns'],
                ['key' => 'logs_view', 'label' => 'View communication logs'],
                ['key' => 'quick_send', 'label' => 'Quick send messages'],
                ['key' => 'mass_send', 'label' => 'Mass send messages'],
            ],
            'directory' => [
                ['key' => 'view', 'label' => 'View business directory'],
                ['key' => 'own_business', 'label' => 'Manage own businesses'],
                ['key' => 'admin_moderate', 'label' => 'Moderate directory listings'],
                ['key' => 'admin_categories', 'label' => 'Manage directory categories'],
                ['key' => 'admin_settings', 'label' => 'Manage directory settings'],
                ['key' => 'admin_reviews', 'label' => 'Moderate reviews'],
            ],
            'builders' => [
                ['key' => 'view', 'label' => 'View builders program'],
                ['key' => 'admin_registrations', 'label' => 'Manage builder registrations'],
                ['key' => 'admin_settings', 'label' => 'Manage builders settings'],
                ['key' => 'admin_resources', 'label' => 'Manage starter pack files'],
            ],
            'users' => [
                ['key' => 'view', 'label' => 'View users'],
                ['key' => 'assign_role', 'label' => 'Assign roles to users'],
                ['key' => 'impersonate', 'label' => 'Impersonate users', 'dangerous' => true],
            ],
            'roles' => [
                ['key' => 'view', 'label' => 'View roles'],
                ['key' => 'manage', 'label' => 'Manage roles and permissions'],
            ],
        ];
    }
}
