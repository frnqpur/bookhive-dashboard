<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function before(User $actor, string $ability): ?bool
    {
        return $actor->isSuperAdmin() ? true : null;
    }

    public function viewAny(User $actor): bool
    {
        return $actor->can('users.manage');
    }

    public function view(User $actor, User $user): bool
    {
        if ($user->isSuperAdmin()) {
            return false;
        }

        return $actor->id === $user->id || $actor->can('users.manage');
    }

    public function create(User $actor): bool
    {
        return $actor->can('users.manage');
    }

    public function update(User $actor, User $user): bool
    {
        if ($actor->id === $user->id) {
            return true;
        }


        if ($user->isSuperAdmin() || $user->isProtectedUser()) {
            return false;
        }

        return $actor->can('users.manage');
    }

    public function delete(User $actor, User $user): bool
    {
        return $user->canBeDeletedBy($actor);
    }

    public function assignSuperAdmin(User $actor): bool
    {
        return $actor->isSuperAdmin();
    }
}
