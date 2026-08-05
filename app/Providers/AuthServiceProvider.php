<?php

namespace App\Providers;

use App\Models\AppSetting;
use App\Models\Book;
use App\Models\BookReview;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Policies\AppSettingPolicy;
use App\Policies\BookPolicy;
use App\Policies\BookReviewPolicy;
use App\Policies\PermissionPolicy;
use App\Policies\RolePolicy;
use App\Policies\UserPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        User::class => UserPolicy::class,
        Role::class => RolePolicy::class,
        Permission::class => PermissionPolicy::class,
        Book::class => BookPolicy::class,
        BookReview::class => BookReviewPolicy::class,
        AppSetting::class => AppSettingPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        Gate::before(function (User $user, string $ability) {
            return $user->isSuperAdmin() ? true : null;
        });
    }
}
