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
        Schema::table('degree_reissues', function (Blueprint $table) {
            // Drop old columns
            $table->dropColumn(['old_registration_number', 'new_registration_number']);

            // Add new columns for diploma blanks
            $table->foreignId('old_diploma_blank_id')->after('degree_id')->nullable()
                ->constrained('diploma_blanks', 'diploma_blank_id')->comment('Phôi văn bằng cũ');
            $table->foreignId('new_diploma_blank_id')->after('old_diploma_blank_id')->nullable()
                ->constrained('diploma_blanks', 'diploma_blank_id')->comment('Phôi văn bằng mới');

            // Add columns for recall/destroy actions
            $table->boolean('is_recalled')->default(false)->after('recall_decision')
                ->comment('Đã thu hồi phôi cũ hay chưa');
            $table->boolean('is_destroyed')->default(false)->after('is_recalled')
                ->comment('Đã hủy phôi cũ hay chưa');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('degree_reissues', function (Blueprint $table) {
            // Drop new columns
            $table->dropForeign(['old_diploma_blank_id']);
            $table->dropForeign(['new_diploma_blank_id']);
            $table->dropColumn(['old_diploma_blank_id', 'new_diploma_blank_id', 'is_recalled', 'is_destroyed']);

            // Add back old columns
            $table->string('old_registration_number', 50)->after('degree_id');
            $table->string('new_registration_number', 50)->after('old_registration_number');
        });
    }
};
