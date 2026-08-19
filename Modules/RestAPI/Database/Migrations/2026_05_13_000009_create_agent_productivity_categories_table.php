<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('agent_productivity_categories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
            $table->string('pattern', 255);
            $table->string('category', 50)->default('neutral');
            $table->string('note', 500)->nullable();
            $table->timestamps();

            $table->index(['company_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('agent_productivity_categories');
    }
};
