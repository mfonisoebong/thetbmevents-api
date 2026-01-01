<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('payouts', function (Blueprint $table) {
            $table->id();
            $table->string('user_id')->nullable();
            $table->decimal('amount');
            $table->string('organizer_bank_details_id')->nullable();
            $table->enum('status', ['approved', 'pending', 'declined', 'paid'])->default('pending');
            $table->timestamps();

            $table->foreign('organizer_bank_details_id')
                ->references('id')
                ->on('organizer_bank_details')
                ->onDelete('SET NULL');
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('SET NULL');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payouts');
    }
};
