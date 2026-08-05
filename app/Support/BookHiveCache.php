<?php

namespace App\Support;

use Illuminate\Support\Facades\Cache;

final class BookHiveCache
{
    public const PUBLIC_CONTACT = 'bookhive:settings:public_contact:v1';
    public const ADMIN_USERS_BY_ROLE = 'bookhive:dashboard:admin:users_by_role:v1';

    public const TTL_PUBLIC_CONTACT = 86400;
    public const TTL_ADMIN_USERS_BY_ROLE = 3600;

    public static function rememberPublicContact(callable $resolver): mixed
    {
        return Cache::remember(self::PUBLIC_CONTACT, self::TTL_PUBLIC_CONTACT, $resolver);
    }

    public static function rememberAdminUsersByRole(callable $resolver): mixed
    {
        return Cache::remember(self::ADMIN_USERS_BY_ROLE, self::TTL_ADMIN_USERS_BY_ROLE, $resolver);
    }

    public static function forgetPublicContact(): void
    {
        Cache::forget(self::PUBLIC_CONTACT);
    }

    public static function forgetAdminUsersByRole(): void
    {
        Cache::forget(self::ADMIN_USERS_BY_ROLE);
    }

    public static function forgetAllManagedKeys(): void
    {
        self::forgetPublicContact();
        self::forgetAdminUsersByRole();
    }
}
