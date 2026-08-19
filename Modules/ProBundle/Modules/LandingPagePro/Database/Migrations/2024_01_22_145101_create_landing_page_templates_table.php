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
		if (!Schema::hasTable('landing_page_templates')) {
			Schema::create('landing_page_templates', function (Blueprint $table) {
				$table->id();
				$table->text('name');
				$table->text('thumbnail');
				$table->text('associated_packages');
				$table->longText('template_contents');
				$table->integer('user_id');
				$table->tinyInteger('status')->default(1);

				$table->timestamps();
			});
		};
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('landing_page_templates');
    }
};
