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
        if (!Schema::hasTable('public_assessment_answers')) {
            Schema::create('public_assessment_answers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('public_assessment_id')->unsigned();
            $table->foreign('public_assessment_id')->references('id')->on('public_assessments')->onDelete('restrict')->onUpdate('cascade');
            $table->unsignedBigInteger('question_id')->unsigned();
            $table->foreign('question_id')->references('id')->on('public_assessment_pro_questions')->onDelete('restrict')->onUpdate('cascade');
            $table->string('answer_code');
            $table->timestamps();
        });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('public_assessment_answers');
    }
};
