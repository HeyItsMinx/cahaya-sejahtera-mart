<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\DimDate;
use App\Models\DimProduct;
use App\Models\DimWarehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\FactInventorySnapshot;
use Illuminate\Support\Facades\Session;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Foundation\Validation\ValidatesRequests;

class InventoryController extends Controller
{
    use ValidatesRequests;

    public function index()
    {
        return view('inventory.index');
    }

    /**
     * Get filter options
     */
    public function getFilterOptions()
    {
        $warehouses = DimWarehouse::select('warehouse_id', 'warehouse_name')->get();
        $categories = DimProduct::distinct()->pluck('category')->filter()->values();

        return response()->json([
            'success' => true,
            'data' => [
                'warehouses' => $warehouses,
                'categories' => $categories
            ]
        ]);
    }

    /**
     * Get inventory overview stats
     */
    public function getOverview(Request $request)
    {
        $query = FactInventorySnapshot::query();

        // Apply filters - check for non-empty values
        if ($request->filled('warehouse_id')) {
            $query->where('warehouse_id', $request->warehouse_id);
        }

        if ($request->filled('category')) {
            $query->whereHas('product', function ($q) use ($request) {
                $q->where('category', $request->category);
            });
        }

        if ($request->filled('date')) {
            $dateId = str_replace('-', '', $request->date);
            $query->where('date_id', $dateId);
        } else {
            // Get latest snapshot if no date filter
            $latestDateId = FactInventorySnapshot::max('date_id');
            if ($latestDateId) {
                $query->where('date_id', $latestDateId);
            }
        }

        $total_qty = $query->sum('quantity_on_hand');
        $total_value = $query->sum('value_on_hand');

        return response()->json([
            'success' => true,
            'data' => [
                'total_qty' => $total_qty,
                'total_value' => $total_value,
            ]
        ]);
    }

    /**
     * Get quantity by date
     */
    public function getQtyByDate(Request $request)
    {
        $query = FactInventorySnapshot::query();

        // Apply filters BEFORE date filtering
        if ($request->filled('warehouse_id')) {
            $query->where('warehouse_id', $request->warehouse_id);
        }

        if ($request->filled('category')) {
            $query->whereHas('product', function ($q) use ($request) {
                $q->where('category', $request->category);
            });
        }

        // Build date list
        $dateQuery = DimDate::query();

        if ($request->filled('date')) {
            // Jika ada filter date: tampilkan HANYA tanggal yang dipilih
            $dateId = str_replace('-', '', $request->date);
            $dateQuery->where('date_id', $dateId);
        } else {
            // Jika TIDAK ada filter date: tampilkan tanggal 1 setiap bulan
            $dateQuery->whereRaw('DAY(full_date) = 1');
        }

        $dateIds = $dateQuery->orderBy('date_id')->pluck('date_id')->toArray();

        if (empty($dateIds)) {
            return response()->json([
                'success' => true,
                'data' => [
                    'labels' => [],
                    'values' => []
                ]
            ]);
        }

        $query->whereIn('date_id', $dateIds);

        $rows = $query->select('date_id', DB::raw('SUM(quantity_on_hand) as total_qty'))
            ->groupBy('date_id')
            ->orderBy('date_id')
            ->get()
            ->keyBy('date_id');

        $labels = array_map(function ($d) {
            $dm = DimDate::find($d);
            return $dm ? $dm->full_date->format('d M Y') : $d;
        }, $dateIds);

        $values = array_map(function ($d) use ($rows) {
            return isset($rows[$d]) ? (int) $rows[$d]->total_qty : 0;
        }, $dateIds);

        return response()->json([
            'success' => true,
            'data' => [
                'labels' => $labels,
                'values' => $values
            ]
        ]);
    }

