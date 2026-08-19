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
        Schema::create('affiliate_referrals', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedBigInteger('company_id');
            $table->foreign('company_id')->references('id')->on('companies')->cascadeOnUpdate()->cascadeOnDelete();
            $table->unsignedInteger('affiliate_id');
            $table->foreign('affiliate_id')->references('id')->on('affiliates')->cascadeOnUpdate()->cascadeOnDelete();
            $table->ipAddress('ip')->nullable();
            $table->string('user_agent')->nullable();
            $table->decimal('commissions', 30, 2)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('affiliate_referrals');
    }

};
