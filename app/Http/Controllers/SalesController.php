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

    public function getUnsoldPromotionsList(Request $request)
    {
        // Base query for unsold items
        $query = DB::table('fact_promotion_coverage AS fpc')
            ->join('dim_promotion AS dp', 'fpc.promotion_id', '=', 'dp.promotion_id')
            ->join('dim_product AS p', 'fpc.product_id', '=', 'p.product_id')
            ->join('dim_store AS s', 'fpc.store_id', '=', 's.store_id')
            ->leftJoin('fact_sales AS fs', function ($join) {
                $join->on('fs.product_id', '=', 'fpc.product_id')
                     ->on('fs.store_id', '=', 'fpc.store_id')
                     // This is the key: only look for sales WITHIN the promotion window
                     ->whereRaw('fs.date_id BETWEEN dp.start_date AND dp.end_date');
            })
            ->whereNull('fs.transaction_id') // The "factless" logic: no sale was found
            ->select(
                'p.product_description', 
                's.store_name', 
                'dp.promotion_name',
                // Join to dim_date to get human-readable dates
                'd_start.full_date AS start_date',
                'd_end.full_date AS end_date'
            )
            ->join('dim_date AS d_start', 'dp.start_date', '=', 'd_start.date_id')
            ->join('dim_date AS d_end', 'dp.end_date', '=', 'd_end.date_id')
            ->distinct();

        // Apply filters
        if ($request->has('region') && !empty($request->region)) {
            $query->where('s.region', $request->region);
        }
        if ($request->has('category') && !empty($request->category)) {
            $query->where('p.category', $request->category);
        }
        if ($request->has('promotion_id') && !empty($request->promotion_id)) {
            $query->where('fpc.promotion_id', $request->promotion_id);
        }

        // Return as Yajra DataTable
        return DataTables::of($query)->make(true);
    }

    private function getUnsoldBaseQuery()
    {
        return DB::table('fact_promotion_coverage AS fpc')
            ->join('dim_promotion AS dp', 'fpc.promotion_id', '=', 'dp.promotion_id')
            ->leftJoin('fact_sales AS fs', function ($join) {
                $join->on('fs.product_id', '=', 'fpc.product_id')
                     ->on('fs.store_id', '=', 'fpc.store_id')
                     ->whereRaw('fs.date_id BETWEEN dp.start_date AND dp.end_date');
            })
            ->whereNull('fs.transaction_id')
             // We only care about the unique fact of failure
            ->select('fpc.product_id', 'fpc.store_id', 'fpc.promotion_id')
            ->distinct();
    }

    /**
     * (Recommended) Get the Top 5 most ineffective promotions,
     * ranked by the number of product/store combinations that failed to sell.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getMostIneffectivePromotions(Request $request)
    {
        $unsoldSubquery = $this->getUnsoldBaseQuery();

        $query = DB::table(DB::raw("({$unsoldSubquery->toSql()}) AS unsold"))
            ->mergeBindings($unsoldSubquery)
            ->join('dim_promotion AS dp', 'unsold.promotion_id', '=', 'dp.promotion_id')
            ->join('dim_product AS p', 'unsold.product_id', '=', 'p.product_id') // For category filter
            ->join('dim_store AS s', 'unsold.store_id', '=', 's.store_id') // For region filter
            ->select(
                'dp.promotion_name',
                // Count the number of unique product/store combinations that failed
                DB::raw('COUNT(*) as unsold_items_count')
            )
            ->groupBy('dp.promotion_id', 'dp.promotion_name')
            ->orderBy('unsold_items_count', 'desc')
            ->limit(5);

        // Apply filters
        if ($request->has('region') && !empty($request->region)) {
            $query->where('s.region', $request->region);
        }
        if ($request->has('category') && !empty($request->category)) {
            $query->where('p.category', $request->category);
        }

        $data = $query->get();

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }

    public function getTop5SuccessfulPromotions(Request $request)
    {
        // This query analyzes fact_sales (Matrix 1 & 2)
        $query = DB::table('fact_sales AS fs')
            // We only care about sales that actually had a promotion
            ->where('fs.promotion_id', '>', 0) 
            ->join('dim_promotion AS dp', 'fs.promotion_id', '=', 'dp.promotion_id')
            ->join('dim_product AS p', 'fs.product_id', '=', 'p.product_id') // For category filter
            ->join('dim_store AS s', 'fs.store_id', '=', 's.store_id') // For region filter
            ->select(
                'dp.promotion_name',
                // This is the main metric: total items sold under this promo
                DB::raw('SUM(fs.quantity_sold) as total_quantity_sold')
            )
            ->groupBy('dp.promotion_id', 'dp.promotion_name')
            ->orderBy('total_quantity_sold', 'desc')
            ->limit(5);

        // Apply filters
        if ($request->has('region') && !empty($request->region)) {
            $query->where('s.region', $request->region);
        }
        if ($request->has('category') && !empty($request->category)) {
            $query->where('p.category', $request->category);
        }

        $data = $query->get();

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }
}