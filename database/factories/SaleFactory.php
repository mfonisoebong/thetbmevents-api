<?php

namespace Database\Factories;

use App\Models\Ticket;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Sale>
 */
class SaleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $ticket= Ticket::all()->random();
        $customer= User::all()->random();
        $today= Carbon::today()->toDateString();
        $yesterday= Carbon::yesterday()->toDateString();
        $lastThreeMonths= Carbon::now()->subMonths(3)
        ->toDateString();



        return [
            'organizer_id'=> $ticket->organizer_id,
            'customer_id'=> $customer->id,
            'ticket_id'=> $ticket->id,
            'event_id'=> $ticket->event_id,
            'tickets_bought'=> fake()->numberBetween(1,4),
            'total'=> 3000.00,
            'created_at'=> fake()->randomElement([$today, $yesterday, $lastThreeMonths])
        ];
    }
}
