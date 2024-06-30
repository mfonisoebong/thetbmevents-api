<?php

namespace Database\Factories;

use App\Models\Event;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Ticket>
 */
class TicketFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $event= Event::all()->random();
        $unlimited= fake()->randomElement([true, false]);

        return [
            'organizer_id'=> $event->user_id,
            'event_id'=> $event->id,
            'name'=> fake()->randomElement(['Regular_d', 'VIP_d', 'VVIP_d']),
            'price'=> fake()->randomElement([3000, 2300, 5000]),
            'unlimited'=> $unlimited,
            'quantity'=> $unlimited ? 0: fake()->randomElement([100,200,300]),
        ];
    }
}
