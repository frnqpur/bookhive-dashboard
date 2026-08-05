<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\User;
use Database\Seeders\CoreRolePermissionSeeder;
use Database\Seeders\CoreUserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookHiveDemoResetCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_demo_reset_preserves_real_super_admin_and_restores_demo_accounts(): void
    {
        $this->seed(CoreRolePermissionSeeder::class);
        $this->seed(CoreUserSeeder::class);

        $realSuperAdmin = User::where('email', env('SUPER_ADMIN_EMAIL', 'super-admin@example.com'))->firstOrFail();
        $originalPassword = $realSuperAdmin->password;

        User::factory()->create(['email' => 'public-user@example.com', 'is_protected' => false, 'is_demo' => false]);
        Book::factory()->create(['title' => 'Public Temporary Book', 'is_protected' => false, 'is_seeded' => false]);

        $this->artisan('demo:reset --skip-storage-cleanup')->assertExitCode(0);

        $this->assertDatabaseHas('users', ['email' => $realSuperAdmin->email, 'is_protected' => true, 'is_demo' => false]);
        $this->assertSame($originalPassword, $realSuperAdmin->fresh()->password);
        $this->assertDatabaseMissing('users', ['email' => 'public-user@example.com']);
        $this->assertDatabaseHas('users', ['email' => 'admin@demo.com', 'is_protected' => true, 'is_demo' => true]);
        $this->assertDatabaseHas('demo_reset_logs', ['status' => 'success']);
    }
}
