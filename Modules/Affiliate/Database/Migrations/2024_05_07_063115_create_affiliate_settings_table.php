<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Modules\Affiliate\Entities\AffiliateSetting;
use Modules\Affiliate\Enums\CommissionType;
use Modules\Affiliate\Enums\PayoutType;
use Modules\Affiliate\Enums\YesNo;

return new class extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('affiliate_settings', function (Blueprint $table) {
            $table->increments('id');
            $table->string('commission_enabled')->default(YesNo::Yes);
            $table->string('payout_type')->default(PayoutType::OnSignUp);
            $table->string('payout_time')->nullable();
            $table->string('commission_type')->default(CommissionType::Fixed);
            $table->unsignedInteger('commission_cap')->default(0);
            $table->unsignedInteger('minimum_payout')->default(0);
            $table->timestamps();
        });

        $this->seedAffiliateSettings();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('affiliate_settings');
    }

    /**
     * Seed the initial affiliate settings.
     *
     * @return void
     */
    private function seedAffiliateSettings()
    {
        AffiliateSetting::create([
            'commission_enabled' => YesNo::Yes,
            'payout_type' => PayoutType::OnSignUp,
            'payout_time' => null,
            'commission_type' => CommissionType::Fixed,
            'commission_cap' => 20,
            'minimum_payout' => 200,
        ]);
    }

};
