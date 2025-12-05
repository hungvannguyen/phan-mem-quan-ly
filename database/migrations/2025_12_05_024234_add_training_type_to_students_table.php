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
        Schema::table('students', function (Blueprint $table) {
            $table->enum('training_type', ['Chính quy', 'Liên thông', 'Từ xa', 'Vừa làm vừa học'])
                ->default('Chính quy')
                ->after('status')
                ->comment('Hình thức đào tạo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropColumn('training_type');
        });
    }
};
