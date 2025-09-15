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
        Schema::create('students', function (Blueprint $table) {
            $table->ulid('student_id')->primary();
            $table->string('student_code', 20)->unique();
            $table->string('full_name', 100);
            $table->date('date_of_birth');
            $table->string('class_name', 50);
            $table->foreignId('major_id')->constrained('majors', 'major_id');
            $table->timestamps();

            // Preserved fields from original students table - commented for later processing
            // $table->string('place_of_birth'); // Place of birth information
            // $table->tinyInteger('gender'); // Gender information (using enum in model)
            // $table->string('nation'); // Nation information
            // $table->string('nationality'); // Nationality information
            // $table->string('number_in_the_book')->unique(); // Book registration number - might be moved to degrees table
            // $table->tinyInteger('status')->default(0); // Student status (using enum in model)
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
