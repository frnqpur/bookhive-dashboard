<?php

namespace App\Policies;

use App\Models\AppSetting;
use App\Models\User;

class AppSettingPolicy
{
    public function before(User $actor, string $ability): ?bool
    {
        return $actor->isSuperAdmin() ? true : null;
    }

    public function viewAny(User $actor): bool
    {
        return $actor->can('settings.manage');
    }

    public function update(User $actor, AppSetting $setting): bool
    {
        if ($setting->is_protected) {
            return false;
        }

        return $actor->can('settings.manage');
    }
}