    /**
     * Get quantity by warehouse
     */
    public function getQtyByWarehouse(Request $request)
    {
        $query = FactInventorySnapshot::query();

        if ($request->filled('category')) {
            $query->whereHas('product', function ($q) use ($request) {
                $q->where('category', $request->category);
            });
        }

        if ($request->filled('date')) {
            $dateId = str_replace('-', '', $request->date);
            $query->where('date_id', $dateId);
        } else {
            // Get latest snapshot if no date filter
            $latestDateId = FactInventorySnapshot::max('date_id');
            if ($latestDateId) {
                $query->where('date_id', $latestDateId);
            }
        }

        $data = $query->select('warehouse_id', DB::raw('SUM(quantity_on_hand) as total_qty'))
            ->with('warehouse')
            ->groupBy('warehouse_id')
            ->orderByDesc('total_qty')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'labels' => $data->pluck('warehouse.warehouse_name')->values(),
                'values' => $data->pluck('total_qty')->values()
            ]
        ]);
    }

    /**
     * Get quantity by date & warehouse (stacked / 3-dimension)
     */
    public function getQtyByDateWarehouse(Request $request)
    {
        $query = FactInventorySnapshot::query();

        // Apply filters BEFORE building date list
        if ($request->filled('category')) {
            $query->whereHas('product', function ($q) use ($request) {
                $q->where('category', $request->category);
            });
        }

        if ($request->filled('warehouse_id')) {
            $query->where('warehouse_id', $request->warehouse_id);
        }

        // Build date list
        $dateQuery = DimDate::query();

        if ($request->filled('date')) {
            // Jika ada filter date: tampilkan HANYA tanggal yang dipilih
            $dateId = str_replace('-', '', $request->date);
            $dateQuery->where('date_id', $dateId);
        } else {
            // Jika TIDAK ada filter date: tampilkan tanggal 1 setiap bulan
            $dateQuery->whereRaw('DAY(full_date) = 1');
        }

        $dateIds = $dateQuery->orderBy('date_id')->pluck('date_id')->toArray();

        if (empty($dateIds)) {
            return response()->json([
                'success' => true,
                'data' => [
                    'labels' => [],
                    'warehouses' => [],
                    'data' => new \stdClass()
                ]
            ]);
        }

        $query->whereIn('date_id', $dateIds);

        $rows = $query->select('date_id', 'warehouse_id', DB::raw('SUM(quantity_on_hand) as total_qty'))
            ->with('warehouse')
            ->groupBy('date_id', 'warehouse_id')
            ->orderBy('date_id')
            ->get();

        // Ordered unique date_ids already available as $dateIds
        $labels = array_map(function ($d) {
            $dm = DimDate::find($d);
            return $dm ? $dm->full_date->format('d M Y') : $d;
        }, $dateIds);

        // Collect unique warehouses
        $warehouses = $rows->map(function ($r) {
            return $r->warehouse->warehouse_name ?? ('WH ' . $r->warehouse_id);
        })->unique()->values()->all();

        // Initialize data mapping
        $dataMap = [];
        foreach ($warehouses as $wh) {
            $dataMap[$wh] = array_fill(0, count($dateIds), 0);
        }

        // Fill values
        foreach ($rows as $row) {
            $whName = $row->warehouse->warehouse_name ?? ('WH ' . $row->warehouse_id);
            $dateIndex = array_search($row->date_id, $dateIds, true);
            if ($dateIndex !== false) {
                $dataMap[$whName][$dateIndex] = (int) $row->total_qty;
            }
        }

        return response()->json([
            'success' => true,
            'data' => [
                'labels' => $labels,
                'warehouses' => $warehouses,
                'data' => $dataMap
            ]
        ]);
    }

    /**
     * Get value by warehouse
     */
    public function getValueByWarehouse(Request $request)
    {
        $query = FactInventorySnapshot::query();

        if ($request->filled('category')) {
            $query->whereHas('product', function ($q) use ($request) {
                $q->where('category', $request->category);
            });
        }

        if ($request->filled('date')) {
            $dateId = str_replace('-', '', $request->date);
            $query->where('date_id', $dateId);
        } else {
            // Get latest snapshot if no date filter
            $latestDateId = FactInventorySnapshot::max('date_id');
            if ($latestDateId) {
                $query->where('date_id', $latestDateId);
            }
        }

        $data = $query->select('warehouse_id', DB::raw('SUM(value_on_hand) as total_value'))
            ->with('warehouse')
            ->groupBy('warehouse_id')
            ->orderByDesc('total_value')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'labels' => $data->pluck('warehouse.warehouse_name')->values(),
                'values' => $data->pluck('total_value')->values()
            ]
        ]);
    }

    /**
     * Get top 10 products by quantity
     */
    public function getTopProducts(Request $request)
    {
        $query = FactInventorySnapshot::query();

        if ($request->filled('warehouse_id')) {
            $query->where('warehouse_id', $request->warehouse_id);
        }

        if ($request->filled('date')) {
            $dateId = str_replace('-', '', $request->date);
            $query->where('date_id', $dateId);
        } else {
            // Get latest snapshot if no date filter
            $latestDateId = FactInventorySnapshot::max('date_id');
            if ($latestDateId) {
                $query->where('date_id', $latestDateId);
            }
        }

        $data = $query->select('product_id', DB::raw('SUM(quantity_on_hand) as total_qty'))
            ->with('product')
            ->groupBy('product_id')
            ->orderByDesc('total_qty')
            ->take(10)
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'labels' => $data->pluck('product.product_description')->values(),
                'values' => $data->pluck('total_qty')->values()
            ]
        ]);
    }

    /**
     * Get warehouse and category distribution
     */
    public function getWarehouseCategoryData(Request $request)
    {
        $query = FactInventorySnapshot::query();

        if ($request->filled('warehouse_id')) {
            $query->where('warehouse_id', $request->warehouse_id);
        }

        if ($request->filled('date')) {
            $dateId = str_replace('-', '', $request->date);
            $query->where('date_id', $dateId);
        } else {
            // Get latest snapshot if no date filter
            $latestDateId = FactInventorySnapshot::max('date_id');
            if ($latestDateId) {
                $query->where('date_id', $latestDateId);
            }
        }

        $data = $query->select('warehouse_id', 'product_id', DB::raw('SUM(quantity_on_hand) as total_qty'))
            ->with(['warehouse', 'product'])
            ->groupBy('warehouse_id', 'product_id')
            ->get();

        // Prepare unique warehouses and categories
        $warehouses = $data->map(function ($d) {
            return $d->warehouse->warehouse_name ?? ('WH ' . $d->warehouse_id);
        })->unique()->values()->all();

        $categories = $data->map(function ($d) {
            return $d->product->category ?? 'Uncategorized';
        })->unique()->values()->all();

        $datasets = [];
        $colors = ['rgb(59, 130, 246)', 'rgb(16, 185, 129)', 'rgb(239, 68, 68)', 'rgb(249, 115, 22)', 'rgb(139, 92, 246)', 'rgb(236, 72, 153)'];

        foreach ($categories as $idx => $category) {
            $values = [];
            foreach ($warehouses as $warehouseName) {
                $sum = $data->filter(function ($row) use ($warehouseName, $category) {
                    $wName = $row->warehouse->warehouse_name ?? ('WH ' . $row->warehouse_id);
                    $cat = $row->product->category ?? 'Uncategorized';
                    return $wName === $warehouseName && $cat === $category;
                })->sum('total_qty');

                $values[] = (int) $sum;
            }

            $datasets[] = [
                'label' => $category,
                'data' => $values,
                'backgroundColor' => $colors[$idx % count($colors)]
            ];
        }

        return response()->json([
            'success' => true,
            'data' => [
                'labels' => $warehouses,
                'datasets' => $datasets
            ]
        ]);
    }
}