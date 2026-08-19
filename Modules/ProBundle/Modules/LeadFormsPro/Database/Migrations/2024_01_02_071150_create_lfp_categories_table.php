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
		Schema::create('lfp_categories', function (Blueprint $table) {
			$table->id();
			$table->unsignedInteger('company_id')->nullable();
			$table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade')->onUpdate('cascade');
			$table->string('name', 256);
			$table->integer('added_by')->default(1);
			$table->integer('updated_by')->nullable();
			$table->tinyInteger('status')->default(0);

			$table->timestamps();
		});
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		Schema::dropIfExists('lfp_categories');
	}
};
