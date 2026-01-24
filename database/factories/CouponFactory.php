<?php

namespace Database\Factories;

use App\Models\Coupon;
use App\Models\Event;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Coupon>
 */
class CouponFactory extends Factory
{
    protected $model = Coupon::class;

    public function definition(): array
    {
        return [
            'code' => strtoupper($this->faker->bothify('CPN-#####')),
            'type' => 'fixed',
            'value' => 500,
            'limit' => 0,
            'event_id' => Event::factory(),
            'status' => 'active',
            'start_date_time' => now()->subDay(),
            'end_date_time' => now()->addDay(),
        ];
    }

    public function percentage(float $value = 10): static
    {
        return $this->state(fn () => [
            'type' => 'percentage',
            'value' => $value,
        ]);
    }

    public function fixed(float $value = 500): static
    {
        return $this->state(fn () => [
            'type' => 'fixed',
            'value' => $value,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn () => [
            'status' => 'inactive',
        ]);
    }

    public function expired(): static
    {
        return $this->state(fn () => [
            'start_date_time' => now()->subDays(10),
            'end_date_time' => now()->subDay(),
        ]);
    }

    public function limited(int $remaining): static
    {
        return $this->state(fn () => [
            'limit' => $remaining,
        ]);
    }
}
