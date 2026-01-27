<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('payment_methods', function (Blueprint $table) {
            $table->dropColumn([
                'vella_tag',
                'vella_webhook_url',
                'vella_test_key',
                'vella_live_key',
                'paystack_webhook_url',
            ]);

            $table->string('flutterwave_test_key')->nullable();
            $table->string('flutterwave_live_key')->nullable();

            $table->enum('gateway', ['flutterwave', 'paystack', 'chainpal'])->change();
        });
    }

    public function down(): void
    {
        Schema::table('payment_methods', function (Blueprint $table) {
            $table->dropColumn(['flutterwave_test_key', 'flutterwave_live_key']);

            $table->string('vella_tag')->nullable();
            $table->string('vella_webhook_url')->nullable();
            $table->string('vella_test_key')->nullable();
            $table->string('vella_live_key')->nullable();
            $table->string('paystack_webhook_url')->nullable();

            $table->enum('gateway', ['paystack', 'vella'])->change();
        });
    }
};
