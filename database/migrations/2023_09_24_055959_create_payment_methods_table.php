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
        Schema::create('payment_methods', function (Blueprint $table) {
            $table->id();
            $table->enum('gateway', ['vella', 'paystack']);
            $table->string('vella_tag')
            ->nullable()
            ->default(null);
            $table->string('vella_webhook_url')
            ->nullable()
            ->default(null);
            $table->string('vella_test_key')
            ->nullable()
            ->default(null);
            $table->string('paystack_test_key')
            ->nullable()
            ->default(null);
            $table->string('vella_live_key')
            ->nullable()
            ->default(null);
            $table->string('paystack_webhook_url')
            ->nullable()
            ->default(null);
            $table->string('paystack_live_key')
            ->nullable()
            ->default(null);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_methods');
    }
};
