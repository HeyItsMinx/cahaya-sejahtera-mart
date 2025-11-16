<?php

namespace App\Http\Controllers\Backends;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Carbon\Carbon;
use App\Models\FactProcurement;
use App\Models\DimProduct;
use App\Models\DimVendor;
use App\Models\DimWarehouse;
use App\Models\DimDate;

class ProcurementController extends Controller
{
    use ValidatesRequests;

    public function index()
    {
        // return a lightweight procurement table view
        return view('procurement.table');
    }

    /**
     * Return JSON data for procurement tracking (client-side DataTables).
     * Columns: product, vendor, warehouse, purchase_order_id, quantity_ordered, status, actions
     */
    public function datatable(Request $request)
    {
        $rows = FactProcurement::with(['product', 'vendor', 'warehouse'])->get()->map(function ($row) {
            $status = empty($row->warehouse_receipt_date_id)
                ? 'Ordered'
                : (empty($row->vendor_invoice_date_id) ? 'Receive' : (empty($row->vendor_payment_date_id) ? 'Invoice' : 'Complete'));

            $buttons = '';
            if ($status === 'Ordered') {
                $buttons .= '<button class="btn btn-sm btn-primary js-advance" data-step="receive" data-po="' . e($row->purchase_order_id) . '" data-product="' . e($row->product_id) . '">Mark Received</button> ';
            }
            if ($status === 'Receive') {
                $buttons .= '<button class="btn btn-sm btn-warning js-advance" data-step="invoice" data-po="' . e($row->purchase_order_id) . '" data-product="' . e($row->product_id) . '">Mark Invoiced</button> ';
            }
            if ($status === 'Invoice') {
                $buttons .= '<button class="btn btn-sm btn-success js-advance" data-step="complete" data-po="' . e($row->purchase_order_id) . '" data-product="' . e($row->product_id) . '">Mark Complete</button>';
            }

            return [
                'product' => optional($row->product)->product_description ?? '-',
                'vendor' => optional($row->vendor)->vendor_name ?? '-',
                'warehouse' => optional($row->warehouse)->warehouse_name ?? '-',
                'purchase_order_id' => $row->purchase_order_id,
                'quantity_ordered' => $row->quantity_ordered,
                'status' => $status,
                'actions' => $buttons
            ];
        });

        return response()->json(['data' => $rows]);
    }

    /**
     * Advance status by setting today's dim_date id on the appropriate column.
     * Request: purchase_order_id, product_id, step (receive|invoice|complete)
     */
    public function updateStatus(Request $request)
    {
        $data = $this->validate($request, [
            'purchase_order_id' => 'required|string',
            'product_id' => 'required|string',
            'step' => 'required|in:receive,invoice,complete'
        ]);

        $po = $data['purchase_order_id'];
        $product = $data['product_id'];
        $step = $data['step'];

        DB::beginTransaction();
        try {
            $row = FactProcurement::where('purchase_order_id', $po)
                ->where('product_id', $product)
                ->lockForUpdate()
                ->firstOrFail();

            $todayDimId = $this->getOrCreateTodayDimDateId();

            if ($step === 'receive') {
                if (!empty($row->warehouse_receipt_date_id)) {
                    return response()->json(['message' => 'Already received'], 422);
                }
                $row->warehouse_receipt_date_id = $todayDimId;
            } elseif ($step === 'invoice') {
                if (empty($row->warehouse_receipt_date_id)) {
                    return response()->json(['message' => 'Cannot invoice before receive'], 422);
                }
                if (!empty($row->vendor_invoice_date_id)) {
                    return response()->json(['message' => 'Already invoiced'], 422);
                }
                $row->vendor_invoice_date_id = $todayDimId;
            } elseif ($step === 'complete') {
                if (empty($row->vendor_invoice_date_id)) {
                    return response()->json(['message' => 'Cannot complete before invoice'], 422);
                }
                if (!empty($row->vendor_payment_date_id)) {
                    return response()->json(['message' => 'Already completed'], 422);
                }
                $row->vendor_payment_date_id = $todayDimId;
            }

            $row->save();
            DB::commit();

            return response()->json(['message' => 'Status updated', 'status' => true]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Procurement status update failed: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to update status'], 500);
        }
    }

    /**
     * Find or create today's DimDate and return its date_id.
     * Assumes date_id uses Ymd integer; adjust if your dim_date uses different ids.
     */
    private function getOrCreateTodayDimDateId()
    {
        $today = Carbon::today();
        $dateString = $today->toDateString();
        $dateId = intval($today->format('Ymd'));

        $dim = DimDate::firstOrCreate(
            ['full_date' => $dateString],
            [
                'date_id' => $dateId,
                'day_of_week' => $today->dayName,
                'day_number_in_month' => $today->day,
                'month_name' => $today->monthName,
                'month_number' => $today->month,
                'quarter' => (int) ceil($today->month / 3),
                'year' => $today->year,
                'is_weekend' => in_array($today->dayOfWeek, [Carbon::SATURDAY, Carbon::SUNDAY]),
                'is_holiday' => false
            ]
        );

        return $dim->date_id;
    }

    /**
     * Get average lead time per vendor per month for charting.
     * Returns: { months: [...], vendors: {...data arrays...} }
     */
    public function chartLeadTimeByVendor(Request $request)
    {
        // Get last 12 months of data
        $query = FactProcurement::with(['vendor', 'purchaseOrderDate'])
            ->selectRaw('
                EXTRACT(YEAR FROM d.full_date) as year,
                EXTRACT(MONTH FROM d.full_date) as month,
                v.vendor_name,
                AVG(fp.order_to_receipt_lag_days) as avg_lead_time,
                COUNT(*) as record_count
            ')
            ->join('dim_date as d', 'fp.purchase_order_date_id', '=', 'd.date_id')
            ->join('dim_vendor as v', 'fp.vendor_id', '=', 'v.vendor_id')
            ->whereNotNull('fp.order_to_receipt_lag_days')
            ->groupBy('year', 'month', 'v.vendor_name')
            ->orderBy('year', 'asc')
            ->orderBy('month', 'asc');

        // Optional: filter by month range
        if ($request->has('months')) {
            $months = (int) $request->get('months', 12);
            $startDate = Carbon::now()->subMonths($months)->startOfMonth();
            $query->where('d.full_date', '>=', $startDate);
        }

        $data = $query->get();

        // Format for Chart.js
        $months = [];
        $vendorData = [];

        foreach ($data as $row) {
            $monthKey = sprintf('%04d-%02d', $row->year, $row->month);
            if (!in_array($monthKey, $months)) {
                $months[] = $monthKey;
            }

            if (!isset($vendorData[$row->vendor_name])) {
                $vendorData[$row->vendor_name] = array_fill(0, count($months), null);
            }
        }

        // Fill vendor data points
        foreach ($data as $row) {
            $monthKey = sprintf('%04d-%02d', $row->year, $row->month);
            $monthIndex = array_search($monthKey, $months);
            $vendorData[$row->vendor_name][$monthIndex] = round($row->avg_lead_time, 2);
        }

        // Ensure all vendors have same number of data points
        foreach ($vendorData as &$values) {
            while (count($values) < count($months)) {
                $values[] = null;
            }
        }

        return response()->json([
            'months' => $months,
            'vendors' => $vendorData
        ]);
    }

    /**
     * Show chart view page
     */
    public function chartPage()
    {
        return view('procurement.chart-lead-time');
    }
}