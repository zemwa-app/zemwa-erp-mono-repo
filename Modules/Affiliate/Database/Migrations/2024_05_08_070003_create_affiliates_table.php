<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Modules\Affiliate\Enums\Status;

return new class extends Migration
{

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('affiliates')) {
            Schema::create('affiliates', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('user_id');
                $table->foreign('user_id')->references('id')->on('users')->cascadeOnUpdate()->cascadeOnDelete();
                $table->string('referral_code')->unique();
                $table->decimal('balance', 15, 2)->default(0);
                $table->string('status')->default(Status::Active);
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('affiliates');
    }

};
