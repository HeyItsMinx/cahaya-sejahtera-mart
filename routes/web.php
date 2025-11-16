<?php

use Illuminate\Http\Request;
use App\Http\Controllers\Backends\SalesController;
use App\Http\Controllers\Backends\ProcurementController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/home', [SalesController::class, 'menu']);

Route::prefix('/sales')->group(function () {
    Route::get('/', [SalesController::class, 'index']);
    Route::get('/monthly-gross-profit', [SalesController::class, 'getMonthlyGrossProfitTrend']);
    Route::get('/top-products', [SalesController::class, 'getTop5ProductsByGrossProfit']);
    Route::get('/filter-options', [SalesController::class, 'getFilterOptions']);
    Route::get('/overview', [SalesController::class, 'getSalesOverview']);
});

Route::prefix('/procurement')->group(function () {
    Route::get('/', [ProcurementController::class, 'index']);
    Route::get('/datatable', [ProcurementController::class, 'datatable']);
    Route::post('/update-status', [ProcurementController::class, 'updateStatus']);
    Route::get('/chart-lead-time', [ProcurementController::class, 'chartPage']);
    Route::get('/chart-lead-time/data', [ProcurementController::class, 'chartLeadTimeByVendor']);
});