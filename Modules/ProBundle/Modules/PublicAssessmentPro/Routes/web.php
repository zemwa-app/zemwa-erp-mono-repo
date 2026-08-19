<?php

use Illuminate\Support\Facades\Route;
use Modules\PublicAssessmentPro\Http\Controllers\PublicAssessmentProController;
use Modules\PublicAssessmentPro\Http\Controllers\PublicAssessmentProSettingsController;
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

Route::group(['middleware' => 'auth', 'prefix' => 'account'], function () {
	Route::resource('publicassessment', PublicAssessmentProController::class)->except(['show'])->names('publicassessmentpro');
	Route::group(['prefix' => 'publicassessment'], function () {
		Route::get('/configuration', [PublicAssessmentProController::class, 'config'])->name('publicassessmentpro.config.home');
		Route::get('/participants', [PublicAssessmentProController::class, 'participants'])->name('publicassessmentpro.config.participants');

        Route::get('/get-by-assesstype/{id}', [PublicAssessmentProController::class, 'getAssessTypeFields'])->name('publicassessmentpro.config.getAssessTypeFields');
        Route::post('/create-assessment', [PublicAssessmentProController::class, 'createAssessment'])->name('publicassessmentpro.config.createAssessment');
        Route::get('/edit-assessment/{id}', [PublicAssessmentProController::class, 'editAssessment'])->name('publicassessmentpro.config.editAssessment');
        Route::delete('/delete-assessment/{id}', [PublicAssessmentProController::class, 'destroyAssessment'])->name('publicassessmentpro.config.destroyAssessment');

        Route::get('/list-category/{action?}', [PublicAssessmentProController::class, 'getQuesCategoryList'])->name('publicassessmentpro.config.getQuesCategoryList');
        Route::get('/get-category', [PublicAssessmentProController::class, 'getCategorySelect'])->name('publicassessmentpro.config.getCategories');
        Route::post('/create-category', [PublicAssessmentProController::class, 'createCategory'])->name('publicassessmentpro.config.createCategory');
        Route::delete('/delete-category/{id}', [PublicAssessmentProController::class, 'destroyCategory'])->name('publicassessmentpro.config.destroyCategory');
        
        Route::get('/list-questions/{id}', [PublicAssessmentProController::class, 'getAssessQuestion'])->name('publicassessmentpro.config.getAssessQuestion');
        Route::get('/create-questions/{id}', [PublicAssessmentProController::class, 'createQuestion'])->name('publicassessmentpro.config.createQuestion');
        Route::post('/store-qa', [PublicAssessmentProController::class, 'storeQa'])->name('publicassessmentpro.config.storeQa');
        Route::get('/edit-qa/{aid}/{qid}', [PublicAssessmentProController::class, 'editQa'])->name('publicassessmentpro.config.editQa');
        Route::delete('/destroy-qa/{id}', [PublicAssessmentProController::class, 'destroyQa'])->name('publicassessmentpro.config.destroyQa');



        Route::group(
			['prefix' => 'settings'],
			function () {
				Route::resource('publicassessment-settings', PublicAssessmentProSettingsController::class)->names('publicassessmentpro-settings');//->except(['show']);
			}
		);
	});
});

