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
            $table->unsignedBigInteger('export_id')->nullable()->after('import_id');

            // Foreign key constraint
            $table->foreign('export_id')->references('export_id')->on('diploma_blank_exports')->onDelete('set null');

            // Index
            $table->index('export_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('diploma_blanks', function (Blueprint $table) {
            $table->dropForeign(['export_id']);
            $table->dropIndex(['export_id']);
            $table->dropColumn('export_id');
        });
    }
};