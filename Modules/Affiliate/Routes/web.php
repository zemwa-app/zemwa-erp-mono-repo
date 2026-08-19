<?php

use Illuminate\Support\Facades\Route;
use Modules\Affiliate\Http\Controllers\PayoutController;
use Modules\Affiliate\Http\Controllers\AffiliateController;
use Modules\Affiliate\Http\Controllers\ReferralsController;
use Modules\Affiliate\Http\Controllers\DashBoardController;
use Modules\Affiliate\Http\Controllers\AffiliateSettingController;
use Modules\Affiliate\Http\Controllers\AffiliateDashboardController;
use Modules\Affiliate\Http\Controllers\AffiliatePublicController;

Route::group(['middleware' => 'auth', 'prefix' => 'account'], function () {
    Route::resource('affiliates-dashboard', DashBoardController::class)->names('dashboard')->only(['index']);
    Route::post('affiliates/change-status', [AffiliateController::class, 'changeStatus'])->name('affiliates.change_status');
    Route::resource('affiliates', AffiliateController::class)->names('affiliate')->except(['edit', 'update']);
    Route::get('affiliates/get-affiliates/{id}', [ReferralsController::class, 'getAffiliates'])->name('affiliates.get_affiliates');
    Route::resource('referrals', ReferralsController::class)->names('referral');

    Route::resource('payouts', PayoutController::class)->names('payout');
    Route::post('payouts/change-status', [PayoutController::class, 'changeStatus'])->name('payouts.change_status');
    Route::get('payouts-confirm-paid/{payout}', [PayoutController::class, 'paidConfirmation'])->name('payouts.confirm_paid');

    Route::resource('affiliate-dashboard', AffiliateDashboardController::class)->names('affiliate-dashboard')->only(['index', 'edit', 'update']);
});

Route::get('affiliates/{referral}', [AffiliatePublicController::class, 'redirectReferral'])->name('affiliate.redirectReferral');

Route::group(['middleware' => 'auth', 'prefix' => 'account/settings'], function () {
    Route::resource('affiliate-settings', AffiliateSettingController::class);
});
