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
        Schema::create('degree_reissues', function (Blueprint $table) {
            $table->id('reissue_id');
            $table->foreignId('degree_id')->constrained('degrees', 'degree_id')->onDelete('cascade');
            $table->string('old_registration_number', 50)->comment('Số hiệu văn bằng cũ');
            $table->string('new_registration_number', 50)->comment('Số hiệu văn bằng mới');
            $table->text('edit_content')->comment('Nội dung chỉnh sửa');
            $table->string('recall_decision', 100)->comment('QĐ thu hồi, hủy bỏ và cấp lại');
            $table->date('decision_date')->comment('Ngày quyết định');
            $table->text('notes')->nullable()->comment('Ghi chú thêm');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('degree_reissues');
    }
};
