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
        Schema::create('diploma_blanks', function (Blueprint $table) {
            $table->id('diploma_blank_id');
            $table->string('serial_number', 50)->unique();
            $table->foreignId('type_id')->constrained('diploma_blank_types', 'type_id');
            $table->string('status', 20)->default('InStock'); // 'InStock', 'Issued', 'Recalled', 'Damaged'
            $table->timestamp('import_date')->useCurrent();
            $table->timestamp('issue_date')->nullable();
            $table->timestamp('recall_date')->nullable();
            $table->string('issue_reason')->nullable();
            $table->string('recall_reason')->nullable();
            $table->timestamps();

            // Preserved fields from original diploma_batches table - commented for later processing
            // Batch management functionality might be needed
            // $table->integer('batch_id')->nullable(); // Reference to batch import
            // $table->integer('quality')->default(0); // Quality control status
            // $table->integer('error')->default(0); // Error status
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('diploma_blanks');
    }
};