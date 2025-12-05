<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'email' => fake()->unique()->safeEmail(),
            'password' => static::$password ??= Hash::make('password'),
            'full_name' => fake()->name(),
            'is_active' => true,

            // Commented preserved fields - uncomment when needed
            // 'email_verified_at' => now(),
            // 'remember_token' => Str::random(10),
        ];
    }

    /**
     * Indicate that the user should be inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn(array $attributes) => [
            'is_active' => false,
        ]);
    }

    /**
     * Create a user with a specific role.
     */
    public function withRole(string $roleName): static
    {
        return $this->afterCreating(function ($user) use ($roleName) {
            $role = \App\Models\Role::firstOrCreate(['role_name' => $roleName]);
            $user->roles()->attach($role->role_id);
        });
    }

    /**
     * Create an admin user.
     */
    public function admin(): static
    {
        return $this->withRole('Admin');
    }
}