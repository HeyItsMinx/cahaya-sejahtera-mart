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
            
            // Query tanpa filter bulan - ambil semua data, tapi batasi dengan LIMIT
            $sql = "
                SELECT 
                    YEAR(STR_TO_DATE(CAST(fp.purchase_order_date_id AS CHAR), '%Y%m%d')) as year,
                    MONTH(STR_TO_DATE(CAST(fp.purchase_order_date_id AS CHAR), '%Y%m%d')) as month,
                    v.vendor_name,
                    ROUND(AVG(fp.order_to_receipt_lag_days), 2) as avg_lead_time
                FROM fact_procurement fp
                JOIN dim_vendor v ON fp.vendor_id = v.vendor_id
                WHERE fp.order_to_receipt_lag_days IS NOT NULL
                GROUP BY YEAR(STR_TO_DATE(CAST(fp.purchase_order_date_id AS CHAR), '%Y%m%d')), 
                         MONTH(STR_TO_DATE(CAST(fp.purchase_order_date_id AS CHAR), '%Y%m%d')), 
                         v.vendor_name
                ORDER BY year ASC, month ASC
            ";
            $data = DB::select($sql);

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

            // Hanya ambil N bulan terakhir dari hasil query
            $totalMonths = count($monthLabels);
            if ($totalMonths > $months) {
                $monthLabels = array_slice($monthLabels, -$months);
            }

            foreach ($data as $row) {
                $monthKey = sprintf('%04d-%02d', $row->year, $row->month);
                if (in_array($monthKey, $monthLabels)) {
                    $monthIndex = array_search($monthKey, $monthLabels);
                    $vendorData[$row->vendor_name][$monthIndex] = $row->avg_lead_time;
                }
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
