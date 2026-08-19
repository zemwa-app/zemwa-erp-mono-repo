<?php

use Illuminate\Support\Facades\Route;
use Modules\ProBundle\Http\Controllers\ProBundleController;

Route::group(['middleware' => 'auth', 'prefix' => 'account'], function () {

    Route::group(
        ['prefix' => 'settings'],
        function () {
            Route::post('install-pro-bundle-module', [ProBundleController::class, 'installProBundleModule'])->name('install-pro-bundle-module');
            Route::post('add-pro-module-purchase-code', [ProBundleController::class, 'addProModulePurchaseCode'])->name('add-pro-module-purchase-code');
        }
    );

});
