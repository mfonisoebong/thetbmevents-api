<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'mysql') {
            // Keep column positioning similar to the legacy schema (after last_name).
            DB::statement("ALTER TABLE `customers` ADD COLUMN `full_name` VARCHAR(255) NULL AFTER `last_name`");
        } else {
            Schema::table('customers', function (Blueprint $table) {
                $table->string('full_name')->nullable();
            });
        }

        // Populate full_name from first_name + last_name (ignores empty strings).
        DB::statement(
            "UPDATE customers SET full_name = NULLIF(CONCAT_WS(' ', NULLIF(TRIM(first_name), ''), NULLIF(TRIM(last_name), '')), '') WHERE full_name IS NULL"
        );

        Schema::table('customers', function (Blueprint $table) {
            if (Schema::hasColumn('customers', 'first_name')) {
                $table->dropColumn('first_name');
            }
            if (Schema::hasColumn('customers', 'last_name')) {
                $table->dropColumn('last_name');
            }
            if (Schema::hasColumn('customers', 'phone_dial_code')) {
                $table->dropColumn('phone_dial_code');
            }

            $table->integer('tickets_bought_count')->default(1)->after('phone_number');
        });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            if (!Schema::hasColumn('customers', 'first_name')) {
                $table->string('first_name')->nullable();
            }
            if (!Schema::hasColumn('customers', 'last_name')) {
                $table->string('last_name')->nullable();
            }
            if (!Schema::hasColumn('customers', 'phone_dial_code')) {
                $table->string('phone_dial_code')->nullable();
            }
        });

        // Best-effort rollback: put full_name back into first_name.
        if (Schema::hasColumn('customers', 'full_name')) {
            DB::table('customers')
                ->whereNull('first_name')
                ->update(['first_name' => DB::raw('full_name')]);
        }

        Schema::table('customers', function (Blueprint $table) {
            if (Schema::hasColumn('customers', 'full_name')) {
                $table->dropColumn('full_name');
            }
        });
    }
};
