<?php

namespace Database\Seeders;

use App\Models\Book;
use App\Models\BookReview;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class BookHiveSampleDataSeeder extends Seeder
{
    public function run(): void
    {
        $owner = User::role(User::ROLE_SUPER_ADMIN)->first()
            ?? User::where('email', env('SUPER_ADMIN_EMAIL', 'super-admin@example.com'))->first();

        $approver = User::where('email', 'admin@demo.com')->first() ?? $owner;
        $reviewer = User::where('email', 'reviewer@demo.com')->first() ?? $owner;
        $customer = User::where('email', 'customer@demo.com')->first() ?? $reviewer;

        $books = [
            [
                'title' => 'The Midnight Library',
                'author' => 'Matt Haig',
                'category' => 'Fiction',
                'published_year' => 2020,
                'description' => 'A reflective novel about choices, regret, and finding meaning through alternate versions of life.',
                'cover_image' => 'https://picsum.photos/seed/bookhive-midnight-library/480/720',
            ],
            [
                'title' => 'Atomic Habits',
                'author' => 'James Clear',
                'category' => 'Self Improvement',
                'published_year' => 2018,
                'description' => 'A practical guide to building better habits through small improvements and repeatable systems.',
                'cover_image' => 'https://picsum.photos/seed/bookhive-atomic-habits/480/720',
            ],
            [
                'title' => 'Clean Code',
                'author' => 'Robert C. Martin',
                'category' => 'Technology',
                'published_year' => 2008,
                'description' => 'A software engineering classic focused on writing readable, maintainable, and professional code.',
                'cover_image' => 'https://picsum.photos/seed/bookhive-clean-code/480/720',
            ],
            [
                'title' => 'The Psychology of Money',
                'author' => 'Morgan Housel',
                'category' => 'Business',
                'published_year' => 2020,
                'description' => 'A collection of lessons about wealth, greed, behavior, and long-term financial decision making.',
                'cover_image' => 'https://picsum.photos/seed/bookhive-money/480/720',
            ],
            [
                'title' => 'Sapiens',
                'author' => 'Yuval Noah Harari',
                'category' => 'History',
                'published_year' => 2011,
                'description' => 'A broad history of humankind, from early societies to modern global systems.',
                'cover_image' => 'https://picsum.photos/seed/bookhive-sapiens/480/720',
            ],
            [
                'title' => 'Deep Work',
                'author' => 'Cal Newport',
                'category' => 'Productivity',
                'published_year' => 2016,
                'description' => 'A productivity book about focused work, attention management, and high-value professional output.',
                'cover_image' => 'https://picsum.photos/seed/bookhive-deep-work/480/720',
            ],
        ];

        foreach ($books as $index => $bookData) {
            $book = Book::withTrashed()->updateOrCreate(
                ['slug' => Str::slug($bookData['title'])],
                [
                    'title' => $bookData['title'],
                    'ISBN_10' => str_pad((string) (1000000000 + $index + 1), 10, '0', STR_PAD_LEFT),
                    'ISBN_13' => str_pad((string) (9780000000000 + $index + 1), 13, '0', STR_PAD_LEFT),
                    'author' => $bookData['author'],
                    'category' => $bookData['category'],
                    'cover_image' => $bookData['cover_image'] ?? null,
                    'description' => $bookData['description'],
                    'published_year' => $bookData['published_year'],
                    'status' => 'published',
                    'created_by' => $owner?->id,
                    'is_seeded' => true,
                    'is_protected' => true,
                ]
            );

            if ($book->trashed()) {
                $book->restore();
            }

            $this->seedReviewsForBook($book, $reviewer, $customer, $approver);
            $book->refreshReviewStats();
        }
    }

    private function seedReviewsForBook(Book $book, ?User $reviewer, ?User $customer, ?User $approver): void
    {
        $reviews = [
            [
                'created_by' => $reviewer?->id,
                'rating' => 5,
                'title' => 'A strong recommendation',
                'body' => 'This book is useful for demo exploration because the review has clear content, a rating, and an approved moderation state.',
                'status' => 'approved',
            ],
            [
                'created_by' => $customer?->id,
                'rating' => 4,
                'title' => 'Useful and easy to follow',
                'body' => 'The structure is accessible and makes the dashboard sample data feel realistic without exposing private user data.',
                'status' => 'approved',
            ],
            [
                'created_by' => $reviewer?->id,
                'rating' => 3,
                'title' => 'Pending moderation example',
                'body' => 'This pending review is seeded so reviewer and editor flows can be tested safely.',
                'status' => 'pending',
            ],
        ];

        foreach ($reviews as $review) {
            $bookReview = BookReview::withTrashed()->updateOrCreate(
                [
                    'book_id' => $book->id,
                    'created_by' => $review['created_by'],
                    'title' => $review['title'],
                ],
                [
                    'rating' => $review['rating'],
                    'body' => $review['body'],
                    'content' => $review['body'],
                    'status' => $review['status'],
                    'moderation_note' => $review['status'] === 'approved' ? 'Seeded approved review.' : null,
                    'approved_by' => $review['status'] === 'approved' ? $approver?->id : null,
                    'approved_at' => $review['status'] === 'approved' ? now() : null,
                    'is_seeded' => true,
                    'is_protected' => true,
                ]
            );

            if ($bookReview->trashed()) {
                $bookReview->restore();
            }
        }
    }
}
