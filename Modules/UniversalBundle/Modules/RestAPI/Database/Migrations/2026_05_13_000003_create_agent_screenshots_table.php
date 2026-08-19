<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('agent_screenshots', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
            $table->unsignedInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->timestamp('captured_at');
            $table->string('file_path', 500);
            $table->string('thumbnail_path', 500)->nullable();
            $table->string('active_app', 255)->nullable();
            $table->string('window_title', 500)->nullable();
            $table->string('category', 50)->default('neutral');
            $table->unsignedTinyInteger('display_idx')->default(0);
            $table->boolean('is_triggered')->default(false);
            $table->unsignedInteger('file_size')->default(0);
            $table->timestamps();

            $table->index(['user_id', 'captured_at']);
            $table->index(['company_id', 'user_id', 'captured_at']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('agent_screenshots');
    }
};
