<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Modules\Affiliate\Enums\PaymentStatus;

return new class extends Migration
{

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('affiliate_payouts', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('affiliate_id');
            $table->foreign('affiliate_id')->references('id')->on('affiliates')->cascadeOnUpdate()->cascadeOnDelete();
            $table->decimal('balance', 30, 2);
            $table->decimal('amount_requested', 30, 2);
            $table->string('status')->default(PaymentStatus::Pending)->nullable();
            $table->string('payment_method');
            $table->string('other_payment_method')->nullable();
            $table->text('note')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->string('transaction_id')->nullable();
            $table->text('memo')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payouts');
    }

};
