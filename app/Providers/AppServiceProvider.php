<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models\Branch;
use App\Models\Business;
use App\Models\Department;
use App\Models\DirectoryCategory;
use App\Models\DirectorySetting;
use App\Models\Event;
use App\Models\Member;
use App\Models\Ministry;
use App\Models\Projection;
use App\Models\SmallGroup;
use App\Models\User;
use App\Policies\BranchPolicy;
use App\Policies\BusinessMessagePolicy;
use App\Policies\BusinessPolicy;
use App\Policies\BusinessReviewPolicy;
use App\Policies\DepartmentPolicy;
use App\Policies\DirectoryCategoryPolicy;
use App\Policies\DirectorySettingPolicy;
use App\Policies\EventPolicy;
use App\Policies\MemberPolicy;
use App\Policies\MinistryPolicy;
use App\Policies\ProjectionPolicy;
use App\Policies\SmallGroupPolicy;
use App\Policies\UserPolicy;
use App\Support\PermissionCatalog;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

final class AppServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     */
    protected array $policies = [
        User::class => UserPolicy::class,
        Branch::class => BranchPolicy::class,
        Event::class => EventPolicy::class,
        Member::class => MemberPolicy::class,
        Ministry::class => MinistryPolicy::class,
        Department::class => DepartmentPolicy::class,
        SmallGroup::class => SmallGroupPolicy::class,
        Projection::class => ProjectionPolicy::class,
        Business::class => BusinessPolicy::class,
        DirectoryCategory::class => DirectoryCategoryPolicy::class,
        DirectorySetting::class => DirectorySettingPolicy::class,
        \App\Models\BusinessReview::class => BusinessReviewPolicy::class,
    ];

    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        Gate::before(function (User $user, string $ability): ?bool {
            if ($user->isSuperAdmin()) {
                return true;
            }

            if (PermissionCatalog::isPermissionAbility($ability)) {
                return $user->hasPermission($ability, $user->getActiveBranchId());
            }

            return null;
        });

        Gate::define('directoryAdmin', fn (User $user) => $user->isDirectoryAdmin());
        Gate::define('manage-builders', fn (User $user) => $user->canManageBuilders());
        Gate::define('sendBusinessMessage', fn (User $user, Business $business) => (new BusinessMessagePolicy)->create($user, $business));
        Gate::define('replyToBusinessMessage', fn (User $user, Business $business, ?string $threadId = null) => (new BusinessMessagePolicy)->reply($user, $business, $threadId));
        Gate::define('viewBusinessMessageThread', fn (User $user, string $threadId) => (new BusinessMessagePolicy)->viewThread($user, $threadId));
    }

    /**
     * Register the application's policies.
     */
    public function registerPolicies(): void
    {
        foreach ($this->policies as $model => $policy) {
            Gate::policy($model, $policy);
        }
    }
}
