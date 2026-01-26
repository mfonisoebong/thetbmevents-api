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
        Schema::create('tickets', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('organizer_id');
            $table->string('event_id');
            $table->string('name');
            $table->dateTime('selling_start_date_time');
            $table->dateTime('selling_end_date_time');
            $table->text('description')->nullable();
            $table->enum('currency', ['NGN', 'USD'])->default('NGN');
            $table->unsignedDouble('price');
            $table->bigInteger('quantity')->default(0);
            $table->bigInteger('sold')->default(0);
            $table->foreign('event_id')->references('id')->on('events')->onDelete('cascade');
            $table->foreign('organizer_id')->references('id')->on('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
