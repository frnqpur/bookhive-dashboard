<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\CoreRolePermissionSeeder;
use Database\Seeders\CoreUserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookHiveProtectionAndPermissionTest extends TestCase
{
    use RefreshDatabase;

    protected User $superAdmin;
    protected User $admin;
    protected User $editor;

    protected function setUp(): void
    {
        parent::setUp();
        config(['permission.cache.store' => 'array']);
        $this->seed(CoreRolePermissionSeeder::class);
        $this->seed(CoreUserSeeder::class);

        $this->superAdmin = User::where('email', env('SUPER_ADMIN_EMAIL', 'super-admin@example.com'))->firstOrFail();
        $this->admin = User::where('email', 'admin@demo.com')->firstOrFail();
        $this->editor = User::where('email', 'editor@demo.com')->firstOrFail();
    }

    public function test_admin_cannot_access_super_admin_only_demo_reset_page(): void
    {
        $this->actingAs($this->admin)
            ->get(route('dashboard.demoReset.index'))
            ->assertForbidden();
    }

    public function test_editor_cannot_access_user_management(): void
    {
        $this->actingAs($this->editor)
            ->get(route('dashboard.global.users.list'))
            ->assertForbidden();
    }

    public function test_admin_cannot_delete_protected_demo_account(): void
    {
        $this->actingAs($this->admin)
            ->delete(route('dashboard.global.users.remove', $this->editor->id))
            ->assertForbidden();

        $this->assertNotSoftDeleted('users', ['id' => $this->editor->id]);
    }

    public function test_admin_cannot_assign_super_admin_role(): void
    {
        $target = User::factory()->create(['email' => 'target@example.com']);
        $target->assignRole(User::ROLE_CUSTOMER);

        $this->actingAs($this->admin)
            ->patch(route('dashboard.global.users.storeUpdate', $target->id), [
                'name' => $target->name,
                'email' => $target->email,
                'password' => '',
                'password_confirmation' => '',
                'roles' => User::ROLE_SUPER_ADMIN,
                'status' => 'active',
            ])
            ->assertSessionHasErrors('roles');

        $this->assertFalse($target->fresh()->hasRole(User::ROLE_SUPER_ADMIN));
    }
}
