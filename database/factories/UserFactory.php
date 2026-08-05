<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
            'is_protected' => false,
            'is_demo' => false,
            'protected_reason' => null,
            'created_by' => null,
            'last_login_at' => null,
            'status' => 'active',
            'remember_token' => Str::random(10),
        ];
    }

    public function demo(): static
    {
        return $this->state(fn () => [
            'is_demo' => true,
            'is_protected' => true,
            'protected_reason' => 'Public demo account. Reset-safe and locked from destructive edits.',
        ]);
    }
}
