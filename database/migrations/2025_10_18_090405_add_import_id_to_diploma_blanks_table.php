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
            // Thêm cột import_id để liên kết với diploma_blank_import
            $table->unsignedBigInteger('import_id')->nullable()->after('type_id');

            // Tạo foreign key constraint
            $table->foreign('import_id')->references('id')->on('diploma_blank_import')->onDelete('set null');

            // Thêm index để tối ưu performance
            $table->index('import_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('diploma_blanks', function (Blueprint $table) {
            // Xóa foreign key constraint trước
            $table->dropForeign(['import_id']);

            // Xóa index
            $table->dropIndex(['import_id']);

            // Xóa cột import_id
            $table->dropColumn('import_id');
        });
    }
};
