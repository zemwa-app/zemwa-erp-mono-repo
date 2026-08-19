<?php

use Illuminate\Support\Facades\Route;
use Modules\PublicAssessmentPro\Http\Controllers\PublicAssessmentController;

Route::get('public-assessment/{id}', [PublicAssessmentController::class, 'showPublicAssessment'])->name('public-assessment');
Route::post('store-public-assessment', [PublicAssessmentController::class, 'storePublicAssessment'])->name('front.store-public-assessement');
