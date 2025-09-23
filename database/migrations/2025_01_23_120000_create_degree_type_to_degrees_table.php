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
            // Thêm cột degree_type để phân loại văn bằng
            $table->enum('degree_type', ['bachelor', 'master', 'doctor', 'certificate'])
                ->default('bachelor')
                ->after('student_id')
                ->comment('Loại văn bằng: bachelor=Cử nhân, master=Thạc sĩ, doctor=Tiến sĩ, certificate=Chứng chỉ');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('degrees', function (Blueprint $table) {
            $table->dropColumn('degree_type');
        });
    }
};