<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\BookReview;
use App\Models\User;
use Database\Seeders\CoreRolePermissionSeeder;
use Database\Seeders\CoreUserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookHiveBooksReviewsTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $customer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(CoreRolePermissionSeeder::class);
        $this->seed(CoreUserSeeder::class);

        $this->admin = User::where('email', 'admin@demo.com')->firstOrFail();
        $this->customer = User::where('email', 'customer@demo.com')->firstOrFail();
    }

    public function test_admin_can_create_book_and_customer_cannot(): void
    {
        $this->actingAs($this->admin)
            ->patch(route('dashboard.be.books.storeUpdate'), [
                'title' => 'Production Laravel Patterns',
                'author' => 'BookHive Team',
                'category' => 'Engineering',
                'description' => 'A practical book for modern Laravel teams.',
                'published_year' => 2026,
                'status' => Book::STATUS_PUBLISHED,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('books', ['title' => 'Production Laravel Patterns', 'status' => Book::STATUS_PUBLISHED]);

        $this->actingAs($this->customer)
            ->get(route('dashboard.be.books.create'))
            ->assertForbidden();
    }

    public function test_review_workflow_updates_average_rating_from_approved_reviews_only(): void
    {
        $book = Book::factory()->create([
            'created_by' => $this->admin->id,
            'status' => Book::STATUS_PUBLISHED,
            'average_rating' => 0,
            'total_reviews' => 0,
        ]);

        $this->actingAs($this->customer)
            ->patch(route('dashboard.be.bookReviews.storeUpdate'), [
                'book_id' => $book->id,
                'rating' => 5,
                'title' => 'Excellent read',
                'body' => 'A helpful and well-structured book.',
            ])
            ->assertRedirect(route('dashboard.be.books.show', $book->id));

        $review = BookReview::where('book_id', $book->id)->firstOrFail();
        $this->assertSame(BookReview::STATUS_PENDING, $review->status);
        $this->assertSame(0, (int) $book->fresh()->total_reviews);

        $this->actingAs($this->admin)
            ->patch(route('dashboard.be.bookReviews.moderate', $review->id), [
                'status' => BookReview::STATUS_APPROVED,
                'moderation_note' => 'Approved for public display.',
            ])
            ->assertRedirect();

        $book->refresh();
        $this->assertSame(1, (int) $book->total_reviews);
        $this->assertEquals(5.0, (float) $book->average_rating);
    }
}
