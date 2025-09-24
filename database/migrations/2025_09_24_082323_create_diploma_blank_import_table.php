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
        Schema::create('diploma_blank_import', function (Blueprint $table) {
            $table->id();

            // Thông tin cơ bản về lần nhập
            $table->unsignedBigInteger('type_id')->comment('ID loại văn bằng/chứng chỉ');
            $table->string('document_reference', 500)->comment('Văn bản đề xuất cấp phôi');
            $table->date('issue_date')->comment('Ngày cấp phôi');
            $table->date('import_date')->comment('Ngày nhập vào hệ thống');

            // Thông tin về số lượng và serial
            $table->integer('total_quantity')->comment('Tổng số lượng phôi được nhập');
            $table->string('prefix', 50)->nullable()->comment('Trường cố định 1 của serial');
            $table->string('suffix', 50)->nullable()->comment('Trường cố định 2 của serial');
            $table->string('from_number', 20)->comment('Số bắt đầu');
            $table->string('to_number', 20)->comment('Số kết thúc');

            // Theo dõi tiến trình xử lý
            $table->tinyInteger('status')
                ->default(0)
                ->comment('Trạng thái xử lý: 0=pending(chờ xử lý), 1=processing(đang xử lý), 2=completed(hoàn thành), 3=failed(lỗi)');
            $table->integer('processed_count')->default(0)->comment('Số lượng đã xử lý/lưu vào database');
            $table->string('last_processed_serial', 100)->nullable()->comment('Serial cuối cùng đã được xử lý');

            // Thông tin bổ sung
            $table->text('error_message')->nullable()->comment('Thông báo lỗi nếu có');
            $table->timestamp('started_at')->nullable()->comment('Thời điểm bắt đầu xử lý');
            $table->timestamp('completed_at')->nullable()->comment('Thời điểm hoàn thành');

            $table->timestamps();

            // Indexes
            $table->index('status');
            $table->index('import_date');
            $table->index(['status', 'import_date']);

            // Foreign key
            $table->foreign('type_id')->references('type_id')->on('diploma_blank_types')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('diploma_blank_import');
    }
};
