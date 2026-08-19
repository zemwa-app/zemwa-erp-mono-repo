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
		if (!Schema::hasTable('training_pro_programmes')) {
			Schema::create('training_pro_programmes', function (Blueprint $table) {
				$table->id();
				$table->unsignedInteger('company_id');
				$table->foreign('company_id')->references('id')->on('companies')->onDelete('restrict')->onUpdate('cascade');
				$table->unsignedBigInteger('category_id')->unsigned();
				$table->foreign('category_id')->references('id')->on('training_pro_categories')->onDelete('restrict')->onUpdate('cascade');
				$table->string('name', 256);
				$table->string('description', 256);
				$table->integer('duration')->default(0);
				$table->integer('order')->default(0);
				$table->integer('added_by')->default(1);
				$table->integer('updated_by')->nullable();
				$table->tinyInteger('is_enabled')->default(0);

				$table->timestamps();
			});
		};
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('training_pro_programmes');
    }
};
