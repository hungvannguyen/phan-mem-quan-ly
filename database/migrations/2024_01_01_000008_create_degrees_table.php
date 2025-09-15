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
            $table->foreignId('diploma_blank_id')->unique()->constrained('diploma_blanks', 'diploma_blank_id');
            $table->string('registration_number', 50)->unique();
            $table->date('granting_date');
            $table->integer('graduation_year');
            $table->string('ranking', 50)->nullable();
            $table->string('decision_number', 50)->nullable();
            $table->timestamps();
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
