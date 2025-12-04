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
        Schema::create('permissions', function (Blueprint $table) {
            $table->id('permission_id');
            $table->string('name')->unique(); // e.g., 'diploma-blanks.create', 'diplomas.delete'
            $table->string('display_name'); // e.g., 'Thêm phôi văn bằng'
            $table->string('category'); // e.g., 'diploma-blanks', 'diplomas', 'users'
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('permissions');
    }
};
