<?php

namespace Database\Factories;

use App\Models\Book;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Book>
 */
class BookFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = fake()->unique()->sentence(3);

        return [
            'title' => $title,
            'slug' => Str::slug($title) . '-' . Str::lower(Str::random(5)),
            'ISBN_10' => fake()->unique()->numerify('##########'),
            'ISBN_13' => fake()->unique()->numerify('#############'),
            'author' => fake()->name(),
            'category' => fake()->randomElement(['Fiction', 'Business', 'Technology', 'History', 'Self Improvement']),
            'cover_image' => null,
            'description' => fake()->paragraphs(3, true),
            'published_year' => fake()->numberBetween(1990, (int) date('Y')),
            'status' => 'published',
            'average_rating' => 0,
            'total_reviews' => 0,
            'created_by' => null,
            'is_seeded' => false,
            'is_protected' => false,
        ];
    }

    public function seeded(): static
    {
        return $this->state(fn () => [
            'is_seeded' => true,
            'is_protected' => true,
        ]);
    }
}
