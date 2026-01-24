<?php

namespace Tests\Feature\V2;

use App\Models\Event;
use App\Models\PaymentMethod;
use App\Models\Ticket;
use App\Models\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ProcessCheckoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_process_checkout_creates_transaction_and_transforms_cart_items(): void
    {
        Http::fake([
            '*' => Http::response([
                'status' => true,
                'data' => [
                    'authorization_url' => 'https://gateway.test/redirect',
                    'reference' => 'ref-123',
                ],
            ], 200),
        ]);

        $event = Event::factory()->create();

        $ticketA = Ticket::factory()->create([
            'event_id' => (string)$event->id,
            'price' => 1000,
            'selling_start_date_time' => now()->subDay(),
            'selling_end_date_time' => now()->addDay(),
        ]);

        $ticketB = Ticket::factory()->create([
            'event_id' => (string)$event->id,
            'price' => 2000,
            'selling_start_date_time' => now()->subDay(),
            'selling_end_date_time' => now()->addDay(),
        ]);

        PaymentMethod::create([
            'gateway' => 'paystack',
            'paystack_test_key' => 'test_sk',
            'paystack_live_key' => 'live_sk',
        ]);

        $payload = [
            'tickets' => [
                (string)$ticketA->id,
                (string)$ticketA->id,
                (string)$ticketB->id,
                (string)$ticketB->id,
            ],
            'customer' => [
                'fullname' => 'John Doe',
                'email' => 'john@example.com',
                'phone' => '08000000000',
            ],
            'send_to_different_email' => false,
            'attendees' => [],
            'gateway' => 'paystack',
            'is_free_checkout' => false,
            'coupon_applied' => false,
        ];

        $response = $this->postJson('/api/v2/checkout', $payload);

        $response->assertStatus(200);

        $this->assertDatabaseCount('transactions', 1);

        /** @var Transaction $tx */
        $tx = Transaction::firstOrFail();

        $this->assertSame('pending', $tx->status);
        $this->assertSame('paystack', $tx->gateway);

        // cart items transformed into quantities
        $this->assertCount(2, $tx->cart_items);

        $this->assertEquals([
            ['id' => (string)$ticketA->id, 'quantity' => 2],
            ['id' => (string)$ticketB->id, 'quantity' => 2],
        ], $tx->cart_items);

        // customer stored in data column, not as a Customer record
        $this->assertSame('john@example.com', $tx->data['customer']['email']);

        // total amount (no coupon): 2*1000 + 2*2000 = 6000
        $this->assertSame(6000.0, $tx->amount);
    }
}
