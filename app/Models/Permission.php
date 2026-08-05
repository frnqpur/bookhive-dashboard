<?php

namespace App\Models;

class Permission extends \Spatie\Permission\Models\Permission
{
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

        return $actor->isSuperAdmin() || $actor->can('permissions.manage');
    }

    public function canBeModifiedBy(?User $actor): bool
    {
        if (! $actor) {
            return false;
        }

        if ($this->is_protected && ! $actor->isSuperAdmin()) {
            return false;
        }

        return $actor->isSuperAdmin() || $actor->can('permissions.manage');
    }
}
