<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('agent_network_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
            $table->unsignedInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->timestamp('hour');
            $table->unsignedBigInteger('total_bytes_sent')->default(0);
            $table->unsignedBigInteger('total_bytes_received')->default(0);
            $table->json('top_processes')->nullable();
            $table->json('cloud_uploads_detected')->nullable();
            $table->boolean('vpn_active')->default(false);
            $table->boolean('large_transfer_alert')->default(false);
            $table->timestamps();

            $table->index(['user_id', 'hour']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('agent_network_logs');
    }
};
