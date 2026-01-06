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
        Schema::create('degree_adjustments', function (Blueprint $table) {
            $table->id('adjustment_id');
            $table->foreignId('degree_id')->constrained('degrees', 'degree_id')->onDelete('cascade');
            $table->text('adjustment_content')->comment('Nội dung điều chỉnh');
            $table->string('decision_number', 100)->nullable()->comment('Số quyết định điều chỉnh');
            $table->date('decision_date')->nullable()->comment('Ngày quyết định điều chỉnh');
            $table->foreignUlid('adjusted_by')->nullable()->constrained('users', 'user_id')->onDelete('set null')->comment('Người thực hiện điều chỉnh');
            $table->timestamps();

            // Index for better query performance
            $table->index('degree_id');
            $table->index('decision_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('degree_adjustments');
    }
};
