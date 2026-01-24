<?php

namespace Tests\Feature\V2;

use App\Models\Coupon;
use App\Models\Event;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApplyCouponTest extends TestCase
{
    use RefreshDatabase;

    public function test_apply_coupon_success_fixed_amount(): void
    {
        $event = Event::factory()->create();
        $coupon = Coupon::factory()
            ->fixed(500)
            ->create([
                'code' => 'SAVE500',
                'event_id' => (string)$event->id,
            ]);

        $response = $this->postJson('/api/v2/checkout/apply-coupon', [
            'coupon_code' => 'SAVE500',
            'event_id' => (string)$event->id,
            'amount' => 2000,
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('message', 'Coupon applied');
        $response->assertJsonPath('data.coupon_id', $coupon->id);
        $response->assertJsonPath('data.discount', 500.0);
        $response->assertJsonPath('data.total', 1500.0);
    }

    public function test_apply_coupon_invalid_for_event(): void
    {
        $eventA = Event::factory()->create();
        $eventB = Event::factory()->create();

        Coupon::factory()->create([
            'code' => 'SAVE10',
            'event_id' => (string)$eventA->id,
        ]);

        $response = $this->postJson('/api/v2/checkout/apply-coupon', [
            'coupon_code' => 'SAVE10',
            'event_id' => (string)$eventB->id,
            'amount' => 1000,
        ]);

        $response->assertStatus(422);
        $response->assertJsonPath('message', 'Invalid coupon code for this event');
    }

    public function test_apply_coupon_inactive(): void
    {
        $event = Event::factory()->create();

        Coupon::factory()->inactive()->create([
            'code' => 'OFF',
            'event_id' => (string)$event->id,
        ]);

        $response = $this->postJson('/api/v2/checkout/apply-coupon', [
            'coupon_code' => 'OFF',
            'event_id' => (string)$event->id,
            'amount' => 1000,
        ]);

        $response->assertStatus(422);
        $response->assertJsonPath('message', 'Coupon is inactive');
    }

    public function test_apply_coupon_expired_window(): void
    {
        $event = Event::factory()->create();

        Coupon::factory()->expired()->create([
            'code' => 'EXPIRED',
            'event_id' => (string)$event->id,
        ]);

        $response = $this->postJson('/api/v2/checkout/apply-coupon', [
            'coupon_code' => 'EXPIRED',
            'event_id' => (string)$event->id,
            'amount' => 1000,
        ]);

        $response->assertStatus(422);
        $response->assertJsonPath('message', 'Coupon is not active');
    }

    public function test_apply_coupon_percentage_caps_at_amount(): void
    {
        $event = Event::factory()->create();

        Coupon::factory()->percentage(100)->create([
            'code' => 'FREE',
            'event_id' => (string)$event->id,
        ]);

        $response = $this->postJson('/api/v2/checkout/apply-coupon', [
            'coupon_code' => 'FREE',
            'event_id' => (string)$event->id,
            'amount' => 1234.56,
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('data.discount', 1234.56);
        $response->assertJsonPath('data.total', 0.0);
    }
}
