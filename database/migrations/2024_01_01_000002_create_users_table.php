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
        Schema::create('users', function (Blueprint $table) {
            $table->ulid('user_id')->primary();
            $table->string('username', 50)->unique();
            $table->string('password_hash', 255);
            $table->string('full_name', 100);
            $table->string('email', 100)->unique()->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();

            // Preserved fields from original migration - commented for later processing
            // $table->timestamp('email_verified_at')->nullable(); // Email verification functionality
            // $table->rememberToken(); // Remember me token for login
            // $table->tinyInteger('is_admin')->default(0); // Admin flag - replaced by role system

            // Two-factor authentication fields from add_two_factor_columns migration
            // $table->text('two_factor_secret')->nullable(); // 2FA secret key
            // $table->text('two_factor_recovery_codes')->nullable(); // 2FA recovery codes
            // $table->timestamp('two_factor_confirmed_at')->nullable(); // 2FA confirmation timestamp
        });

        // Keep password reset tokens table as it's Laravel standard functionality
        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        // Keep sessions table as it's Laravel standard functionality
        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignUlid('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
