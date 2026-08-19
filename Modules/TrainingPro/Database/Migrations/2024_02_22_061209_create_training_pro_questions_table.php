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
		if (!Schema::hasTable('training_pro_questions')) {
			Schema::create('training_pro_questions', function (Blueprint $table) {
				$table->id();
				$table->unsignedInteger('company_id');
				$table->foreign('company_id')->references('id')->on('companies')->onDelete('restrict')->onUpdate('cascade');
				$table->unsignedBigInteger('assessment_id')->unsigned();
				$table->foreign('assessment_id')->references('id')->on('training_pro_assessments')->onDelete('restrict')->onUpdate('cascade');
				$table->string('question', 256);
				$table->string('correct_answer', 256);
				$table->integer('mark');
				$table->tinyInteger('is_enabled')->default(0);

				$table->timestamps();
			});
		}
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('training_pro_questions');
    }
};
