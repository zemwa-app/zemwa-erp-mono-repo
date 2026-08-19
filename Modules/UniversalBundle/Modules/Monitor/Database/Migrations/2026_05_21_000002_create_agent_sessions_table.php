<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('agent_sessions')) {
            return;
        }

        Schema::create('agent_sessions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
            $table->unsignedInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->boolean('is_online')->default(false);
            $table->timestamp('last_seen_at')->nullable();
            $table->string('active_app')->nullable();
            $table->timestamps();

            $table->unique('user_id');
            $table->index(['is_online', 'last_seen_at']);
            $table->index(['company_id', 'is_online']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agent_sessions');
    }
};
