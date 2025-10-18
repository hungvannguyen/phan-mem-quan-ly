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
            $table->string('major_name', 255)->nullable()->after('decision_number')->comment('Tên chuyên ngành');
            $table->text('notes')->nullable()->after('major_name')->comment('Ghi chú thêm');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('degrees', function (Blueprint $table) {
            $table->dropColumn(['major_name', 'notes']);
        });
    }
};
