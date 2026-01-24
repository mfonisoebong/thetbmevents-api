<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('new_purchased_tickets', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('ticket_id');
            $table->foreignUuid('transaction_id');
            $table->foreignId('attendee_id');
            $table->boolean('used')->default(false);
            $table->timestamps();

            $table->foreign('ticket_id')->references('id')->on('tickets')->onDelete('cascade');
            $table->foreign('transaction_id')->references('id')->on('transactions')->onDelete('cascade');
            $table->foreign('attendee_id')->references('id')->on('attendees')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('new_purchased_tickets');
    }
};
