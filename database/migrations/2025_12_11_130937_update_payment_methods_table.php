<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('payment_methods', function (Blueprint $table) {
            // remove legacy Vella / Paystack webhook/test/live columns
            $table->dropColumn([
                'vella_tag',
                'vella_webhook_url',
                'vella_test_key',
                'vella_live_key',
                'paystack_webhook_url',
            ]);

            // add Flutterwave keys
            $table->string('flutterwave_test_key')->nullable();
            $table->string('flutterwave_live_key')->nullable();
        });

        // Convert existing rows that used `vella` to `flutterwave` so the enum change succeeds.
        DB::table('payment_methods')
            ->where('gateway', 'vella')
            ->update(['gateway' => 'flutterwave']);

        // Update enum values for `gateway` to include flutterwave instead of vella.
        // This uses a raw statement suitable for MySQL. If you use SQLite or another
        // driver you'll need to adjust this step accordingly.
        DB::statement("ALTER TABLE `payment_methods` MODIFY `gateway` ENUM('flutterwave','paystack') NOT NULL");
    }

    public function down(): void
    {
        // Revert schema changes: remove flutterwave keys, add back the dropped columns,
        // and change enum back to the original values.
        Schema::table('payment_methods', function (Blueprint $table) {
            $table->dropColumn(['flutterwave_test_key', 'flutterwave_live_key']);

            $table->string('vella_tag')
                ->nullable()
                ->default(null);
            $table->string('vella_webhook_url')
                ->nullable()
                ->default(null);
            $table->string('vella_test_key')
                ->nullable()
                ->default(null);
            $table->string('vella_live_key')
                ->nullable()
                ->default(null);
            $table->string('paystack_webhook_url')
                ->nullable()
                ->default(null);
        });

        // Convert rows that used `flutterwave` back to `vella` before reverting the enum.
        DB::table('payment_methods')
            ->where('gateway', 'flutterwave')
            ->update(['gateway' => 'vella']);

        DB::statement("ALTER TABLE `payment_methods` MODIFY `gateway` ENUM('vella','paystack') NOT NULL");
    }
};
