<?php

use Illuminate\Support\Facades\Route;
use Modules\LandingPagePro\Http\Controllers\LandingPageProController;
use Modules\LandingPagePro\Http\Controllers\LandingPageProSettingController;


// Admin routes
Route::group(['middleware' => 'auth', 'prefix' => 'account'], function () {
	Route::resource('landingpages', LandingPageProController::class)->names('landingpagepro');

	Route::get('/edit-landingpage/{id}', [LandingPageProController::class, 'edit'])->name('template.edit');
	Route::delete('/destroy-landingpage/{id}', [LandingPageProController::class, 'destroy'])->name('template.delete');

	Route::get('/lp-builder/{id}', [LandingPageProController::class, 'builder'])->name('template.editor');
	Route::get('/lp-builder-form/{id}', [LandingPageProController::class, 'form'])->name('template.form');
	Route::post('/lp-preview', [LandingPageProController::class, 'updatePage'])->name('landingpage.update');

	Route::post('/lp-category', [LandingPageProController::class, 'storeCategory'])->name('lpCategory.store');
	Route::get('/lp-category/{id}', [LandingPageProController::class, 'editCategory'])->name('lpCategory.edit');
	Route::delete('/lp-category/{id}', [LandingPageProController::class, 'destroyCategory'])->name('lpCategory.destroy');

	Route::group(
		['prefix' => 'settings'],
		function () {
			Route::resource('landingpage-settings', LandingPageProSettingController::class);

			Route::post('/lp-template', [LandingPageProSettingController::class, 'storeTemplate'])->name('lpTemplate.store');
			Route::get('/lp-template/{id}', [LandingPageProSettingController::class, 'editTemplate'])->name('lpTemplate.edit');
			Route::delete('/lp-template/{id}', [LandingPageProSettingController::class, 'destroyTemplate'])->name('lpTemplate.destroy');
		}
	);
});
