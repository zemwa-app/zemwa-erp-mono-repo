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
		if (!Schema::hasTable('training_pro_assessment_logs')) {
			Schema::create('training_pro_assessment_logs', function (Blueprint $table) {
				$table->id();
				$table->unsignedInteger('company_id');
				$table->foreign('company_id')->references('id')->on('companies')->onDelete('restrict')->onUpdate('cascade');
				$table->unsignedInteger('user_id')->unsigned();
				$table->foreign('user_id')->references('id')->on('users')->onDelete('restrict')->onUpdate('cascade');
				$table->unsignedBigInteger('assessment_id')->unsigned();
				$table->foreign('assessment_id')->references('id')->on('training_pro_assessments')->onDelete('restrict')->onUpdate('cascade');

				$table->jsonb('assessment_data')->nullable();

				$table->timestamp('started_at');
				$table->timestamp('finished_at')->nullable();

				$table->integer('duration_took')->default(0);
				$table->integer('max_score')->nullable()->default(0);
				$table->integer('min_score')->nullable()->default(0);
				$table->integer('score')->nullable()->default(0);
				$table->double('score_percentage')->nullable()->default(0);
				$table->integer('assessment_status')->default(0);

				$table->timestamps();
			});
		}
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		Schema::dropIfExists('training_pro_assessment_logs');
	}
};
