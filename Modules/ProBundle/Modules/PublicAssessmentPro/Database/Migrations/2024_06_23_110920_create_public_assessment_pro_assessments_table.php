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
		if (!Schema::hasTable('public_assessment_pro_assessments')) {
			Schema::create('public_assessment_pro_assessments', function (Blueprint $table) {
				$table->id();
				$table->unsignedInteger('company_id');
				$table->foreign('company_id')->references('id')->on('companies')->onDelete('restrict')->onUpdate('cascade');
				$table->integer('product_id');
                $table->integer('assessment_type')->default(0); //'scored'=>0,'scoreless'=>1,'rated'=>2
				$table->string('assessment_name', 256);
                $table->text('description');
				$table->integer('submission_limit');
				$table->integer('max_score');
				$table->integer('min_score');
				$table->integer('view_count')->default(0);
				$table->integer('added_by')->default(1);
				$table->integer('updated_by')->nullable();
				$table->tinyInteger('status')->default(0);

				$table->timestamps();
			});
		}
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('public_assessment_pro_assessments');
    }
};
