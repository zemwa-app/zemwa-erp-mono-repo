<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('agent_pauses', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
            $table->unsignedInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->string('reason', 255)->nullable();
            $table->unsignedInteger('duration_minutes')->default(30);
            $table->timestamp('started_at');
            $table->timestamp('ends_at')->nullable();
            $table->timestamp('resumed_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'started_at']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('agent_pauses');
    }
};
