<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('agent_screenshots', function (Blueprint $table) {
            $table->unsignedInteger('task_id')->nullable()->after('user_id');
            $table->foreign('task_id')->references('id')->on('tasks')->onDelete('set null');
            $table->index(['task_id', 'captured_at']);
        });
    }

    public function down()
    {
        Schema::table('agent_screenshots', function (Blueprint $table) {
            $table->dropForeign(['task_id']);
            $table->dropIndex(['task_id', 'captured_at']);
            $table->dropColumn('task_id');
        });
    }
};
