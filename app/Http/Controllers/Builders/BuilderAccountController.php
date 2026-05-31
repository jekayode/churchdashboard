<?php

declare(strict_types=1);

namespace App\Http\Controllers\Builders;

use App\Http\Controllers\Controller;
use App\Models\BuilderRegistration;
use App\Models\BuilderResource;
use App\Models\BuilderSetting;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class BuilderAccountController extends Controller
{
    public function index(): View|RedirectResponse
    {
        $user = auth()->user();

        if (! $user->hasVerifiedEmail()) {
            return redirect()->route('builders.thank-you')
                ->with('status', 'Please verify your email using the link we sent you before accessing your pack.');
        }

        $registration = BuilderRegistration::query()
            ->where('user_id', $user->id)
            ->orWhere('email', $user->email)
            ->first();

        if (! $registration) {
            return redirect()->route('builders.create')
                ->with('status', 'Please complete the registration form first.');
        }

        $settings = BuilderSetting::instance();
        $resources = BuilderResource::query()->orderBy('sort_order')->get();

        return view('builders.account', compact('registration', 'settings', 'resources'));
    }

    public function download(BuilderResource $resource): StreamedResponse|RedirectResponse
    {
        $user = auth()->user();

        if (! $user->hasVerifiedEmail()) {
            abort(403, 'Please verify your email first.');
        }

        $hasRegistration = BuilderRegistration::query()
            ->where(function ($query) use ($user): void {
                $query->where('user_id', $user->id)
                    ->orWhere('email', $user->email);
            })
            ->exists();

        if (! $hasRegistration) {
            abort(403);
        }

        if (! Storage::disk('public')->exists($resource->file_path)) {
            abort(404);
        }

        return Storage::disk('public')->download(
            $resource->file_path,
            $resource->original_name
        );
    }
}
