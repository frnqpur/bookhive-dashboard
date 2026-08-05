<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\CoreRolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(CoreRolePermissionSeeder::class);
    }

    public function test_profile_page_is_displayed(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->get(route('dashboard.profile.edit'));

        $response->assertOk();
    }

    public function test_profile_information_can_be_updated(): void
    {
        $user = User::factory()->create();
        $user->assignRole(User::ROLE_CUSTOMER);

        $response = $this
            ->actingAs($user)
            ->patch(route('dashboard.profile.update'), [
                'name' => 'Test User',
                'email' => 'test@example.com',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('dashboard.profile.edit'));

        $user->refresh();

        $this->assertSame('Test User', $user->name);
        $this->assertSame('test@example.com', $user->email);
        $this->assertNull($user->email_verified_at);
    }

    public function test_email_verification_status_is_unchanged_when_the_email_address_is_unchanged(): void
    {
        $user = User::factory()->create();
        $user->assignRole(User::ROLE_CUSTOMER);

        $response = $this
            ->actingAs($user)
            ->patch(route('dashboard.profile.update'), [
                'name' => 'Test User',
                'email' => $user->email,
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('dashboard.profile.edit'));

        $this->assertNotNull($user->refresh()->email_verified_at);
    }

    public function test_user_can_delete_their_account(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->delete(route('dashboard.profile.destroy'), [
                'password' => 'password',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/');

        $this->assertGuest();
        $this->assertSoftDeleted('users', [
            'id' => $user->id,
        ]);
    }

    public function test_correct_password_must_be_provided_to_delete_account(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->from(route('dashboard.profile.edit'))
            ->delete(route('dashboard.profile.destroy'), [
                'password' => 'wrong-password',
            ]);

        $response
            ->assertSessionHasErrors('password')
            ->assertRedirect(route('dashboard.profile.edit'));

        $this->assertNotNull($user->fresh());
    }

    public function test_protected_account_cannot_be_deleted(): void
    {
        $user = User::factory()->create(['is_protected' => true]);

        $response = $this
            ->actingAs($user)
            ->from(route('dashboard.profile.edit'))
            ->delete(route('dashboard.profile.destroy'), [
                'password' => 'password',
            ]);

        $response
            ->assertSessionHasErrors('password')
            ->assertRedirect(route('dashboard.profile.edit'));

        $this->assertAuthenticated();
        $this->assertNotNull($user->fresh());
    }

    public function test_demo_account_cannot_be_deleted(): void
    {
        $user = User::factory()->demo()->create();

        $response = $this
            ->actingAs($user)
            ->from(route('dashboard.profile.edit'))
            ->delete(route('dashboard.profile.destroy'), [
                'password' => 'password',
            ]);

        $response
            ->assertSessionHasErrors('password')
            ->assertRedirect(route('dashboard.profile.edit'));

        $this->assertAuthenticated();
        $this->assertNotNull($user->fresh());
    }

    public function test_super_admin_account_cannot_be_deleted(): void
    {
        $user = User::factory()->create();
        $user->assignRole(User::ROLE_SUPER_ADMIN);

        $response = $this
            ->actingAs($user)
            ->from(route('dashboard.profile.edit'))
            ->delete(route('dashboard.profile.destroy'), [
                'password' => 'password',
            ]);

        $response
            ->assertSessionHasErrors('password')
            ->assertRedirect(route('dashboard.profile.edit'));

        $this->assertAuthenticated();
        $this->assertNotNull($user->fresh());
    }
}
