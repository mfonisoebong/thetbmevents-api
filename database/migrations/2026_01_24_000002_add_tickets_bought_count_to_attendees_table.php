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
            if (!Schema::hasColumn('attendees', 'full_name')) {
                DB::statement("ALTER TABLE `attendees` ADD COLUMN `full_name` VARCHAR(255) NULL AFTER `last_name`");
            }
        } else {
            Schema::table('attendees', function (Blueprint $table) {
                if (!Schema::hasColumn('attendees', 'full_name')) {
                    $table->string('full_name')->nullable();
                }
            });
        }

        Schema::table('attendees', function (Blueprint $table) {
            if (!Schema::hasColumn('attendees', 'tickets_bought_count')) {
                $table->unsignedInteger('tickets_bought_count')->default(1)->after('customer_id');
            }
        });

        if (Schema::hasColumn('attendees', 'first_name') && Schema::hasColumn('attendees', 'last_name')) {
            DB::statement(
                "UPDATE attendees SET full_name = NULLIF(CONCAT_WS(' ', NULLIF(TRIM(first_name), ''), NULLIF(TRIM(last_name), '')), '') WHERE full_name IS NULL"
            );
        }

        Schema::table('attendees', function (Blueprint $table) {
            if (Schema::hasColumn('attendees', 'first_name')) {
                $table->dropColumn('first_name');
            }
            if (Schema::hasColumn('attendees', 'last_name')) {
                $table->dropColumn('last_name');
            }
        });
    }

    public function down(): void
    {
        // Recreate legacy columns.
        Schema::table('attendees', function (Blueprint $table) {
            if (!Schema::hasColumn('attendees', 'first_name')) {
                $table->string('first_name')->nullable();
            }
            if (!Schema::hasColumn('attendees', 'last_name')) {
                $table->string('last_name')->nullable();
            }
        });

        // Best-effort rollback: copy full_name into first_name.
        if (Schema::hasColumn('attendees', 'full_name')) {
            DB::table('attendees')
                ->whereNull('first_name')
                ->update(['first_name' => DB::raw('full_name')]);
        }

        Schema::table('attendees', function (Blueprint $table) {
            if (Schema::hasColumn('attendees', 'tickets_bought_count')) {
                $table->dropColumn('tickets_bought_count');
            }
            if (Schema::hasColumn('attendees', 'full_name')) {
                $table->dropColumn('full_name');
            }
        });
    }
};
