<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Member;
use App\Models\User;

final class MemberPolicy extends BasePolicy
{
    /**
     * Determine whether the user can view any members.
     */
    public function viewAny(User $user): bool
    {
        // Users with leadership privileges can view member lists
        return $this->hasLeadershipPrivileges($user);
    }

    /**
     * Determine whether the user can view the member.
     */
    public function view(User $user, mixed $member): bool
    {
        // Users can view their own member profile
        if ($user->member && $user->member->id === $member->id) {
            return true;
        }

        // Users with leadership privileges can view members in their branch
        if ($this->hasLeadershipPrivileges($user)) {
            return $this->belongsToSameBranch($user, $member);
        }

        return false;
    }

    /**
     * Determine whether the user can create members.
     */
    public function create(User $user): bool
    {
        // Users with administrative privileges can create member records
        return $this->hasAdminPrivileges($user);
    }

    /**
     * Determine whether the user can update the member.
     */
    public function update(User $user, mixed $member): bool
    {
        // Users can update their own member profile
        if ($user->member && $user->member->id === $member->id) {
            return true;
        }

        // Users with leadership privileges can update members in their branch
        if ($this->hasLeadershipPrivileges($user)) {
            return $this->belongsToSameBranch($user, $member);
        }

        return false;
    }

    /**
     * Determine whether the user can delete the member.
     */
    public function delete(User $user, mixed $member): bool
    {
        // Users cannot delete their own member profile
        if ($user->member && $user->member->id === $member->id) {
            return false;
        }

        // Only users with admin privileges can delete members
        return $this->hasAdminPrivileges($user) && $this->belongsToSameBranch($user, $member);
    }

    /**
     * Determine whether the user can update member growth levels.
     */
    public function updateGrowthLevel(User $user, Member $member): bool
    {
        // Users with leadership privileges can update growth levels
        return $this->hasLeadershipPrivileges($user) && $this->belongsToSameBranch($user, $member);
    }

    /**
     * Determine whether the user can update TECI progress.
     */
    public function updateTeciProgress(User $user, Member $member): bool
    {
        // Users with leadership privileges can update TECI progress
        return $this->hasLeadershipPrivileges($user) && $this->belongsToSameBranch($user, $member);
    }

    /**
     * Determine whether the user can view member reports.
     */
    public function viewReports(User $user, Member $member): bool
    {
        // Users can view their own reports
        if ($user->member && $user->member->id === $member->id) {
            return true;
        }

        // Users with leadership privileges can view member reports
        return $this->hasLeadershipPrivileges($user) && $this->belongsToSameBranch($user, $member);
    }

    /**
     * Determine whether the user can view any guests.
     * Override for guest management access.
     */
    public function viewAnyGuests(User $user): bool
    {
        // #region agent log
        try {
            file_put_contents('/Users/emmanuel/Herd/churchdashboard/.cursor/debug.log', json_encode([
                'sessionId' => 'debug-session',
                'runId' => 'pre-fix',
                'hypothesisId' => 'C',
                'location' => 'app/Policies/MemberPolicy.php:viewAnyGuests:entry',
                'message' => 'MemberPolicy::viewAnyGuests (entry)',
                'data' => [
                    'user_id' => $user->id,
                    'is_super_admin' => $user->isSuperAdmin(),
                    'is_branch_pastor' => $user->isBranchPastor(),
                    'is_ministry_leader' => $user->isMinistryLeader(),
                    'is_department_leader' => $user->isDepartmentLeader(),
                    'has_member_profile' => (bool) $user->member,
                ],
                'timestamp' => (int) round(microtime(true) * 1000),
            ], JSON_UNESCAPED_SLASHES).PHP_EOL, FILE_APPEND);
        } catch (\Throwable) {
        }
        // #endregion

        // Super Admins have full access
        if ($this->isSuperAdmin($user)) {
            return true;
        }

        // Branch Pastors have access
        if ($this->isBranchPastor($user)) {
            return true;
        }

        // Eligible Ministry Leaders have access
        $eligible = $this->isEligibleMinistryLeader($user);

        // #region agent log
        try {
            file_put_contents('/Users/emmanuel/Herd/churchdashboard/.cursor/debug.log', json_encode([
                'sessionId' => 'debug-session',
                'runId' => 'pre-fix',
                'hypothesisId' => 'C',
                'location' => 'app/Policies/MemberPolicy.php:viewAnyGuests:result',
                'message' => 'MemberPolicy::viewAnyGuests result (eligible ministry leader)',
                'data' => [
                    'user_id' => $user->id,
                    'eligible_ministry_leader' => $eligible,
                ],
                'timestamp' => (int) round(microtime(true) * 1000),
            ], JSON_UNESCAPED_SLASHES).PHP_EOL, FILE_APPEND);
        } catch (\Throwable) {
        }
        // #endregion

        return $eligible;
    }

