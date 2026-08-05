<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\BookReview;
use App\Models\User;
use Database\Seeders\CoreRolePermissionSeeder;
use Database\Seeders\CoreUserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

class BookHiveJwtApiTest extends TestCase
{
    use RefreshDatabase;

    protected User $customer;

    protected function setUp(): void
    {
        parent::setUp();
        config(['jwt.secret' => str_repeat('a', 64)]);
        $this->seed(CoreRolePermissionSeeder::class);
        $this->seed(CoreUserSeeder::class);
        $this->customer = User::where('email', 'customer@demo.com')->firstOrFail();
    }

    public function test_api_register_rejects_super_admin_role(): void
    {
        $this->postJson('/api/client/register', [
            'name' => 'API Bad Actor',
            'email' => 'api-bad@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'role' => User::ROLE_SUPER_ADMIN,
        ])->assertStatus(422)->assertJson(['success' => false]);
    }

    public function test_api_can_create_and_manage_own_pending_review(): void
    {
        $book = Book::factory()->create(['status' => Book::STATUS_PUBLISHED, 'is_protected' => false]);
        $token = JWTAuth::fromUser($this->customer);

        $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson("/api/client/books/{$book->id}/reviews", [
                'rating' => 4,
                'title' => 'API Review',
                'body' => 'Created from the JWT API.',
            ])
            ->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.review.status', BookReview::STATUS_PENDING);

        $review = BookReview::where('book_id', $book->id)->where('created_by', $this->customer->id)->firstOrFail();

        $this->withHeader('Authorization', 'Bearer ' . $token)
            ->patchJson("/api/client/reviews/{$review->id}", [
                'rating' => 5,
                'title' => 'Updated API Review',
                'body' => 'Updated while still pending.',
            ])
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/client/my-reviews')
            ->assertOk()
            ->assertJsonPath('success', true);
    }
}
