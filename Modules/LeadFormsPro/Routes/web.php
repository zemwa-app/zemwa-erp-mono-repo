<?php

use Illuminate\Support\Facades\Route;
use Modules\LeadFormsPro\Http\Controllers\LeadFormsProController;
use Modules\LeadFormsPro\Http\Controllers\LeadFormsProSettingsController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Admin routes
Route::group(['middleware' => 'auth', 'prefix' => 'account'], function () {
	Route::resource('leadforms', LeadFormsProController::class)->names('leadformspro');

	Route::post('/lfp-lead-category', [LeadFormsProController::class, 'storeCategory'])->name('lfpLeadCategory.store');
	Route::get('/lfp-lead-category/{id}', [LeadFormsProController::class, 'editCategory'])->name('lfpLeadCategory.edit');
	Route::delete('/lfp-lead-category/{id}', [LeadFormsProController::class, 'destroyCategory'])->name('lfpLeadCategory.destroy');

	Route::post('/store-lead-form', [LeadFormsProController::class, 'storeLeadForm'])->name('leadProForm.store');
	Route::get('/edit-lead-form/{id}', [LeadFormsProController::class, 'editLeadForm'])->name('leadProForm.edit');
	Route::delete('/delete-lead-form/{id}', [LeadFormsProController::class, 'destroyLeadForm'])->name('leadProForm.destroy');

    Route::group(
        ['prefix' => 'settings'],
        function () {
	        Route::resource('leadforms-settings', LeadFormsProSettingsController::class)->names('leadformspro-settings');
        }
    );
});
