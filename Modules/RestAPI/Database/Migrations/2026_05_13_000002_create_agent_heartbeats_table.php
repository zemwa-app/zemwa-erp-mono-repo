<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('agent_heartbeats', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
            $table->unsignedInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->string('agent_version', 20)->nullable();
            $table->string('os', 50)->nullable();
            $table->string('os_version', 50)->nullable();
            $table->string('hostname', 255)->nullable();
            $table->boolean('is_idle')->default(false);
            $table->boolean('is_paused')->default(false);
            $table->string('active_app', 255)->nullable();
            $table->unsignedInteger('pending_sync_count')->default(0);
            $table->timestamp('event_timestamp')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'created_at']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('agent_heartbeats');
    }
};
