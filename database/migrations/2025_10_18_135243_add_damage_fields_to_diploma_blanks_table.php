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
            $table->unsignedBigInteger('damage_reason_id')->nullable()->after('recall_reason');
            $table->text('damage_description')->nullable()->after('damage_reason_id');
            $table->timestamp('damage_date')->nullable()->after('damage_description');

            $table->foreign('damage_reason_id')->references('id')->on('damage_reasons')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('diploma_blanks', function (Blueprint $table) {
            $table->dropForeign(['damage_reason_id']);
            $table->dropColumn(['damage_reason_id', 'damage_description', 'damage_date']);
        });
    }
};