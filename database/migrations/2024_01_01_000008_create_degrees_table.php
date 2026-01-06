<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('degrees', function (Blueprint $table) {
            $table->id('degree_id');
            $table->foreignUlid('student_id')->constrained('students', 'student_id');
            $table->enum('degree_type', ['bachelor', 'master', 'doctor', 'certificate'])
                ->default('bachelor')
                ->comment('Loại văn bằng: bachelor=Cử nhân, master=Thạc sĩ, doctor=Tiến sĩ, certificate=Chứng chỉ');
            $table->foreignId('diploma_blank_id')->nullable()->constrained('diploma_blanks', 'diploma_blank_id');
            $table->string('registration_number', 50)->unique();
            $table->date('granting_date');
            $table->integer('graduation_year');
            $table->string('ranking', 50)->nullable();
            $table->string('decision_number', 50)->nullable();
            $table->string('council_decision_number', 50)->nullable()->comment('Số QĐ thành lập hội đồng đánh giá luận án');
            $table->date('council_decision_date')->nullable()->comment('Ngày QĐ thành lập hội đồng đánh giá luận án');
            $table->string('graduation_decision_number', 50)->nullable()->comment('Số QĐ công nhận tốt nghiệp');
            $table->date('graduation_decision_date')->nullable()->comment('Ngày QĐ công nhận tốt nghiệp');
            $table->unsignedBigInteger('major_id')->nullable();
            $table->foreign('major_id')->references('major_id')->on('majors')->onDelete('set null');
            $table->string('major_name', 255)->nullable()->comment('Tên chuyên ngành');
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
        Schema::dropIfExists('degrees');
    }
};
