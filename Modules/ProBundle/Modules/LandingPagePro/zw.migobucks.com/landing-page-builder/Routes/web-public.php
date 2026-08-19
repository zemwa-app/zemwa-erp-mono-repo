<?php

use Illuminate\Support\Facades\Route;
use Modules\LandingPagePro\Http\Controllers\LaunchPadController;

Route::get('launchpad/{id}', [LaunchPadController::class, 'preview'])->name('template.page');
