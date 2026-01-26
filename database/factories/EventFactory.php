<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Event>
 */
class EventFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => fake()->sentence(3),
            'user_id' => User::all()->random()->id,
            'description' => fake()->text(200),
            'timezone' => fake()->randomElement(['WAT', 'UTC']),
            'type' => 'physical',
            'image_url' => fake()->randomElement(['storage/event_img_1.png', 'storage/event_img_2.png', 'storage/event_img_3.png', null]),
            'location' => fake()->randomElement(['Abuja, Nigeria', 'Lagos, Nigeria', 'Akure, Ondo']),
            'attendees' => fake()->numberBetween(0, 2000),
            'event_date' => fake()->date(),
            'event_time' => fake()->time(),
            'undisclose_location' => fake()->boolean(),
            'category' => fake()->randomElement(['free,online', 'online,paid', 'online', 'free', null])
        ];
    }
}
