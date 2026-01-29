<?php

namespace Tests\Unit;

use App\Models\Transaction;
use InvalidArgumentException;
use Tests\TestCase;

class TransactionDataRequiredTest extends TestCase
{
    public function test_it_throws_when_data_is_missing_on_create(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Transaction 'data' is required");

        Transaction::create([
            'gateway' => 'paystack',
            'status' => 'pending',
            'cart_items' => [],
            // 'data' intentionally omitted
        ]);
    }

    public function test_it_throws_when_data_is_empty_array_on_create(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Transaction 'data' is required");

        Transaction::create([
            'gateway' => 'paystack',
            'status' => 'pending',
            'cart_items' => [],
            'data' => [],
        ]);
    }
}
