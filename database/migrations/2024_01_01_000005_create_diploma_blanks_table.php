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
            $table->unsignedBigInteger('import_id')->nullable();
            $table->foreign('import_id')->references('id')->on('diploma_blank_import')->onDelete('set null');
            $table->unsignedBigInteger('export_id')->nullable();
            $table->foreign('export_id')->references('export_id')->on('diploma_blank_exports')->onDelete('set null');
            $table->string('status', 20)->default('InStock'); // 'InStock', 'Issued', 'Recalled', 'Damaged'
            $table->timestamp('import_date')->useCurrent();
            $table->timestamp('issue_date')->nullable();
            $table->timestamp('recall_date')->nullable();
            $table->string('issue_reason')->nullable();
            $table->string('recall_reason')->nullable();
            $table->unsignedBigInteger('damage_reason_id')->nullable();
            $table->foreign('damage_reason_id')->references('id')->on('damage_reasons')->onDelete('set null');
            $table->text('damage_description')->nullable();
            $table->timestamp('damage_date')->nullable();
            $table->timestamps();

            // Indexes for performance optimization
            $table->index('import_id');
            $table->index('export_id');
            $table->index(['type_id', 'status'], 'idx_type_status');
            $table->index(['status'], 'idx_status');
            $table->index(['status', 'type_id', 'serial_number'], 'idx_status_type_serial');
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
