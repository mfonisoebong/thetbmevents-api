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
        Schema::create('users', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('first_name')
            ->nullable()
            ->default(null);
            $table->string('last_name')
            ->nullable()
            ->default(null);
            $table->string('buisness_name')
            ->nullable()
            ->default(null);
            $table
            ->boolean('completed_profile')
            ->default(true);
            $table->string('avatar')
            ->nullable()
            ->default(null);
            $table
            ->enum('auth_provider', ['local', 'google'])
            ->default('local');
            $table
            ->string('email')
            ->unique();
            $table
            ->enum('role', ['organizer', 'admin']);
            $table
                ->enum('admin_role', ['super_admin', 'support', 'manager', null])
                ->default(null)
                ->nullable();

            $table
            ->string('country')
            ->nullable()
            ->default(null);
            $table
            ->string('phone_number')
            ->nullable()
            ->default(null);
            $table
            ->string('phone_dial_code')
            ->nullable()
            ->default(null);
            $table
            ->enum('account_state',['active', 'pending', 'blocked'])
            ->default('pending');
            $table
            ->timestamp('email_verified_at')
            ->nullable()
                ->default(now());
            $table
            ->string('password', 200)
            ->nullable()
            ->default(null);
            $table
            ->rememberToken();
            $table
            ->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
