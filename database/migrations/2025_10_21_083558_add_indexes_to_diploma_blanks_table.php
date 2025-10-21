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
        Schema::table('diploma_blanks', function (Blueprint $table) {
            // Add composite index for optimized queries (type + status + serial_number)
            $table->index(['type_id', 'status'], 'idx_type_status');

            // Add index on status column for quick status-based filtering
            $table->index(['status'], 'idx_status');

            // Add composite index for export queries (status + type + serial pattern)
            $table->index(['status', 'type_id', 'serial_number'], 'idx_status_type_serial');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('diploma_blanks', function (Blueprint $table) {
            $table->dropIndex('idx_type_status');
            $table->dropIndex('idx_status');
            $table->dropIndex('idx_status_type_serial');
        });
    }
};
