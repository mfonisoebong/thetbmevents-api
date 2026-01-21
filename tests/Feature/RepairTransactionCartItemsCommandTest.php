<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class RepairTransactionCartItemsCommandTest extends TestCase
{
    use RefreshDatabase;

    private function createCustomerId(): int
    {
        // Customer factory doesn't exist in this repo, so create a minimal customer row.
        // If the Customer model has guarded fields, fall back to inserting directly.
        try {
            $customer = Customer::create([
                'first_name' => 'Test',
                'last_name' => 'Customer',
                'email' => 'test@example.com',
                'phone_dial_code' => '+1',
                'phone_number' => '5550000000',
            ]);

            return (int) $customer->id;
        } catch (\Throwable $e) {
            DB::table('customers')->insert([
                'first_name' => 'Test',
                'last_name' => 'Customer',
                'email' => 'test@example.com',
                'phone_dial_code' => '+1',
                'phone_number' => '5550000000',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return (int) DB::getPdo()->lastInsertId();
        }
    }

    public function test_it_does_not_touch_rows_before_since(): void
    {
        $customerId = $this->createCustomerId();

        $before = Transaction::create([
            'customer_id' => $customerId,
            'payment_method' => 'paystack',
            'cart_items' => [['id' => 't1', 'quantity' => 1]],
            'transaction_reference' => 'ref_before',
            'payment_status' => 'pending',
            'created_at' => '2025-12-24 14:51:08',
        ]);

        $this->artisan('app:repair-transaction-cart-items --since="2025-12-24 14:51:09"')
            ->assertExitCode(0);

        $before->refresh();
        $this->assertIsArray($before->cart_items);
        $this->assertSame([['id' => 't1', 'quantity' => 1]], $before->cart_items);
    }

    public function test_it_repairs_stringified_json_in_dry_run_and_force_modes(): void
    {
        $customerId = $this->createCustomerId();

        // Corrupted: JSON string stored in JSON column.
        $corrupted = Transaction::create([
            'customer_id' => $customerId,
            'payment_method' => 'paystack',
            'cart_items' => json_encode([['id' => 'a', 'quantity' => 1]]),
            'transaction_reference' => 'ref_corrupted',
            'payment_status' => 'pending',
            'created_at' => '2025-12-24 14:51:09',
        ]);

        // Dry-run should not persist (it will still report it as fixable).
        $this->artisan('app:repair-transaction-cart-items --since="2025-12-24 14:51:09"')
            ->assertExitCode(0);

        $corrupted->refresh();
        $this->assertIsString($corrupted->getRawOriginal('cart_items'));

        // Force mode should persist the normalized array.
        $this->artisan('app:repair-transaction-cart-items --since="2025-12-24 14:51:09" --force')
            ->assertExitCode(0);

        $corrupted->refresh();
        $this->assertIsArray($corrupted->cart_items);
        $this->assertSame([['id' => 'a', 'quantity' => 1]], $corrupted->cart_items);
    }
}
