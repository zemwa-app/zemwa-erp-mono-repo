<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('deal_email_templates')) {
            Schema::create('deal_email_templates', function (Blueprint $table) {
                $table->id();
                $table->unsignedInteger('company_id')->nullable();
                $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade')->onUpdate('cascade');
                $table->string('name');
                $table->string('subject');
                $table->longText('body');
                $table->unsignedInteger('added_by')->nullable();
                $table->foreign('added_by')->references('id')->on('users')->onDelete('set null')->onUpdate('cascade');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('deal_email_histories')) {
            Schema::create('deal_email_histories', function (Blueprint $table) {
                $table->id();
                $table->unsignedInteger('company_id')->nullable();
                $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade')->onUpdate('cascade');
                $table->unsignedBigInteger('deal_id');
                $table->foreign('deal_id')->references('id')->on('deals')->onDelete('cascade')->onUpdate('cascade');
                $table->unsignedInteger('lead_id');
                $table->foreign('lead_id')->references('id')->on('leads')->onDelete('cascade')->onUpdate('cascade');
                $table->unsignedBigInteger('deal_email_template_id')->nullable();
                $table->foreign('deal_email_template_id')->references('id')->on('deal_email_templates')->onDelete('set null')->onUpdate('cascade');
                $table->string('subject');
                $table->longText('body');
                $table->string('recipient_email');
                $table->string('recipient_name')->nullable();
                $table->unsignedInteger('sent_by')->nullable();
                $table->foreign('sent_by')->references('id')->on('users')->onDelete('set null')->onUpdate('cascade');
                $table->enum('status', ['sent', 'failed'])->default('sent');
                $table->json('meta')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('deal_email_attachments')) {
            Schema::create('deal_email_attachments', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('deal_email_history_id');
                $table->foreign('deal_email_history_id')->references('id')->on('deal_email_histories')->onDelete('cascade')->onUpdate('cascade');
                $table->string('filename');
                $table->string('hashname');
                $table->string('size')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('deal_email_attachments');
        Schema::dropIfExists('deal_email_histories');
        Schema::dropIfExists('deal_email_templates');
    }
};
