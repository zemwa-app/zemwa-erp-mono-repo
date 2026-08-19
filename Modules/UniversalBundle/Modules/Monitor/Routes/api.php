<?php

use Illuminate\Support\Facades\Route;
use Modules\Monitor\Http\Controllers\MonitorController;

Route::middleware(['api', 'auth'])->prefix('tracker')->group(function () {
    Route::get('live-status', [MonitorController::class, 'liveStatus'])->name('api.tracker.live-status');
});
