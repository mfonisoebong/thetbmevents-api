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
        Schema::create('organizer_bank_details', function (Blueprint $table) {
            $table->uuid('id')
            ->primary();
            $table->string('user_id');
            $table->string('bank_name')
            ->nullable()
            ->default(null);
            $table->string('account_number')
            ->nullable()
            ->default(null);
            $table->string('account_name')
            ->nullable()
            ->default(null);
            $table->string('swift_code')
            ->nullable()
            ->default(null);
            $table->string('iban')
            ->nullable()
            ->default(null);
            $table->timestamps();
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('organizer_bank_details');
    }
};
