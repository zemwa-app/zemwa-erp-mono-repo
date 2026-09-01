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
        if (!Schema::hasTable('groups')) {
            Schema::create('groups', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedBigInteger('company_id');
                $table->foreign('company_id')->references('id')->on('companies')->onUpdate('CASCADE')->onDelete('CASCADE');
                $table->string('name')->nullable();
                $table->integer('owner_id')->nullable();
                $table->text('description')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('group_members')) {
            Schema::create('group_members', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('group_id');
                $table->foreign('group_id')->references('id')->on('groups')->onUpdate('CASCADE')->onDelete('CASCADE');
                $table->unsignedInteger('user_id');
                $table->foreign('user_id')->references('id')->on('users')->onUpdate('CASCADE')->onDelete('CASCADE');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('channels')) {
            Schema::create('channels', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedBigInteger('company_id');
                $table->foreign('company_id')->references('id')->on('companies')->onUpdate('CASCADE')->onDelete('CASCADE');
                $table->string('name');
                $table->integer('owner_id')->nullable();
                $table->text('description')->nullable();
                $table->timestamps();
            });
        }

        if (Schema::hasTable('users_chat')) {
            Schema::table('users_chat', function (Blueprint $table) {
                if (!Schema::hasColumn('users_chat', 'chat_type')) {
                    $table->enum('chat_type', ['private', 'client', 'group', 'channel'])->default('private')->after('message_seen');
                }
                if (!Schema::hasColumn('users_chat', 'group_id')) {
                    $table->unsignedInteger('group_id')->nullable()->after('chat_type');
                    $table->foreign('group_id')->references('id')->on('groups')->onUpdate('CASCADE')->onDelete('CASCADE');
                }
                if (!Schema::hasColumn('users_chat', 'channel_id')) {
                    $table->unsignedInteger('channel_id')->nullable()->after('group_id');
                    $table->foreign('channel_id')->references('id')->on('channels')->onUpdate('CASCADE')->onDelete('CASCADE');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists(['groups', 'group_members', 'channels', 'channel_members']);

        Schema::table('users_chat', function (Blueprint $table) {
            $table->dropForeign(['group_id', 'channel_id']);
            $table->dropColumn(['group_id', 'channel_id', 'chat_type']);
        });
    }

};
