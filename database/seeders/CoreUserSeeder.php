<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class CoreUserSeeder extends Seeder
{
    public function run(): void
    {
        $superAdmin = User::updateOrCreate(
            [
                'email' => env('SUPER_ADMIN_EMAIL', 'super-admin@example.com'),
            ],
            [
                'name' => env('SUPER_ADMIN_NAME', 'Frengki Josua Purba'),
                'password' => Hash::make(env('SUPER_ADMIN_PASSWORD') ?: throw new \InvalidArgumentException('SUPER_ADMIN_PASSWORD environment variable is missing or empty. Please set it to a secure value.')),
                'email_verified_at' => now(),
                'is_protected' => true,
                'is_demo' => false,
                'protected_reason' => 'Real Super Admin account. Hidden from public demo credentials and protected from destructive actions.',
                'created_by' => null,
                'status' => 'active',
            ]
        );
        $superAdmin->syncRoles([User::ROLE_SUPER_ADMIN]);

        $demoAccounts = [
            [
                'name' => 'Demo Admin',
                'email' => 'admin@demo.com',
                'role' => User::ROLE_ADMIN,
            ],
            [
                'name' => 'Demo Editor',
                'email' => 'editor@demo.com',
                'role' => User::ROLE_EDITOR,
            ],
            [
                'name' => 'Demo Reviewer',
                'email' => 'reviewer@demo.com',
                'role' => User::ROLE_REVIEWER,
            ],
            [
                'name' => 'Demo Customer',
                'email' => 'customer@demo.com',
                'role' => User::ROLE_CUSTOMER,
            ],
        ];

        foreach ($demoAccounts as $account) {
            $user = User::updateOrCreate(
                ['email' => $account['email']],
                [
                    'name' => $account['name'],
                    'password' => Hash::make('password'),
                    'email_verified_at' => now(),
                    'is_protected' => true,
                    'is_demo' => true,
                    'protected_reason' => 'Public demo account. Login is published, so email/password/role/status are locked.',
                    'created_by' => $superAdmin->id,
                    'status' => 'active',
                ]
            );

            $user->syncRoles([$account['role']]);
        }
    }
}
