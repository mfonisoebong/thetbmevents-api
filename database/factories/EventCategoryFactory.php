<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\EventCategory>
 */
class EventCategoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $category= fake()->unique()->randomElement(['online', 'paid', 'webinar', 'tech', 'tutorial', 'party', 'concert', 'club']);
        return [
            'category'=> $category,
            'slug'=> $category
        ];
    }
}
