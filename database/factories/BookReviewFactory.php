<?php

namespace Database\Factories;

use App\Models\BookReview;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BookReview>
 */
class BookReviewFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $body = fake()->paragraphs(fake()->numberBetween(2, 4), true);

        return [
            'rating' => fake()->numberBetween(1, 5),
            'title' => fake()->sentence(5),
            'body' => $body,
            'content' => $body,
            'status' => fake()->randomElement(['pending', 'approved', 'rejected']),
            'moderation_note' => null,
            'approved_by' => null,
            'approved_at' => null,
            'is_seeded' => false,
            'is_protected' => false,
        ];
    }

    public function approved(): static
    {
        return $this->state(fn () => [
            'status' => 'approved',
            'approved_at' => now(),
        ]);
    }

    public function seeded(): static
    {
        return $this->state(fn () => [
            'is_seeded' => true,
            'is_protected' => true,
        ]);
    }
}
