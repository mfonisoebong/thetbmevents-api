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
            $table->string('title');
            $table->string('alias');
            $table->longText('description');
            $table->string('event_link')
                ->nullable()
                ->default(null);
            $table
                ->enum('currency', ['USD', 'NGN'])
                ->default('NGN');
            $table->string('location_tips')
                ->nullable()
                ->default(null);
            $table->string('timezone');
            $table->enum('type', ['physical', 'virtual']);
            $table->string('location')
                ->nullable()
                ->default(null);
            $table->string('user_id');
            $table->integer('attendees')->default(0);
            $table->date('event_date');
            $table->time('event_time')
                ->nullable()
            ->default(null);
            $table->string('links_instagram')
                ->nullable()
                ->default(null);
            $table->string('links_twitter')
                ->nullable()
                ->default(null);
            $table->string('links_facebook')
                ->nullable()
                ->default(null);

            $table->boolean('undisclose_location');
            $table->longText('logo')->default(null)->nullable();
            $table->string('categories')->nullable();
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
        Schema::dropIfExists('events');
    }
};
