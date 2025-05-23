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
		Schema::create('diploma_batches', function (Blueprint $table) {
			$table->id();
			$table->date('import_date');
			$table->integer('quality')->default(0);
			$table->integer('remaining')->default(0);
			$table->integer('error')->default(0);
			$table->string('description')->nullable();
			$table->timestamps();
		});
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		Schema::dropIfExists('diploma_batches');
	}
};
