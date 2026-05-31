<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use Illuminate\Http\JsonResponse;

final class PermissionController extends Controller
{
    public function index(): JsonResponse
    {
        $grouped = Permission::query()
            ->orderBy('group')
            ->orderBy('name')
            ->get()
            ->groupBy('group')
            ->map(fn ($items, $group) => [
                'group' => $group,
                'permissions' => $items->values(),
            ])
            ->values();

        return response()->json([
            'success' => true,
            'data' => $grouped,
        ]);
    }
}
