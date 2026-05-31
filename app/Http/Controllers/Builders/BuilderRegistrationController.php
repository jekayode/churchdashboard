<?php

declare(strict_types=1);

namespace App\Http\Controllers\Builders;

use App\Enums\BuilderIndustry;
use App\Enums\BusinessChallenge;
use App\Enums\BusinessStage;
use App\Enums\CacStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Builders\StoreBuilderRegistrationRequest;
use App\Models\BuilderResource;
use App\Models\BuilderSetting;
use App\Services\BuilderRegistrationService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

final class BuilderRegistrationController extends Controller
{
    public function __construct(
        private readonly BuilderRegistrationService $registrationService
    ) {}

    public function create(): View
    {
        $settings = BuilderSetting::instance();
        $stages = BusinessStage::cases();
        $challenges = BusinessChallenge::cases();
        $industries = BuilderIndustry::cases();
        $cacStatuses = CacStatus::cases();

        return view('builders.form', compact('settings', 'stages', 'challenges', 'industries', 'cacStatuses'));
    }

    public function store(StoreBuilderRegistrationRequest $request): RedirectResponse
    {
        $result = $this->registrationService->register($request->validated());

        return redirect()
            ->route('builders.thank-you')
            ->with('builders_is_new_user', $result['is_new_user'])
            ->with('builders_email', $request->validated('email'));
    }

    public function thankYou(Request $request): View
    {
        $settings = BuilderSetting::instance();
        $resources = BuilderResource::query()->orderBy('sort_order')->get();
        $isNewUser = (bool) $request->session()->get('builders_is_new_user', false);

        return view('builders.thank-you', compact('settings', 'resources', 'isNewUser'));
    }
}
