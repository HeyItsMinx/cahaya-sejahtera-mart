<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FactProcurement extends Model
{
    protected $table = 'fact_procurement';
    public $timestamps = false;
    public $incrementing = false;

    // Composite primary key
    protected $primaryKey = ['purchase_order_id', 'product_id'];

    protected $fillable = [
        'product_id',
        'vendor_id',
        'warehouse_id',
        'purchase_order_id',
        'purchase_order_date_id',
        'warehouse_receipt_date_id',
        'vendor_invoice_date_id',
        'vendor_payment_date_id',
        'quantity_ordered',
        'total_cost',
        'order_to_receipt_lag_days',
        'receipt_to_invoice_lag_days',
        'invoice_to_payment_lag_days',
        'total_procurement_lag_days'
    ];

    protected $casts = [
        'total_cost' => 'float'
    ];

    // Relationships
    public function product()
    {
        return $this->belongsTo(DimProduct::class, 'product_id', 'product_id');
    }

    public function vendor()
    {
        return $this->belongsTo(DimVendor::class, 'vendor_id', 'vendor_id');
    }

    public function warehouse()
    {
        return $this->belongsTo(DimWarehouse::class, 'warehouse_id', 'warehouse_id');
    }

    public function purchaseOrderDate()
    {
        return $this->belongsTo(DimDate::class, 'purchase_order_date_id', 'date_id');
    }

    public function warehouseReceiptDate()
    {
        return $this->belongsTo(DimDate::class, 'warehouse_receipt_date_id', 'date_id');
    }

    public function vendorInvoiceDate()
    {
        return $this->belongsTo(DimDate::class, 'vendor_invoice_date_id', 'date_id');
    }

    public function vendorPaymentDate()
    {
        return $this->belongsTo(DimDate::class, 'vendor_payment_date_id', 'date_id');
    }
}