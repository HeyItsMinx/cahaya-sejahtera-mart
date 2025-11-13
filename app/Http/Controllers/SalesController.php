<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Sales;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;

class SalesController extends Controller
{
    use ValidatesRequests;

    public function menu()
    {
        return view('menu');
    }

    public function index()
    {
        return view('sales.index');
    }

    /**
     * Get monthly gross profit trend data
     * Filters: region, category
     */
    public function getMonthlyGrossProfitTrend(Request $request)
    {
        $query = DB::table('fact_sales')
            ->join('dim_date', 'fact_sales.date_id', '=', 'dim_date.date_id')
            ->join('dim_product', 'fact_sales.product_id', '=', 'dim_product.product_id')
            ->join('dim_store', 'fact_sales.store_id', '=', 'dim_store.store_id')
            ->select(
                'dim_date.year',
                'dim_date.month_number',
                'dim_date.month_name',
                DB::raw('SUM(fact_sales.gross_profit) as total_gross_profit')
            )
            ->groupBy('dim_date.year', 'dim_date.month_number', 'dim_date.month_name')
            ->orderBy('dim_date.year', 'asc')
            ->orderBy('dim_date.month_number', 'asc');

        // Region Filter
        if ($request->has('region') && !empty($request->region)) {
            $query->where('dim_store.region', $request->region);
        }

        // Category Filter
        if ($request->has('category') && !empty($request->category)) {
            $query->where('dim_product.category', $request->category);
        }

        $data = $query->get();

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }

    /**
     * Get gross profit by top 5 products
     */
    public function getTop5ProductsByGrossProfit(Request $request)
    {
        $query = DB::table('fact_sales')
            ->join('dim_product', 'fact_sales.product_id', '=', 'dim_product.product_id')
            ->select(
                'dim_product.product_id',
                'dim_product.product_description',
                'dim_product.category',
                'dim_product.subcategory',
                DB::raw('SUM(fact_sales.gross_profit) as total_gross_profit'),
                DB::raw('SUM(fact_sales.quantity_sold) as total_quantity_sold'),
                DB::raw('SUM(fact_sales.total_amount) as total_sales_amount')
            )
            ->groupBy('dim_product.product_id', 'dim_product.product_description', 'dim_product.category', 'dim_product.subcategory')
            ->orderBy('total_gross_profit', 'desc')
            ->limit(5);

        // Apply filters Region and Category
        if ($request->has('region') && !empty($request->region)) {
            $query->join('dim_store', 'fact_sales.store_id', '=', 'dim_store.store_id')
                ->where('dim_store.region', $request->region);
        }

        if ($request->has('category') && !empty($request->category)) {
            $query->where('dim_product.category', $request->category);
        }

        $data = $query->get();

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }

    // Filter
    public function getFilterOptions()
    {
        $regions = DB::table('dim_store')
            ->select('region')
            ->distinct()
            ->whereNotNull('region')
            ->orderBy('region')
            ->pluck('region');

        $categories = DB::table('dim_product')
            ->select('category')
            ->distinct()
            ->whereNotNull('category')
            ->orderBy('category')
            ->pluck('category');

        $promotions = DB::table('dim_promotion')
            ->select('promotion_id', 'promotion_name')
            ->orderBy('promotion_name')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'regions' => $regions,
                'categories' => $categories,
                'promotions' => $promotions
            ]
        ]);
    }

    // Overview
    public function getSalesOverview(Request $request)
    {
        $query = DB::table('fact_sales');

        // Apply filters
        if ($request->has('region') && !empty($request->region)) {
            $query->join('dim_store', 'fact_sales.store_id', '=', 'dim_store.store_id')
                  ->where('dim_store.region', $request->region);
        }

        if ($request->has('category') && !empty($request->category)) {
            $query->join('dim_product', 'fact_sales.product_id', '=', 'dim_product.product_id')
                  ->where('dim_product.category', $request->category);
        }

        $stats = $query->select(
            DB::raw('SUM(fact_sales.total_amount) as total_revenue'),
            DB::raw('SUM(fact_sales.total_cost) as total_cost'),
            DB::raw('SUM(fact_sales.gross_profit) as total_gross_profit'),
            DB::raw('SUM(fact_sales.quantity_sold) as total_quantity_sold'),
            DB::raw('COUNT(DISTINCT fact_sales.transaction_id) as total_transactions'),
            DB::raw('AVG(fact_sales.gross_profit) as avg_gross_profit_per_transaction')
        )->first();

        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }
}