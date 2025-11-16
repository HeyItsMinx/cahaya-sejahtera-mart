<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\FactProcurement;

class ProcurementController extends Controller
{
    public function chartPage()
    {
        return view('procurement.chart');
    }

    public function chartLeadTimeByVendor(Request $request)
    {
        try {
            $months = (int) $request->get('months', 12);
            $driver = DB::getDriverName();

            if ($driver === 'sqlite') {
                $sql = "
                    SELECT 
                        CAST(strftime('%Y', d.full_date) AS INTEGER) as year,
                        CAST(strftime('%m', d.full_date) AS INTEGER) as month,
                        v.vendor_name,
                        ROUND(AVG(CAST(fp.order_to_receipt_lag_days AS REAL)), 2) as avg_lead_time
                    FROM fact_procurement fp
                    JOIN dim_date d ON fp.purchase_order_date_id = d.date_id
                    JOIN dim_vendor v ON fp.vendor_id = v.vendor_id
                    WHERE fp.order_to_receipt_lag_days IS NOT NULL
                    AND d.full_date >= datetime('now', '-' || ? || ' months')
                    GROUP BY year, month, v.vendor_name
                    ORDER BY year ASC, month ASC
                ";
                $data = DB::select($sql, [$months]);
            } else {
                $sql = "
                    SELECT 
                        EXTRACT(YEAR FROM d.full_date) as year,
                        EXTRACT(MONTH FROM d.full_date) as month,
                        v.vendor_name,
                        ROUND(AVG(fp.order_to_receipt_lag_days), 2) as avg_lead_time
                    FROM fact_procurement fp
                    JOIN dim_date d ON fp.purchase_order_date_id = d.date_id
                    JOIN dim_vendor v ON fp.vendor_id = v.vendor_id
                    WHERE fp.order_to_receipt_lag_days IS NOT NULL
                    AND d.full_date >= DATE_SUB(NOW(), INTERVAL ? MONTH)
                    GROUP BY EXTRACT(YEAR FROM d.full_date), EXTRACT(MONTH FROM d.full_date), v.vendor_name
                    ORDER BY year ASC, month ASC
                ";
                $data = DB::select($sql, [$months]);
            }

            // Format data untuk Chart.js
            $monthLabels = [];
            $vendorData = [];

            foreach ($data as $row) {
                $monthKey = sprintf('%04d-%02d', $row->year, $row->month);
                if (!in_array($monthKey, $monthLabels)) {
                    $monthLabels[] = $monthKey;
                }

                if (!isset($vendorData[$row->vendor_name])) {
                    $vendorData[$row->vendor_name] = [];
                }
            }

            foreach ($data as $row) {
                $monthKey = sprintf('%04d-%02d', $row->year, $row->month);
                $monthIndex = array_search($monthKey, $monthLabels);
                $vendorData[$row->vendor_name][$monthIndex] = $row->avg_lead_time;
            }

            // Ensure all vendors have same number of data points
            foreach ($vendorData as &$values) {
                for ($i = 0; $i < count($monthLabels); $i++) {
                    if (!isset($values[$i])) {
                        $values[$i] = null;
                    }
                }
                ksort($values);
                $values = array_values($values);
            }

            return response()->json([
                'success' => true,
                'months' => $monthLabels,
                'vendors' => $vendorData
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }
}
