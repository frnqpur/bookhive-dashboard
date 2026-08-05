<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\CoreRolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookHiveAuthAndRegisterTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(CoreRolePermissionSeeder::class);
    }

    public function test_public_register_requires_allowed_non_super_admin_role(): void
    {
        $this->post('/register', [
            'name' => 'Public Reviewer',
            'email' => 'public-reviewer@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'role' => User::ROLE_REVIEWER,
        ])->assertRedirect('/dashboard');

        $this->assertDatabaseHas('users', ['email' => 'public-reviewer@example.com']);
        $this->assertTrue(User::where('email', 'public-reviewer@example.com')->first()->hasRole(User::ROLE_REVIEWER));
    }

    public function test_public_register_cannot_choose_super_admin(): void
    {
        $this->from('/register')->post('/register', [
            'name' => 'Bad Actor',
            'email' => 'bad@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'role' => User::ROLE_SUPER_ADMIN,
        ])->assertRedirect('/register')->assertSessionHasErrors('role');

        $this->assertDatabaseMissing('users', ['email' => 'bad@example.com']);
    }
}
