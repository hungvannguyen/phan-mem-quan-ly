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
        Schema::table('degree_adjustments', function (Blueprint $table) {
            $table->string('adjusted_field')->after('degree_id')->nullable()->comment('Trường được điều chỉnh');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('degree_adjustments', function (Blueprint $table) {
            $table->dropColumn('adjusted_field');
        });
    }
};
