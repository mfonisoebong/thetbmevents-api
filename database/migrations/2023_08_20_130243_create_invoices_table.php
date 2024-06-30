<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->uuid('id')
                ->primary();
            $table->unsignedBigInteger('customer_id');
            $table->enum('payment_method', ['vella', 'paystack']);
            $table
                ->enum('payment_status', ['pending', 'success', 'failed', 'reversed'])
                ->default('pending');
            $table->json('cart_items');
            $table->string('transaction_reference');
            $table->timestamps();

            $table->foreign('customer_id')
                ->references('id')
                ->on('customers')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
