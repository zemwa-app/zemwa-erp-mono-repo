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
		if (!Schema::hasTable('landing_pages')) {
			Schema::create('landing_pages', function (Blueprint $table) {
				$table->id();
				$table->unsignedInteger('company_id');
				$table->foreign('company_id')->references('id')->on('companies')->onDelete('restrict')->onUpdate('cascade');
				$table->unsignedBigInteger('template_id');
				$table->foreign('template_id')->references('id')->on('landing_page_templates')->onDelete('restrict')->onUpdate('cascade');
				$table->unsignedBigInteger('category_id');
				$table->foreign('category_id')->references('id')->on('landing_page_categories')->onDelete('restrict')->onUpdate('cascade');
				$table->string('name',256);
				$table->longText('template_contents');
				$table->integer('user_id');
				$table->tinyInteger('status')->default(3);

				$table->timestamps();
			});
		}
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('landing_pages');
    }
};
