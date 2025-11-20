<?php

use App\Http\Controllers\InventoryController;
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
    Route::get('/unsold-products-by-region', [SalesController::class, 'getUnsoldProductsByRegion'])
        ->name('getUnsoldProductsByRegion');
});

Route::prefix('/inventory')->group(function () {
        Route::get('/', [InventoryController::class, 'index'])->name('inventory.index');
        Route::get('/filter-options', [InventoryController::class, 'getFilterOptions'])->name('inventory.getFilterOptions');
        Route::get('/overview', [InventoryController::class, 'getOverview'])->name('inventory.getOverview');
        Route::get('/qty-by-date', [InventoryController::class, 'getQtyByDate'])->name('inventory.getQtyByDate');
        Route::get('/qty-by-warehouse', [InventoryController::class, 'getQtyByWarehouse'])->name('inventory.getQtyByWarehouse');
        Route::get('/value-by-warehouse', [InventoryController::class, 'getValueByWarehouse'])->name('inventory.getValueByWarehouse');
        Route::get('/qty-by-date-warehouse', [App\Http\Controllers\InventoryController::class, 'getQtyByDateWarehouse'])->name('inventory.getQtyByDateWarehouse');
        Route::get('/top-products', [InventoryController::class, 'getTopProducts'])->name('inventory.getTopProducts');
        Route::get('/warehouse-category', [InventoryController::class, 'getWarehouseCategoryData'])->name('inventory.getWarehouseCategoryData');
    });

Route::prefix('/procurement')->group(function () {
    Route::get('/chart-lead-time', [ProcurementController::class, 'chartPage']);
    Route::get('/chart-lead-time/data', [ProcurementController::class, 'chartLeadTimeByVendor']);
});