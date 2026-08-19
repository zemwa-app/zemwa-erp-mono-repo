<?php

use Illuminate\Support\Facades\Route;
use Modules\Monitor\Http\Controllers\MonitorAnalyticsController;
use Modules\Monitor\Http\Controllers\MonitorController;
use Modules\Monitor\Http\Controllers\MonitorInstallerSettingController;
use Modules\Monitor\Http\Controllers\MonitorProductivityRulesController;
use Modules\Monitor\Http\Controllers\MonitorPublicInstallerController;

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

Route::get('monitor/installers/{filename}', MonitorPublicInstallerController::class)
    ->name('monitor.installer.serve')
    ->where('filename', '[A-Za-z0-9._\-]+');

Route::group(['middleware' => 'auth', 'prefix' => 'account'], function () {

    Route::get('monitor/installer-settings', [MonitorInstallerSettingController::class, 'index'])
        ->name('monitor.installer-settings.index');
    Route::post('monitor/installer-settings/upload', [MonitorInstallerSettingController::class, 'upload'])
        ->name('monitor.installer-settings.upload');
    Route::delete('monitor/installer-settings/{platform}', [MonitorInstallerSettingController::class, 'destroy'])
        ->name('monitor.installer-settings.destroy')
        ->whereIn('platform', ['windows', 'mac', 'ubuntu']);

    Route::get('monitor/installer/download/{platform}', [MonitorController::class, 'downloadInstaller'])
        ->name('monitor.installer.download')
        ->whereIn('platform', ['windows', 'mac', 'ubuntu']);
    Route::get('monitor/installer', [MonitorController::class, 'installer'])->name('monitor.installer.index');
    Route::get('monitor/config/overrides/create', [MonitorController::class, 'createOverride'])->name('monitor.config.overrides.create');
    Route::get('monitor/config/overrides/{id}/edit', [MonitorController::class, 'editOverride'])->name('monitor.config.overrides.edit');
    Route::post('monitor/config/overrides', [MonitorController::class, 'storeOverride'])->name('monitor.config.overrides.store');
    Route::put('monitor/config/overrides/{id}', [MonitorController::class, 'updateOverride'])->name('monitor.config.overrides.update');
    Route::delete('monitor/config/overrides/{id}', [MonitorController::class, 'destroyOverride'])->name('monitor.config.overrides.destroy');
    Route::get('monitor/config', [MonitorController::class, 'config'])->name('monitor.config.index');
    Route::post('monitor/config', [MonitorController::class, 'storeConfig'])->name('monitor.config.store');
    Route::post('monitor/seats/{userId}/toggle', [MonitorController::class, 'toggleMonitoringSeat'])->name('monitor.seats.toggle');
    Route::get('monitor/config/rules', [MonitorProductivityRulesController::class, 'index'])->name('monitor.config.rules.index');
    Route::post('monitor/config/rules', [MonitorProductivityRulesController::class, 'store'])->name('monitor.config.rules.store');
    Route::put('monitor/config/rules/{id}', [MonitorProductivityRulesController::class, 'update'])->name('monitor.config.rules.update');
    Route::delete('monitor/config/rules/{id}', [MonitorProductivityRulesController::class, 'destroy'])->name('monitor.config.rules.destroy');
    Route::post('monitor/config/rules/reclassify', [MonitorProductivityRulesController::class, 'reclassify'])->name('monitor.config.rules.reclassify');
    Route::get('monitor/reports/export', [MonitorController::class, 'exportReports'])->name('monitor.reports.export');
    Route::get('monitor/reports', [MonitorController::class, 'reports'])->name('monitor.reports.index');
    Route::prefix('monitor/analytics')->name('monitor.analytics.')->group(function () {
        Route::get('export', [MonitorAnalyticsController::class, 'export'])->name('export');
        Route::get('scores/{employee}', [MonitorAnalyticsController::class, 'scoreDetail'])->name('scores.show');
        Route::get('heatmap/{employee}', [MonitorAnalyticsController::class, 'heatmap'])->name('heatmap.show');
        Route::get('idle/{employee}', [MonitorAnalyticsController::class, 'idle'])->name('idle.show');
        Route::get('departments/{department}', [MonitorAnalyticsController::class, 'departmentDetail'])->name('departments.show');
        Route::get('/', [MonitorAnalyticsController::class, 'index'])->name('index');
    });

    Route::get('monitor/screenshots', [MonitorController::class, 'screenshots'])->name('monitor.screenshots.index');
    Route::get('monitor/screenshot/preview', [MonitorController::class, 'screenshotPreview'])
        ->name('monitor.screenshot.preview');
    Route::resource('monitor', MonitorController::class)->only(['index', 'show']);

});
