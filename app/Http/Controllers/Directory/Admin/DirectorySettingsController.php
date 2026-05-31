<?php

declare(strict_types=1);

namespace App\Http\Controllers\Directory\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Directory\UpdateDirectorySettingsRequest;
use App\Models\DirectorySetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;

final class DirectorySettingsController extends Controller
{
    public function show(): JsonResponse
    {
        $settings = DirectorySetting::instance();

        return response()->json([
            'success' => true,
            'data' => array_merge($settings->toArray(), [
                'logo_url' => $settings->logo_url,
            ]),
        ]);
    }

    public function update(UpdateDirectorySettingsRequest $request): JsonResponse
    {
        $settings = DirectorySetting::instance();
        Gate::authorize('update', $settings);

        $data = $request->validated();

        if ($request->hasFile('logo')) {
            if ($settings->logo_path) {
                Storage::disk('public')->delete($settings->logo_path);
            }
            $data['logo_path'] = $request->file('logo')->store('directory', 'public');
        }

        unset($data['logo']);
        $settings->update($data);

        return response()->json([
            'success' => true,
            'data' => array_merge($settings->fresh()->toArray(), ['logo_url' => $settings->logo_url]),
            'message' => 'Directory settings updated.',
        ]);
    }
}
