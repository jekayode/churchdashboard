<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

final class Role extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'display_name',
        'description',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Get the users that have this role.
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_roles')
            ->withPivot('branch_id')
            ->withTimestamps();
    }

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'role_permissions')
            ->withTimestamps();
    }

    public function hasPermission(string $name): bool
    {
        if ($this->relationLoaded('permissions')) {
            return $this->permissions->contains('name', $name);
        }

        return $this->permissions()->where('name', $name)->exists();
    }

    /**
     * @param  array<int>  $permissionIds
     */
    public function syncPermissions(array $permissionIds): void
    {
        $this->permissions()->sync($permissionIds);
    }

    public function isSystemRole(): bool
    {
        return in_array($this->name, self::systemRoleNames(), true);
    }

    /**
     * @return list<string>
     */
    public static function systemRoleNames(): array
    {
        return [
            'super_admin',
            'branch_pastor',
            'ministry_leader',
            'department_leader',
            'church_member',
            'public_user',
            'directory_admin',
            'business_care_leader',
        ];
    }

    /**
     * Scope to get roles by name.
     */
    public function scopeByName($query, string $name)
    {
        return $query->where('name', $name);
    }

    /**
     * Check if this is a super admin role.
     */
    public function isSuperAdmin(): bool
    {
        return $this->name === 'super_admin';
    }

    /**
     * Check if this is a branch pastor role.
     */
    public function isBranchPastor(): bool
    {
        return $this->name === 'branch_pastor';
    }
}
