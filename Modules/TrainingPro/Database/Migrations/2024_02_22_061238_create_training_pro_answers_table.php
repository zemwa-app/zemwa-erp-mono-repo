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
		if (!Schema::hasTable('training_pro_answers')) {
			Schema::create('training_pro_answers', function (Blueprint $table) {
				$table->id();
				$table->unsignedInteger('company_id');
				$table->foreign('company_id')->references('id')->on('companies')->onDelete('restrict')->onUpdate('cascade');
				$table->unsignedBigInteger('question_id')->unsigned();
				$table->foreign('question_id')->references('id')->on('training_pro_questions')->onDelete('restrict')->onUpdate('cascade');
				$table->string('ans_code', 8);
				$table->string('option_text', 128);

				$table->timestamps();
			});
		}
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('training_pro_answers');
    }
};
