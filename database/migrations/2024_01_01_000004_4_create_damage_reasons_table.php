<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * Note: This table was preserved from original schema as it may be needed
     * for tracking damage reasons for diploma blanks
     */
    public function up(): void
    {
        Schema::create('damage_reasons', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('description')->nullable();
            $table->timestamps();

            // This table might be used to categorize damage reasons
            // for diploma_blanks with status 'Damaged'
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('damage_reasons');
    }
};
