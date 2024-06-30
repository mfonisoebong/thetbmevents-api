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
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $userRole= fake()->randomElement(['organizer', 'admin']);

        $name= $userRole !=='organizer' ? [
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
        ]: [
            'buisness_name'=> fake()->company()
        ];

        $adminAttr= $userRole === 'admin' ? [
            'admin_role'=> fake()->randomElement(['super_admin', 'support', 'manager']),
        ]: [];

        return [
            'phone_dial_code'=> '+234',
            'phone_number'=> '332423432423',
            ...$name,
            ...$adminAttr,
            'role'=> $userRole,
            'country'=> fake()->country(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' =>Hash::make('12345678'), // password
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
