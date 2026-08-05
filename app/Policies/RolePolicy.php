<?php

namespace App\Policies;

use App\Models\Role;
use App\Models\User;

class RolePolicy
{
    public function before(User $actor, string $ability): ?bool
    {
        return $actor->isSuperAdmin() ? true : null;
    }

    public function viewAny(User $actor): bool
    {
        return $actor->can('roles.manage');
    }

    public function create(User $actor): bool
    {
        return $actor->can('roles.manage');
    }

    public function update(User $actor, Role $role): bool
    {
        return $role->canBeModifiedBy($actor);
    }

    public function delete(User $actor, Role $role): bool
    {
        return $role->canBeDeletedBy($actor);
    }
}
