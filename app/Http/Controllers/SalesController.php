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

        // Date range
        if ($request->filled('date_range')) {
            $dates = explode(' to ', $request->date_range);
            if (count($dates) == 2) {
                $query->whereBetween('dim_date.date_id', [
                    str_replace('-', '', $dates[0]),
                    str_replace('-', '', $dates[1])
                ]);
            }
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

        if ($request->filled('date_range')) {
            $dates = explode(' to ', $request->date_range);
            if (count($dates) == 2) {
                $query->whereBetween('date_id', [
                    str_replace('-', '', $dates[0]),
                    str_replace('-', '', $dates[1])
                ]);
            }
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

    /**
     * Get monthly gross profit trend, broken down by product category.
     * This is a 2-Dimension query (Fact vs Time vs Product).
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getProfitTrendByCategory(Request $request)
    {
        $query = DB::table('fact_sales')
            ->join('dim_date', 'fact_sales.date_id', '=', 'dim_date.date_id')
            ->join('dim_product', 'fact_sales.product_id', '=', 'dim_product.product_id')
            ->join('dim_store', 'fact_sales.store_id', '=', 'dim_store.store_id')
            ->select(
                'dim_date.year',
                'dim_date.month_number',
                'dim_date.month_name',
                'dim_product.category', 
                DB::raw('SUM(fact_sales.gross_profit) as total_gross_profit')
            )
            // Group by both dimensions
            ->groupBy('dim_date.year', 'dim_date.month_number', 'dim_date.month_name', 'dim_product.category')
            ->orderBy('dim_date.year', 'asc')
            ->orderBy('dim_date.month_number', 'asc');

        // Apply filters
        if ($request->has('region') && !empty($request->region)) {
            $query->where('dim_store.region', $request->region);
        }

        if ($request->has('category') && !empty($request->category)) {
            $query->where('dim_product.category', $request->category);
        }

        if ($request->filled('date_range')) {
            $dates = explode(' to ', $request->date_range);
            if (count($dates) == 2) {
                $query->whereBetween('dim_date.date_id', [
                    str_replace('-', '', $dates[0]),
                    str_replace('-', '', $dates[1])
                ]);
            }
        }

        $data = $query->get();

        return response()->json([
            'success' => true,
            'data' => $data
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

        // Date range
        if ($request->filled('date_range')) {
            $dates = explode(' to ', $request->date_range);
            if (count($dates) == 2) {
                $query->whereBetween('date_id', [
                    str_replace('-', '', $dates[0]),
                    str_replace('-', '', $dates[1])
                ]);
            }
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


    private function getUnsoldBaseQuery(Request $request)
    {
        $query =  DB::table('fact_promotion_coverage AS fpc')
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

        if ($request->filled('date_range')) {
            $dates = explode(' to ', $request->date_range);
            if (count($dates) == 2) {
                $query->whereBetween('dp.end_date', [
                    str_replace('-', '', $dates[0]),
                    str_replace('-', '', $dates[1])
                ]);
            }
        }

        return $query;
    }


    /**
     * Get Top 10 unsold promotional products, stacked by store region.
     * This is a 2-Dimension query (Factless vs Product vs Store).
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getUnsoldProductsByRegion(Request $request)
    {
       // 1. Get the base query of all unsold items
        $baseQuery = $this->getUnsoldBaseQuery($request);

        $top10Query = clone $baseQuery;

        // Join tabel dimensi yang diperlukan untuk filter
        $top10Query->join('dim_product AS p', 'fpc.product_id', '=', 'p.product_id')
                   ->join('dim_store AS s', 'fpc.store_id', '=', 's.store_id');

        // Terapkan Filter (Region/Category)
        if ($request->has('region') && !empty($request->region)) {
            $top10Query->where('s.region', $request->region);
        }
        if ($request->has('category') && !empty($request->category)) {
            $top10Query->where('p.category', $request->category);
        }

        // Ambil Top 10 berdasarkan jumlah kegagalan
        $top10Products = $top10Query->select(
                'fpc.product_id',
                'p.product_description',
                DB::raw('COUNT(*) as total_failures')
            )
            ->groupBy('fpc.product_id', 'p.product_description')
            ->orderBy('total_failures', 'desc')
            ->limit(10)
            ->get();

        // Jika tidak ada data, kembalikan array kosong
        if ($top10Products->isEmpty()) {
            return response()->json(['success' => true, 'data' => []]);
        }

        // Ambil ID produk untuk filter query kedua
        $topProductIds = $top10Products->pluck('product_id')->toArray();
        
        // Buat Map untuk lookup total failures nanti: [product_id => total_failures]
        $productTotalsMap = $top10Products->pluck('total_failures', 'product_id');


        $regionQuery = $this->getUnsoldBaseQuery($request);

        $regionalData = $regionQuery
            ->join('dim_product AS p', 'fpc.product_id', '=', 'p.product_id')
            ->join('dim_store AS s', 'fpc.store_id', '=', 's.store_id')
            ->whereIn('fpc.product_id', $topProductIds) // Filter HANYA untuk Top 10 tadi
            ->select(
                'p.product_id',
                'p.product_description',
                's.region',
                DB::raw('COUNT(*) as unsold_count_by_region')
            )
            ->groupBy('p.product_id', 'p.product_description', 's.region')
            ->get();


        
        $finalData = $regionalData->map(function ($item) use ($productTotalsMap) {
            $item->total_failures = $productTotalsMap[$item->product_id] ?? 0;
            return $item;
        });

        $finalData = $finalData->sortByDesc('total_failures')->values();

        return response()->json([
            'success' => true,
            'data' => $finalData
        ]);
    }
}