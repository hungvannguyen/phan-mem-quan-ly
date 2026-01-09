<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     * Bảng tổng quát để lưu lịch sử thay đổi cho tất cả các entity (students, degrees, etc.)
     */
    public function up(): void
    {
        // Drop bảng degree_adjustments cũ nếu tồn tại (trước khi tạo view)
        Schema::dropIfExists('degree_adjustments');

        // Tạo bảng change_logs mới
        Schema::create('change_logs', function (Blueprint $table) {
            $table->id('log_id');

            // Thông tin entity được thay đổi (polymorphic relationship)
            $table->string('entity_type', 100)->comment('Loại entity (Student, Degree, etc.)');
            $table->string('entity_id', 100)->comment('ID của entity (hỗ trợ cả ULID và integer)');

            // Thông tin thay đổi
            $table->string('changed_field', 100)->nullable()->comment('Trường được thay đổi');
            $table->text('old_value')->nullable()->comment('Giá trị cũ');
            $table->text('new_value')->nullable()->comment('Giá trị mới');
            $table->text('change_description')->comment('Mô tả thay đổi/Nội dung điều chỉnh');

            // Thông tin quyết định (nếu có)
            $table->string('decision_number', 100)->nullable()->comment('Số quyết định thay đổi');
            $table->date('decision_date')->nullable()->comment('Ngày quyết định thay đổi');

            // Thông tin người thực hiện
            $table->foreignUlid('changed_by')->nullable()->constrained('users', 'user_id')->onDelete('set null')->comment('Người thực hiện thay đổi');

            // Metadata
            $table->string('action_type', 50)->default('update')->comment('Loại hành động: create, update, delete, restore');
            $table->string('ip_address', 45)->nullable()->comment('Địa chỉ IP');
            $table->text('user_agent')->nullable()->comment('User Agent');
            $table->json('additional_data')->nullable()->comment('Dữ liệu bổ sung (JSON)');

            $table->timestamps();

            // Indexes for better query performance
            $table->index(['entity_type', 'entity_id'], 'entity_index');
            $table->index('changed_by');
            $table->index('decision_date');
            $table->index('action_type');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('change_logs');
    }
};
