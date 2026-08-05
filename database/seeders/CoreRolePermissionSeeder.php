<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Spatie\Permission\PermissionRegistrar;

class CoreRolePermissionSeeder extends Seeder
{
    public const PERMISSIONS = [
        'dashboard.view' => 'View dashboard',
        'users.manage' => 'Manage users',
        'roles.manage' => 'Manage roles',
        'permissions.manage' => 'Manage permissions',
        'books.view' => 'View books',
        'books.manage' => 'Manage books',
        'reviews.view' => 'View reviews',
        'reviews.create' => 'Create reviews',
        'reviews.update-own' => 'Update own reviews',
        'reviews.manage' => 'Manage reviews',
        'reviews.approve' => 'Approve or reject reviews',
        'settings.manage' => 'Manage settings',
        'audit-logs.view' => 'View audit logs',
        'demo-reset.manage' => 'Manage demo reset',
        'api-docs.access' => 'Access API docs',
        'profile.update-own' => 'Update own profile',
    ];

    public const ROLE_PERMISSIONS = [
        User::ROLE_SUPER_ADMIN => [
            'dashboard.view',
            'users.manage',
            'roles.manage',
            'permissions.manage',
            'books.view',
            'books.manage',
            'reviews.view',
            'reviews.create',
            'reviews.update-own',
            'reviews.manage',
            'reviews.approve',
            'settings.manage',
            'audit-logs.view',
            'demo-reset.manage',
            'api-docs.access',
            'profile.update-own',
        ],
        User::ROLE_ADMIN => [
            'dashboard.view',
            'users.manage',
            'roles.manage',
            'permissions.manage',
            'books.view',
            'books.manage',
            'reviews.view',
            'reviews.create',
            'reviews.update-own',
            'reviews.manage',
            'reviews.approve',
            'settings.manage',
            'audit-logs.view',
            'api-docs.access',
            'profile.update-own',
        ],
        User::ROLE_EDITOR => [
            'dashboard.view',
            'books.view',
            'books.manage',
            'reviews.view',
            'api-docs.access',
            'profile.update-own',
        ],
        User::ROLE_REVIEWER => [
            'dashboard.view',
            'books.view',
            'reviews.view',
            'reviews.create',
            'reviews.update-own',
            'api-docs.access',
            'profile.update-own',
        ],
        User::ROLE_CUSTOMER => [
            'dashboard.view',
            'books.view',
            'reviews.view',
            'reviews.create',
            'reviews.update-own',
            'profile.update-own',
        ],
    ];

    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (self::PERMISSIONS as $name => $description) {
            Permission::updateOrCreate(
                ['name' => $name, 'guard_name' => 'web'],
                [
                    'slug' => Str::slug($name, '__'),
                    'description' => $description,
                    'is_active' => true,
                    'is_core' => true,
                    'is_protected' => true,
                    'protected_reason' => 'Core BookHive permission required by the application.',
                ]
            );
        }

        $roles = [
            User::ROLE_SUPER_ADMIN => [
                'description' => 'Full owner access. Only the private real owner account should use this role.',
                'user_type' => 'internal',
                'record_access' => 'all',
            ],
            User::ROLE_ADMIN => [
                'description' => 'Administrative demo role with management permissions except Super Admin ownership controls.',
                'user_type' => 'internal',
                'record_access' => 'all',
            ],
            User::ROLE_EDITOR => [
                'description' => 'Content manager role for maintaining books and viewing review activity.',
                'user_type' => 'internal',
                'record_access' => 'all',
            ],
            User::ROLE_REVIEWER => [
                'description' => 'Review-focused role for creating and managing reviews.',
                'user_type' => 'customer',
                'record_access' => 'owned',
            ],
            User::ROLE_CUSTOMER => [
                'description' => 'Default public role for regular dashboard exploration.',
                'user_type' => 'customer',
                'record_access' => 'owned',
            ],
        ];

        foreach ($roles as $roleName => $attributes) {
            $role = Role::updateOrCreate(
                ['name' => $roleName, 'guard_name' => 'web'],
                [
                    'slug' => Str::slug($roleName, '__'),
                    'description' => $attributes['description'],
                    'is_active' => true,
                    'is_core' => true,
                    'is_protected' => true,
                    'protected_reason' => 'Core BookHive role required by the application.',
                    'user_type' => $attributes['user_type'],
                    'record_access' => $attributes['record_access'],
                ]
            );

            $role->syncPermissions(self::ROLE_PERMISSIONS[$roleName]);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
