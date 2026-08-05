<?php

namespace Tests\Feature;

use App\Models\AppSetting;
use App\Models\User;
use App\Support\BookHiveCache;
use Database\Seeders\CoreRolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class BookHiveCacheTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(CoreRolePermissionSeeder::class);
    }

    public function test_public_contact_query_stores_result_in_cache(): void
    {
        $this->assertFalse(Cache::has(BookHiveCache::PUBLIC_CONTACT));

        $response = $this->get(route('contact-developer'));
        $response->assertOk();

        $this->assertTrue(Cache::has(BookHiveCache::PUBLIC_CONTACT));
    }

    public function test_update_app_setting_clears_public_contact_cache(): void
    {
        $this->get(route('contact-developer'))->assertOk();
        $this->assertTrue(Cache::has(BookHiveCache::PUBLIC_CONTACT));

        $admin = User::factory()->create();
        $admin->assignRole(User::ROLE_ADMIN);

        AppSetting::firstOrCreate(
            ['key' => 'contact.github'],
            ['value' => 'https://github.com/old', 'type' => 'string', 'group' => 'contact', 'is_public' => true, 'is_protected' => false]
        );

        $this->actingAs($admin)
            ->patch(route('dashboard.globalSettings.update'), [
                'key' => 'contact.github',
                'value' => 'https://github.com/new',
            ])
            ->assertRedirect(route('dashboard.globalSettings.edit'));

        $this->assertFalse(Cache::has(BookHiveCache::PUBLIC_CONTACT));
    }

    public function test_users_by_role_stores_result_in_cache_for_authorized_manager(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole(User::ROLE_ADMIN);

        $this->assertFalse(Cache::has(BookHiveCache::ADMIN_USERS_BY_ROLE));

        $response = $this->actingAs($admin)->get(route('dashboard'));
        $response->assertOk();

        $this->assertTrue(Cache::has(BookHiveCache::ADMIN_USERS_BY_ROLE));
    }

    public function test_registration_clears_users_by_role_cache(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole(User::ROLE_ADMIN);

        $this->actingAs($admin)->get(route('dashboard'))->assertOk();
        $this->assertTrue(Cache::has(BookHiveCache::ADMIN_USERS_BY_ROLE));

        $this->post(route('logout'))->assertRedirect('/');

        $this->post('/register', [
            'name' => 'New Customer',
            'email' => 'customer@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'role' => User::ROLE_CUSTOMER,
        ])->assertRedirect(route('dashboard'));

        $this->assertDatabaseHas('users', ['email' => 'customer@example.com']);
        $this->assertTrue(User::where('email', 'customer@example.com')->first()->hasRole(User::ROLE_CUSTOMER));
        $this->assertFalse(Cache::has(BookHiveCache::ADMIN_USERS_BY_ROLE));
    }

    public function test_user_management_clears_users_by_role_cache(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole(User::ROLE_ADMIN);

        $this->actingAs($admin)->get(route('dashboard'))->assertOk();
        $this->assertTrue(Cache::has(BookHiveCache::ADMIN_USERS_BY_ROLE));

        $this->actingAs($admin)->patch(route('dashboard.global.users.storeUpdate'), [
            'name' => 'New Editor',
            'email' => 'editor@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'status' => 'active',
            'roles' => User::ROLE_EDITOR,
        ])->assertRedirect(route('dashboard.global.users.list'));

        $this->assertFalse(Cache::has(BookHiveCache::ADMIN_USERS_BY_ROLE));
    }

    public function test_updating_existing_user_role_clears_users_by_role_cache(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole(User::ROLE_ADMIN);

        $target = User::factory()->create();
        $target->assignRole(User::ROLE_CUSTOMER);

        $this->actingAs($admin)->get(route('dashboard'))->assertOk();
        $this->assertTrue(Cache::has(BookHiveCache::ADMIN_USERS_BY_ROLE));

        $this->actingAs($admin)->patch(route('dashboard.global.users.storeUpdate', $target->id), [
            'name' => $target->name,
            'email' => $target->email,
            'password' => '',
            'status' => 'active',
            'roles' => User::ROLE_EDITOR,
        ])->assertRedirect(route('dashboard.global.users.list'));

        $this->assertFalse(Cache::has(BookHiveCache::ADMIN_USERS_BY_ROLE));
        $this->assertTrue($target->fresh()->hasRole(User::ROLE_EDITOR));
    }

    public function test_profile_self_delete_clears_users_by_role_cache(): void
    {
        $customer = User::factory()->create();
        $customer->assignRole(User::ROLE_CUSTOMER);

        $admin = User::factory()->create();
        $admin->assignRole(User::ROLE_ADMIN);

        $this->actingAs($admin)->get(route('dashboard'))->assertOk();
        $this->assertTrue(Cache::has(BookHiveCache::ADMIN_USERS_BY_ROLE));

        $this->actingAs($customer)
            ->delete(route('dashboard.profile.destroy'), [
                'password' => 'password',
            ])
            ->assertRedirect('/');

        $this->assertGuest();
        $this->assertSoftDeleted('users', ['id' => $customer->id]);
        $this->assertFalse(Cache::has(BookHiveCache::ADMIN_USERS_BY_ROLE));
    }

    public function test_role_management_clears_users_by_role_cache(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole(User::ROLE_ADMIN);

        $this->actingAs($admin)->get(route('dashboard'))->assertOk();
        $this->assertTrue(Cache::has(BookHiveCache::ADMIN_USERS_BY_ROLE));

        $this->actingAs($admin)->patch(route('dashboard.global.roles.storeUpdate'), [
            'name' => 'Custom Moderator',
            'slug' => 'custom-moderator',
            'description' => 'Custom role for moderation',
            'is_active' => true,
            'guard_name' => 'web',
            'user_type' => 'customer',
            'record_access' => 'owned',
            'permissions' => ['reviews.view', 'reviews.approve'],
        ])->assertRedirect(route('dashboard.global.roles.list'));

        $this->assertFalse(Cache::has(BookHiveCache::ADMIN_USERS_BY_ROLE));
    }

    public function test_demo_reset_clears_both_managed_cache_keys(): void
    {
        Cache::put(BookHiveCache::PUBLIC_CONTACT, ['test' => 'data'], 3600);
        Cache::put(BookHiveCache::ADMIN_USERS_BY_ROLE, ['test' => 'data'], 3600);

        $this->assertTrue(Cache::has(BookHiveCache::PUBLIC_CONTACT));
        $this->assertTrue(Cache::has(BookHiveCache::ADMIN_USERS_BY_ROLE));

        $superAdmin = User::factory()->create(['email' => 'super-admin-test@example.com']);
        $superAdmin->assignRole(User::ROLE_SUPER_ADMIN);

        $this->artisan('demo:reset', ['--trigger' => 'manual', '--user-id' => $superAdmin->id])
            ->assertExitCode(0);

        $this->assertFalse(Cache::has(BookHiveCache::PUBLIC_CONTACT));
        $this->assertFalse(Cache::has(BookHiveCache::ADMIN_USERS_BY_ROLE));
    }
}
