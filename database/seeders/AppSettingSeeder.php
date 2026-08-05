<?php

namespace Database\Seeders;

use App\Models\AppSetting;
use Illuminate\Database\Seeder;

class AppSettingSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            [
                'key' => 'app.name',
                'value' => 'BookHive Dashboard',
                'type' => 'string',
                'group' => 'general',
                'is_public' => true,
                'is_protected' => true,
            ],
            [
                'key' => 'app.description',
                'value' => 'A role-based book review and library management dashboard built with Laravel, React, Inertia, Tailwind, and MySQL/MariaDB.',
                'type' => 'string',
                'group' => 'general',
                'is_public' => true,
                'is_protected' => false,
            ],
            [
                'key' => 'app.footer_text',
                'value' => '© ' . date('Y') . ' Developed by Frengki Josua Purba',
                'type' => 'string',
                'group' => 'general',
                'is_public' => true,
                'is_protected' => true,
            ],

            [
                'key' => 'app.timezone',
                'value' => env('APP_TIMEZONE', 'Asia/Jakarta'),
                'type' => 'string',
                'group' => 'general',
                'is_public' => true,
                'is_protected' => true,
            ],
            [
                'key' => 'demo.reset_interval_hours',
                'value' => '6',
                'type' => 'integer',
                'group' => 'demo',
                'is_public' => true,
                'is_protected' => true,
            ],
            [
                'key' => 'demo.public_enabled',
                'value' => 'true',
                'type' => 'boolean',
                'group' => 'demo',
                'is_public' => true,
                'is_protected' => true,
            ],
            [
                'key' => 'demo.credentials',
                'value' => json_encode([
                    ['role' => 'Admin', 'email' => 'admin@demo.com', 'password' => 'password'],
                    ['role' => 'Editor', 'email' => 'editor@demo.com', 'password' => 'password'],
                    ['role' => 'Reviewer', 'email' => 'reviewer@demo.com', 'password' => 'password'],
                    ['role' => 'Customer', 'email' => 'customer@demo.com', 'password' => 'password'],
                ]),
                'type' => 'json',
                'group' => 'demo',
                'is_public' => true,
                'is_protected' => true,
            ],
            [
                'key' => 'security.hide_real_super_admin',
                'value' => 'true',
                'type' => 'boolean',
                'group' => 'security',
                'is_public' => false,
                'is_protected' => true,
            ],
            [
                'key' => 'contact.github',
                'value' => 'https://github.com/frnqpur',
                'type' => 'string',
                'group' => 'contact',
                'is_public' => true,
                'is_protected' => false,
            ],
            [
                'key' => 'contact.linkedin',
                'value' => 'https://www.linkedin.com/in/frengkijosuapurba',
                'type' => 'string',
                'group' => 'contact',
                'is_public' => true,
                'is_protected' => false,
            ],
            [
                'key' => 'contact.portfolio',
                'value' => 'https://frengkipurba.com',
                'type' => 'string',
                'group' => 'contact',
                'is_public' => true,
                'is_protected' => false,
            ],
            [
                'key' => 'contact.email',
                'value' => 'contact@frengkipurba.com',
                'type' => 'string',
                'group' => 'contact',
                'is_public' => true,
                'is_protected' => false,
            ],
        ];

        foreach ($settings as $setting) {
            AppSetting::updateOrCreate(
                ['key' => $setting['key']],
                [
                    'value' => $setting['value'],
                    'type' => $setting['type'],
                    'group' => $setting['group'],
                    'is_public' => $setting['is_public'],
                    'is_protected' => $setting['is_protected'],
                ]
            );
        }
    }
}
