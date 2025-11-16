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
    Route::get('/profit-trend-by-category', [SalesController::class, 'getProfitTrendByCategory'])
        ->name('getProfitTrendByCategory');
    Route::get('/overview', [SalesController::class, 'getSalesOverview'])->name('getSalesOverview');
    // Promotions
    Route::get('/ineffective-promotions', [SalesController::class, 'getMostIneffectivePromotions'])
        ->name('getMostIneffectivePromotions');
    Route::get('/top-successful-promotions', [SalesController::class, 'getTop5SuccessfulPromotions'])
        ->name('getTop5SuccessfulPromotions');
    Route::get('/unsold-products-by-region', [SalesController::class, 'getUnsoldProductsByRegion'])
        ->name('getUnsoldProductsByRegion');
});

Route::prefix('/procurement')->group(function () {
    Route::get('/', [ProcurementController::class, 'index']);
    Route::get('/datatable', [ProcurementController::class, 'datatable']);
    Route::post('/update-status', [ProcurementController::class, 'updateStatus']);
    Route::get('/chart-lead-time', [ProcurementController::class, 'chartPage']);
    Route::get('/chart-lead-time/data', [ProcurementController::class, 'chartLeadTimeByVendor']);
});