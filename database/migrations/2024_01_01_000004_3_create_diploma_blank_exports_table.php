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
        Schema::create('diploma_blank_exports', function (Blueprint $table) {
            $table->id('export_id');
            $table->unsignedBigInteger('type_id');
            $table->string('course')->nullable();
            $table->integer('graduation_year');
            $table->string('decision_number');
            $table->date('issue_date');
            $table->integer('quantity_requested');
            $table->integer('quantity_exported');
            $table->timestamp('export_date');
            $table->json('export_ranges');
            $table->text('notes')->nullable();
            $table->ulid('exported_by');
            $table->timestamps();

            // Foreign key constraints
            $table->foreign('type_id')->references('type_id')->on('diploma_blank_types')->onDelete('cascade');
            $table->foreign('exported_by')->references('user_id')->on('users')->onDelete('cascade');

            // Indexes
            $table->index(['type_id', 'graduation_year']);
            $table->index('export_date');
            $table->index('exported_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('diploma_blank_exports');
    }
};