    /**
     * Determine whether the user can view a guest.
     * Override for guest management access.
     */
    public function viewGuest(User $user, Member $member): bool
    {
        // Must be a guest (visitor status with guest-form registration source)
        if ($member->member_status !== 'visitor' || $member->registration_source !== 'guest-form') {
            return false;
        }

        // Super Admins have full access
        if ($this->isSuperAdmin($user)) {
            return true;
        }

        // Branch Pastors can view guests in their branch(es)
        if ($this->isBranchPastor($user)) {
            return $this->belongsToSameBranch($user, $member);
        }

        // Eligible Ministry Leaders can view guests in their ministry's branch
        if ($this->isEligibleMinistryLeader($user)) {
            return $this->belongsToSameBranch($user, $member);
        }

        return false;
    }

    /**
     * Check if user is an eligible Ministry Leader (leads ministries matching criteria).
     */
    protected function isEligibleMinistryLeader(User $user): bool
    {
        // User must have a member record to lead ministries
        if (! $user->member) {
            // #region agent log
            try {
                file_put_contents('/Users/emmanuel/Herd/churchdashboard/.cursor/debug.log', json_encode([
                    'sessionId' => 'debug-session',
                    'runId' => 'pre-fix',
                    'hypothesisId' => 'B',
                    'location' => 'app/Policies/MemberPolicy.php:isEligibleMinistryLeader:no-member',
                    'message' => 'isEligibleMinistryLeader=false (no member profile)',
                    'data' => [
                        'user_id' => $user->id,
                    ],
                    'timestamp' => (int) round(microtime(true) * 1000),
                ], JSON_UNESCAPED_SLASHES).PHP_EOL, FILE_APPEND);
            } catch (\Throwable) {
            }
            // #endregion

            return false;
        }

        // Keywords to match in ministry names (case-insensitive partial match)
        $keywords = [
            'life groups',
            'assimilation',
            'online church',
            'finance',
            'prayers',
        ];

        // Get all ministries led by this user's member
        $ledMinistries = $user->member->ledMinistries()
            ->where('status', 'active')
            ->get();

        // #region agent log
        try {
            $names = $ledMinistries->take(5)->map(fn ($m) => (string) ($m->name ?? ''))->values()->all();
            file_put_contents('/Users/emmanuel/Herd/churchdashboard/.cursor/debug.log', json_encode([
                'sessionId' => 'debug-session',
                'runId' => 'pre-fix',
                'hypothesisId' => 'D',
                'location' => 'app/Policies/MemberPolicy.php:isEligibleMinistryLeader:ministries',
                'message' => 'Loaded led ministries for eligibility check',
                'data' => [
                    'user_id' => $user->id,
                    'member_id' => $user->member->id,
                    'active_led_ministries_count' => $ledMinistries->count(),
                    'sample_ministry_names' => $names,
                ],
                'timestamp' => (int) round(microtime(true) * 1000),
            ], JSON_UNESCAPED_SLASHES).PHP_EOL, FILE_APPEND);
        } catch (\Throwable) {
        }
        // #endregion

        // Check if any ministry name contains any of the keywords
        foreach ($ledMinistries as $ministry) {
            $ministryName = strtolower($ministry->name ?? '');
            foreach ($keywords as $keyword) {
                if (str_contains($ministryName, strtolower($keyword))) {
                    // #region agent log
                    try {
                        file_put_contents('/Users/emmanuel/Herd/churchdashboard/.cursor/debug.log', json_encode([
                            'sessionId' => 'debug-session',
                            'runId' => 'pre-fix',
                            'hypothesisId' => 'D',
                            'location' => 'app/Policies/MemberPolicy.php:isEligibleMinistryLeader:match',
                            'message' => 'Eligible ministry leader keyword match',
                            'data' => [
                                'user_id' => $user->id,
                                'matched_keyword' => (string) $keyword,
                                'ministry_name' => (string) ($ministry->name ?? ''),
                            ],
                            'timestamp' => (int) round(microtime(true) * 1000),
                        ], JSON_UNESCAPED_SLASHES).PHP_EOL, FILE_APPEND);
                    } catch (\Throwable) {
                    }
                    // #endregion

                    return true;
                }
            }
        }

        // #region agent log
        try {
            file_put_contents('/Users/emmanuel/Herd/churchdashboard/.cursor/debug.log', json_encode([
                'sessionId' => 'debug-session',
                'runId' => 'pre-fix',
                'hypothesisId' => 'D',
                'location' => 'app/Policies/MemberPolicy.php:isEligibleMinistryLeader:no-match',
                'message' => 'isEligibleMinistryLeader=false (no keyword match)',
                'data' => [
                    'user_id' => $user->id,
                ],
                'timestamp' => (int) round(microtime(true) * 1000),
            ], JSON_UNESCAPED_SLASHES).PHP_EOL, FILE_APPEND);
        } catch (\Throwable) {
        }
        // #endregion

        return false;
    }
}
