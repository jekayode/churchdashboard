<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AssignUserRoleRequest;
use App\Http\Requests\Admin\RevokeUserRoleRequest;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class UserRoleController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $actor = $request->user();

        if (! $actor->hasPermission('users.view') && ! $actor->hasPermission('users.assign_role')) {
            abort(403);
        }

        $query = User::query()
            ->with(['roles' => fn ($q) => $q->withPivot('branch_id')])
            ->orderBy('name');

        if ($actor->isBranchPastor() && ! $actor->isSuperAdmin()) {
            $branchId = $actor->getActiveBranchId();

            if (! $branchId) {
                return response()->json(['success' => true, 'data' => ['data' => [], 'total' => 0]]);
            }

            $query->where(function ($q) use ($branchId): void {
                $q->whereHas('roles', fn ($r) => $r->where('user_roles.branch_id', $branchId))
                    ->orWhereHas('member', fn ($m) => $m->where('branch_id', $branchId));
            });
        }

        if ($search = $request->query('search')) {
            $query->where(function ($q) use ($search): void {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $users = $query->paginate(20);

        $users->getCollection()->transform(function (User $user): array {
            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'roles' => $user->roles->map(fn (Role $role) => [
                    'id' => $role->id,
                    'name' => $role->name,
                    'display_name' => $role->display_name,
                    'branch_id' => $role->pivot->branch_id,
                ]),
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $users,
        ]);
    }

    public function assign(AssignUserRoleRequest $request, User $user): JsonResponse
    {
        $actor = $request->user();
        $role = Role::query()->findOrFail($request->validated('role_id'));
        $branchId = $request->validated('branch_id');

        $this->assertCanAssignRole($actor, $user, $role, $branchId);

        if (! $user->hasRole($role->name, $branchId)) {
            $user->assignRole($role->name, $branchId);
        }

        return response()->json([
            'success' => true,
            'message' => 'Role assigned.',
            'data' => $this->userPayload($user->fresh(['roles'])),
        ]);
    }

    public function revoke(RevokeUserRoleRequest $request, User $user): JsonResponse
    {
        $actor = $request->user();
        $role = Role::query()->findOrFail($request->validated('role_id'));
        $branchId = $request->validated('branch_id');

        $this->assertCanAssignRole($actor, $user, $role, $branchId);

        $user->removeRole($role->name, $branchId);

        return response()->json([
            'success' => true,
            'message' => 'Role removed.',
            'data' => $this->userPayload($user->fresh(['roles'])),
        ]);
    }

    private function assertCanAssignRole(User $actor, User $target, Role $role, ?int $branchId): void
    {
        if ($actor->isSuperAdmin()) {
            if ($role->name === 'super_admin' && $target->id !== $actor->id) {
                // allow super admin to grant super_admin only if desired - keep allowed for super admin
            }

            return;
        }

        if ($role->name === 'super_admin' || $role->name === 'branch_pastor') {
            abort(403, 'You cannot assign this role.');
        }

        if ($actor->isBranchPastor()) {
            $pastorBranchId = $actor->getActiveBranchId();

            if ($branchId !== $pastorBranchId) {
                abort(403, 'You can only assign roles within your branch.');
            }

            if ($target->isSuperAdmin() || $target->isBranchPastor()) {
                abort(403, 'You cannot modify roles for this user.');
            }
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function userPayload(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'roles' => $user->roles->map(fn (Role $role) => [
                'id' => $role->id,
                'name' => $role->name,
                'display_name' => $role->display_name,
                'branch_id' => $role->pivot->branch_id,
            ]),
        ];
    }
}
