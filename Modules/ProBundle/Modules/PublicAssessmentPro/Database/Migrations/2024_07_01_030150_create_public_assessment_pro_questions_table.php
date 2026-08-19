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
        if (!Schema::hasTable('public_assessment_pro_questions')) {
            Schema::create('public_assessment_pro_questions', function (Blueprint $table) {
                $table->id();
                $table->unsignedInteger('company_id');
                $table->foreign('company_id')->references('id')->on('companies')->onDelete('restrict')->onUpdate('cascade');
                $table->unsignedBigInteger('assessment_id')->unsigned();
                $table->foreign('assessment_id')->references('id')->on('public_assessment_pro_assessments')->onDelete('restrict')->onUpdate('cascade');
                $table->unsignedBigInteger('quest_cat_id')->unsigned();
                $table->foreign('quest_cat_id')->references('id')->on('public_assessment_pro_quest_categories')->onDelete('restrict')->onUpdate('cascade');
                $table->string('question', 256);
                $table->string('correct_answer', 256);
                $table->integer('score');
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
        Schema::dropIfExists('public_assessment_pro_questions');
    }
};
