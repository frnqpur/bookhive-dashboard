<?php

namespace App\Policies;

use App\Models\Permission;
use App\Models\User;

class PermissionPolicy
{
    public function before(User $actor, string $ability): ?bool
    {
        return $actor->isSuperAdmin() ? true : null;
    }

    public function viewAny(User $actor): bool
    {
        return $actor->can('permissions.manage');
    }

    public function create(User $actor): bool
    {
        return $actor->can('permissions.manage');
    }

    public function update(User $actor, Permission $permission): bool
    {
        return $permission->canBeModifiedBy($actor);
    }

    public function delete(User $actor, Permission $permission): bool
    {
        return $permission->canBeDeletedBy($actor);
    }
}
