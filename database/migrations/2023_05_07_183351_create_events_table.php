<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('events', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('user_id');
            $table->string('title');
            $table->string('alias');
            $table->longText('description');
            $table->string('event_link')->nullable();
            $table->enum('currency', ['USD', 'NGN'])->default('NGN');
            $table->string('location_tips')->nullable();
            $table->string('timezone');
            $table->enum('type', ['physical', 'virtual']);
            $table->string('location')->nullable();
            $table->integer('attendees')->default(0);
            $table->date('event_date');
            $table->time('event_time')->nullable();
            $table->string('links_instagram')->nullable();
            $table->string('links_twitter')->nullable();
            $table->string('links_facebook')->nullable();
            $table->boolean('undisclose_location');
            $table->longText('image_url')->nullable();
            $table->string('category')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
