<?php

declare(strict_types=1);

namespace App\Http\Controllers\Builders\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Builders\UpdateBuilderSettingsRequest;
use App\Models\BuilderResource;
use App\Models\BuilderSetting;
use Illuminate\Http\JsonResponse;

final class BuilderSettingsController extends Controller
{
    public function show(): JsonResponse
    {
        $settings = BuilderSetting::instance();
        $resources = BuilderResource::query()->orderBy('sort_order')->get();

        return response()->json([
            'success' => true,
            'data' => [
                'settings' => $settings,
                'resources' => $resources,
            ],
        ]);
    }

    public function update(UpdateBuilderSettingsRequest $request): JsonResponse
    {
        $settings = BuilderSetting::instance();
        $settings->update($request->validated());

        return response()->json([
            'success' => true,
            'data' => $settings->fresh(),
            'message' => 'Settings updated.',
        ]);
    }
}
