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
        if (!Schema::hasTable('public_assessments')) {
            Schema::create('public_assessments', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('company_id');
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('restrict')->onUpdate('cascade');
            $table->unsignedBigInteger('assessment_id')->unsigned();
            $table->foreign('assessment_id')->references('id')->on('public_assessment_pro_assessments')->onDelete('restrict')->onUpdate('cascade');
            $table->string('participant_name');
            $table->string('participant_phone');
            $table->string('participant_email')->nullable();
			$table->integer('total_score')->nullable()->default(0);
			$table->double('scored_mark')->nullable()->default(0);
            $table->string('grade')->nullable();
            $table->string('rank')->nullable();
            $table->dateTime('submitted_on');

            $table->timestamps();
            });

            

        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('public_assessments');
    }
};
