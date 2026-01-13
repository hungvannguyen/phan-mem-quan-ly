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
        Schema::create('import_logs', function (Blueprint $table) {
            $table->id();
            $table->ulid('user_id');
            $table->foreign('user_id')->references('user_id')->on('users')->onDelete('cascade');
            $table->enum('import_type', ['degree', 'political_theory', 'certificate']);
            $table->string('file_name');
            $table->bigInteger('file_size')->nullable();
            $table->enum('status', ['processing', 'completed', 'completed_with_errors', 'failed'])->default('processing');
            $table->integer('total_rows')->nullable();
            $table->integer('success_rows')->nullable();
            $table->integer('error_rows')->nullable();
            $table->text('error_details')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            // Indexes
            $table->index('import_type');
            $table->index('status');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('import_logs');
    }
};
