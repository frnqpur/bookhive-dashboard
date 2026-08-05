<?php

namespace App\Models;

class Role extends \Spatie\Permission\Models\Role
{
    public const CORE_ROLES = [
        'Super Admin',
        'Admin',
        'Editor',
        'Reviewer',
        'Customer',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'slug',
        'description',
        'is_active',
        'is_core',
        'is_protected',
        'protected_reason',
        'guard_name',
        'user_type',
        'record_access',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_core' => 'boolean',
        'is_protected' => 'boolean',
    ];

    public function canBeDeletedBy(?User $actor): bool
    {
        if (! $actor || $this->is_core || $this->is_protected) {
            return false;
        }

        return $actor->isSuperAdmin() || $actor->can('roles.manage');
    }

    public function canBeModifiedBy(?User $actor): bool
    {
        if (! $actor) {
            return false;
        }

        if ($this->name === User::ROLE_SUPER_ADMIN && ! $actor->isSuperAdmin()) {
            return false;
        }

        if ($this->is_protected && ! $actor->isSuperAdmin()) {
            return false;
        }

        return $actor->isSuperAdmin() || $actor->can('roles.manage');
    }
}
