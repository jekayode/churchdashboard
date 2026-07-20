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
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
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
        \App\Models\Sermon::class => \App\Policies\SermonPolicy::class,
        \App\Models\Series::class => \App\Policies\SeriesPolicy::class,
        \App\Models\ReadingPlan::class => \App\Policies\ReadingPlanPolicy::class,
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
        $this->defineQuizRateLimits();

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

    /*
     * Quiz limits are keyed on the phone, not the network.
     *
     * A congregation is behind one NATed wifi address, so per-IP limits count
     * the whole room as a single visitor: at the defaults, the twenty-first
     * person to join was refused, and with a hundred people answering every
     * twenty-five seconds most answers would have been rejected too. It fails
     * for everyone at once, in the middle of a service, and looks like the
     * quiz is broken rather than like a limit being hit.
     *
     * So the device token is the key wherever there is one, giving each phone
     * its own allowance. Joining has no token yet, so it stays on the address
     * with a ceiling set for a full room rather than for one person.
     */
    private function defineQuizRateLimits(): void
    {
        $perDevice = static function (Request $request, int $perMinute, int $perAddress): Limit {
            $token = (string) ($request->input('device_token') ?? $request->query('device_token') ?? '');

            return $token !== ''
                ? Limit::perMinute($perMinute)->by('quiz-device:'.sha1($token))
                : Limit::perMinute($perAddress)->by('quiz-ip:'.$request->ip());
        };

        // One person can only join once per quiz, so this is purely a flood
        // guard — sized for a whole church arriving in the same minute.
        RateLimiter::for('quiz-join', static fn (Request $request): Limit => Limit::perMinute(400)
            ->by('quiz-join:'.$request->ip()));

        // A phone polls roughly 40 times a minute; the rest is headroom.
        RateLimiter::for('quiz-state', static fn (Request $request): Limit => $perDevice($request, 90, 6000));

        // Answering twice is already impossible — the unique index sees to that
        // — so this only stops a client hammering the endpoint.
        RateLimiter::for('quiz-answer', static fn (Request $request): Limit => $perDevice($request, 60, 2000));
    }
}
