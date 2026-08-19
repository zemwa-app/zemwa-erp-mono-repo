<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('agent_activity_windows', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
            $table->unsignedInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->timestamp('window_start');
            $table->timestamp('window_end');
            $table->unsignedInteger('keystrokes')->default(0);
            $table->unsignedInteger('mouse_clicks')->default(0);
            $table->unsignedInteger('mouse_distance')->default(0);
            $table->unsignedInteger('scroll_events')->default(0);
            $table->decimal('activity_pct', 5, 2)->default(0);
            $table->boolean('is_idle')->default(false);
            $table->timestamps();

            $table->index(['user_id', 'window_start']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('agent_activity_windows');
    }
};
