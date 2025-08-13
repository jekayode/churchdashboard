<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\User;
use App\Models\Branch;
use App\Models\Event;
use App\Models\Member;
use App\Models\Ministry;
use App\Models\Department;
use App\Models\SmallGroup;
use App\Models\Projection;
use App\Policies\UserPolicy;
use App\Policies\BranchPolicy;
use App\Policies\EventPolicy;
use App\Policies\MemberPolicy;
use App\Policies\MinistryPolicy;
use App\Policies\DepartmentPolicy;
use App\Policies\SmallGroupPolicy;
use App\Policies\ProjectionPolicy;

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
