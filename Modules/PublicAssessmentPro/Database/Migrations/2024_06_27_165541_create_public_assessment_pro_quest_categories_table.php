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
        if (!Schema::hasTable('public_assessment_pro_quest_categories')) {
            Schema::create('public_assessment_pro_quest_categories', function (Blueprint $table) {
                $table->id();
                $table->unsignedInteger('company_id');
                $table->foreign('company_id')->references('id')->on('companies')->onDelete('restrict')->onUpdate('cascade');
                $table->string('category_name', 256);
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
        Schema::dropIfExists('public_assessment_pro_quest_categories');
    }
};
