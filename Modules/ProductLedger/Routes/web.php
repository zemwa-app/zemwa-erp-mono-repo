<?php

use Illuminate\Support\Facades\Route;
use Modules\ProductLedger\Http\Controllers\ProductLedgerController;

Route::group(['middleware' => 'auth', 'prefix' => 'account'], function () {
    Route::get('product-ledger', [ProductLedgerController::class, 'index'])->name('product-ledger.index');
    Route::get('product-ledger/summary', [ProductLedgerController::class, 'summary'])->name('product-ledger.summary');
    Route::get('product-ledger/{productId}', [ProductLedgerController::class, 'showProductLedger'])->name('product-ledger.show');
});
