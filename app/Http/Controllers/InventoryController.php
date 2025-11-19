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
        Log::info('InventoryController@getOverview called', $request->all());
        $query = FactInventorySnapshot::query();

        // Apply filters
        if ($request->warehouse_id) {
            $query->where('warehouse_id', $request->warehouse_id);
        }

        if ($request->category) {
            $query->whereHas('product', function ($q) use ($request) {
                $q->where('category', $request->category);
            });
        }

        if ($request->date_range) {
            $dates = explode(' to ', $request->date_range);
            if (count($dates) == 2) {
                $query->whereBetween('date_id', [
                    str_replace('-', '', $dates[0]),
                    str_replace('-', '', $dates[1])
                ]);
            }
        }

        // Get latest snapshot if no date filter
        if (!$request->date_range) {
            $latestDateId = FactInventorySnapshot::max('date_id');
            if ($latestDateId) {
                $query->where('date_id', $latestDateId);
            }
        }

        $total_qty = $query->sum('quantity_on_hand');
        $total_value = $query->sum('value_on_hand');
        $active_products = $query->distinct('product_id')->count('product_id');
        $total_warehouses = $query->distinct('warehouse_id')->count('warehouse_id');
        $avg_qty = $active_products > 0 ? $total_qty / $active_products : 0;
        $avg_value = $total_warehouses > 0 ? $total_value / $total_warehouses : 0;

        return response()->json([
            'success' => true,
            'data' => [
                'total_qty' => $total_qty,
                'total_value' => $total_value,
                'active_products' => $active_products,
                'total_warehouses' => $total_warehouses,
                'avg_qty' => $avg_qty,
                'avg_value' => $avg_value
            ]
        ]);
    }

    /**
     * Get quantity by date
     */
    public function getQtyByDate(Request $request)
    {
        Log::info('InventoryController@getQtyByDate called', $request->all());
        $query = FactInventorySnapshot::query();

        if ($request->warehouse_id) {
            $query->where('warehouse_id', $request->warehouse_id);
        }

        if ($request->category) {
            $query->whereHas('product', function ($q) use ($request) {
                $q->where('category', $request->category);
            });
        }

        if ($request->date_range) {
            $dates = explode(' to ', $request->date_range);
            if (count($dates) == 2) {
                $query->whereBetween('date_id', [
                    str_replace('-', '', $dates[0]),
                    str_replace('-', '', $dates[1])
                ]);
            }
        }

        $data = $query->select('date_id', DB::raw('SUM(quantity_on_hand) as total_qty'))
            ->groupBy('date_id')
            ->orderBy('date_id')
            ->get();

        $labels = $data->map(function ($item) {
            $dateModel = DimDate::find($item->date_id);
            return $dateModel ? $dateModel->full_date->format('d M Y') : $item->date_id;
        });

        return response()->json([
            'success' => true,
            'data' => [
                'labels' => $labels->values(),
                'values' => $data->pluck('total_qty')->values()
            ]
        ]);
    }

    /**
     * Get quantity by warehouse
     */
    public function getQtyByWarehouse(Request $request)
    {
        Log::info('InventoryController@getQtyByWarehouse called', $request->all());
        $query = FactInventorySnapshot::query();

        if ($request->category) {
            $query->whereHas('product', function ($q) use ($request) {
                $q->where('category', $request->category);
            });
        }

        if ($request->date_range) {
            $dates = explode(' to ', $request->date_range);
            if (count($dates) == 2) {
                $query->whereBetween('date_id', [
                    str_replace('-', '', $dates[0]),
                    str_replace('-', '', $dates[1])
                ]);
            }
        } else {
            // Get latest date
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
     *
     * Returns:
     * {
     *   labels: ['01 Jan 2025', ...],
     *   warehouses: ['WH A', 'WH B', ...],
     *   data: { 'WH A': [v1, v2, ...], 'WH B': [...] }
     * }
     */
    public function getQtyByDateWarehouse(Request $request)
    {
        Log::info('InventoryController@getQtyByDateWarehouse called', $request->all());
        $query = FactInventorySnapshot::query();

        if ($request->category) {
            $query->whereHas('product', function ($q) use ($request) {
                $q->where('category', $request->category);
            });
        }

        if ($request->warehouse_id) {
            $query->where('warehouse_id', $request->warehouse_id);
        }

        if ($request->date_range) {
            $dates = explode(' to ', $request->date_range);
            if (count($dates) == 2) {
                $query->whereBetween('date_id', [
                    str_replace('-', '', $dates[0]),
                    str_replace('-', '', $dates[1])
                ]);
            }
        } else {
            $latestDateId = FactInventorySnapshot::max('date_id');
            if ($latestDateId) {
                $query->where('date_id', $latestDateId);
            }
        }

        $rows = $query->select('date_id', 'warehouse_id', DB::raw('SUM(quantity_on_hand) as total_qty'))
            ->with('warehouse')
            ->groupBy('date_id', 'warehouse_id')
            ->orderBy('date_id')
            ->get();

        // collect ordered unique date_ids
        $dateIds = $rows->pluck('date_id')->unique()->sort()->values()->all();
        $labels = array_map(function ($d) {
            $dm = DimDate::find($d);
            return $dm ? $dm->full_date->format('d M Y') : $d;
        }, $dateIds);

        // collect unique warehouses (by name) in stable order
        $warehouses = $rows->map(function ($r) {
            return $r->warehouse->warehouse_name ?? ('WH ' . $r->warehouse_id);
        })->unique()->values()->all();

        // initialize data mapping
        $dataMap = [];
        foreach ($warehouses as $wh) {
            $dataMap[$wh] = array_fill(0, count($dateIds), 0);
        }

        // fill values
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

        if ($request->category) {
            $query->whereHas('product', function ($q) use ($request) {
                $q->where('category', $request->category);
            });
        }

        if ($request->date_range) {
            $dates = explode(' to ', $request->date_range);
            if (count($dates) == 2) {
                $query->whereBetween('date_id', [
                    str_replace('-', '', $dates[0]),
                    str_replace('-', '', $dates[1])
                ]);
            }
        } else {
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

        if ($request->warehouse_id) {
            $query->where('warehouse_id', $request->warehouse_id);
        }

        if ($request->date_range) {
            $dates = explode(' to ', $request->date_range);
            if (count($dates) == 2) {
                $query->whereBetween('date_id', [
                    str_replace('-', '', $dates[0]),
                    str_replace('-', '', $dates[1])
                ]);
            }
        } else {
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

        if ($request->warehouse_id) {
            $query->where('warehouse_id', $request->warehouse_id);
        }

        if ($request->date_range) {
            $dates = explode(' to ', $request->date_range);
            if (count($dates) == 2) {
                $query->whereBetween('date_id', [
                    str_replace('-', '', $dates[0]),
                    str_replace('-', '', $dates[1])
                ]);
            }
        } else {
            $latestDateId = FactInventorySnapshot::max('date_id');
            if ($latestDateId) {
                $query->where('date_id', $latestDateId);
            }
        }

        $data = $query->select('warehouse_id', 'product_id', DB::raw('SUM(quantity_on_hand) as total_qty'))
            ->with(['warehouse', 'product'])
            ->groupBy('warehouse_id', 'product_id')
            ->get();

        // prepare unique warehouses and categories
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