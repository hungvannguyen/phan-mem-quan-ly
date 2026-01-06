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
            $table->text('old_value')->after('adjusted_field')->nullable()->comment('Giá trị cũ');
            $table->text('new_value')->after('old_value')->nullable()->comment('Giá trị mới');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('degree_adjustments', function (Blueprint $table) {
            $table->dropColumn(['old_value', 'new_value']);
        });
    }
};
