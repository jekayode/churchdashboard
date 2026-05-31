<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreRoleRequest;
use App\Http\Requests\Admin\SyncRolePermissionsRequest;
use App\Http\Requests\Admin\UpdateRoleRequest;
use App\Models\Role;
use Illuminate\Http\JsonResponse;

final class RoleController extends Controller
{
    public function index(): JsonResponse
    {
        $roles = Role::query()
            ->withCount('permissions', 'users')
            ->orderBy('display_name')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $roles,
        ]);
    }

    public function store(StoreRoleRequest $request): JsonResponse
    {
        $role = Role::query()->create($request->validated());

        return response()->json([
            'success' => true,
            'data' => $role,
            'message' => 'Role created.',
        ], 201);
    }

    public function update(UpdateRoleRequest $request, Role $role): JsonResponse
    {
        if ($role->isSystemRole() && $request->has('name') && $request->input('name') !== $role->name) {
            return response()->json([
                'success' => false,
                'message' => 'System role names cannot be changed.',
            ], 422);
        }

        $role->update($request->validated());

        return response()->json([
            'success' => true,
            'data' => $role->fresh(),
            'message' => 'Role updated.',
        ]);
    }

    public function destroy(Role $role): JsonResponse
    {
        if ($role->isSystemRole()) {
            return response()->json([
                'success' => false,
                'message' => 'System roles cannot be deleted.',
            ], 422);
        }

        if ($role->users()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Remove all users from this role before deleting.',
            ], 422);
        }

        $role->delete();

        return response()->json([
            'success' => true,
            'message' => 'Role deleted.',
        ]);
    }

    public function permissions(Role $role): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => [
                'role_id' => $role->id,
                'permission_ids' => $role->permissions()->pluck('permissions.id'),
            ],
        ]);
    }

    public function syncPermissions(SyncRolePermissionsRequest $request, Role $role): JsonResponse
    {
        $role->syncPermissions($request->validated('permissions'));

        return response()->json([
            'success' => true,
            'data' => [
                'role_id' => $role->id,
                'permission_ids' => $role->permissions()->pluck('permissions.id'),
            ],
            'message' => 'Permissions updated for '.$role->display_name.'.',
        ]);
    }
}
