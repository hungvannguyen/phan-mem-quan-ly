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
        Schema::create('majors', function (Blueprint $table) {
            $table->id('major_id');
            $table->string('major_name', 150)->unique();
            $table->string('major_code', 20)->unique();
            $table->timestamps();

            // Preserved field from original trainings table - commented for later processing
            // $table->string('description')->nullable(); // Major description functionality
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('majors');
    }
};
