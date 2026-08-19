<?php

use Illuminate\Support\Facades\Route;
use Modules\LeadFormsPro\Http\Controllers\LfController;

Route::get('leadspro/{id}', [LfController::class, 'leadForm'])->name('front.lead_pro_form');
