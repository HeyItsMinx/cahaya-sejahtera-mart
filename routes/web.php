<?php

use Illuminate\Http\Request;
use App\Http\Controllers\SalesController;
use App\Http\Controllers\ProcurementController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/home', [SalesController::class, 'menu']);

Route::prefix('/sales')->name('sales.')->group(function () {
    Route::get('/', [SalesController::class, 'index']);
    Route::get('/monthly-gross-profit', [SalesController::class, 'getMonthlyGrossProfitTrend'])->name('getMonthlyGrossProfitTrend');;
    Route::get('/top-products', [SalesController::class, 'getTop5ProductsByGrossProfit'])->name('getTop5ProductsByGrossProfit');;
    Route::get('/filter-options', [SalesController::class, 'getFilterOptions'])->name('getFilterOptions');;
    Route::get('/overview', [SalesController::class, 'getSalesOverview'])->name('getSalesOverview');
     Route::get('/unsold-promotions-list', [SalesController::class, 'getUnsoldPromotionsList'])
        ->name('getUnsoldPromotionsList');

    Route::get('/unsold-promo-trend', [SalesController::class, 'getUnsoldPromoTrend'])
        ->name('getUnsoldPromoTrend');

    Route::get('/ineffective-promotions', [SalesController::class, 'getMostIneffectivePromotions'])
        ->name('getMostIneffectivePromotions');
});

Route::prefix('/procurement')->group(function () {
    Route::get('/', [ProcurementController::class, 'index']);
});