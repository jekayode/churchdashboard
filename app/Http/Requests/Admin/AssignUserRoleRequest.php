<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;

final class AssignUserRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        $target = $this->route('user');
        $actor = $this->user();

        if (! $target instanceof User || ! $actor) {
            return false;
        }

        if ($actor->can('assignRole', $target)) {
            return true;
        }

        if ($actor->isBranchPastor() && ! $actor->isSuperAdmin()) {
            $branchId = (int) $this->input('branch_id');

            return $branchId > 0
                && $branchId === (int) $actor->getActiveBranchId()
                && ! $target->isSuperAdmin()
                && ! $target->isBranchPastor();
        }

        return false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'role_id' => ['required', 'integer', 'exists:roles,id'],
            'branch_id' => ['nullable', 'integer', 'exists:branches,id'],
        ];
    }
}
