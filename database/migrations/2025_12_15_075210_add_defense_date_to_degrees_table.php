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
        Schema::table('degrees', function (Blueprint $table) {
            // Add defense_date column for Master and PhD degrees
            $table->date('defense_date')
                ->nullable()
                ->after('granting_date')
                ->comment('Ngày bảo vệ luận án/luận văn (dành cho Thạc sĩ và Tiến sĩ)');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('degrees', function (Blueprint $table) {
            $table->dropColumn('defense_date');
        });
    }
};
