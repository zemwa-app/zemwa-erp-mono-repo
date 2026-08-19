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
		Schema::create('lfp_lead_forms', function (Blueprint $table) {
			$table->id();
			$table->unsignedInteger('company_id')->nullable();
			$table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade')->onUpdate('cascade');
			$table->string('name', 256);
			$table->integer('category_id')->nullable();
			//$table->foreign('category_id')->references('id')->on('lfp_categories')->onDelete('cascade')->onUpdate('cascade');
			$table->text('form_fields');
			$table->text('hash');
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
		Schema::dropIfExists('lfp_lead_forms');
	}
};
