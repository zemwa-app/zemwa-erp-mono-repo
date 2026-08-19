<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('agent_activity_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
            $table->unsignedInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->string('app_name', 255);
            $table->string('process_name', 255)->nullable();
            $table->string('window_title', 500)->nullable();
            $table->string('url', 2048)->nullable();
            $table->string('category', 50)->default('neutral');
            $table->timestamp('started_at');
            $table->timestamp('ended_at')->nullable();
            $table->unsignedInteger('duration_seconds')->default(0);
            $table->timestamps();

            $table->index(['user_id', 'started_at']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('agent_activity_logs');
    }
};
