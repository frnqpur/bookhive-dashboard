<?php

namespace Database\Factories;

use App\Models\DemoResetLog;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DemoResetLog>
 */
class DemoResetLogFactory extends Factory
{
    public function definition(): array
    {
        return [
            'triggered_by' => null,
            'trigger_type' => fake()->randomElement(['scheduled', 'manual']),
            'started_at' => now()->subMinutes(1),
            'finished_at' => now(),
            'status' => fake()->randomElement(['success', 'failed']),
            'message' => fake()->sentence(),
            'summary' => [
                'deleted_users' => fake()->numberBetween(0, 5),
                'deleted_books' => fake()->numberBetween(0, 5),
                'deleted_reviews' => fake()->numberBetween(0, 10),
            ],
        ];
    }
}
