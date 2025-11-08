<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FactSales extends Model
{
    protected $table = 'fact_sales';
    protected $primaryKey = 'transaction_id';
    public $timestamps = false;
    public $incrementing = false;

    protected $fillable = [
        'transaction_id',
        'date_id',
        'product_id',
        'store_id',
        'promotion_id',
        'quantity_sold',
        'unit_price',
        'unit_cost',
        'total_amount',
        'total_cost',
        'gross_profit',
        'discount_amount'
    ];

    protected $casts = [
        'unit_price' => 'float',
        'unit_cost' => 'float',
        'total_amount' => 'float',
        'total_cost' => 'float',
        'gross_profit' => 'float',
        'discount_amount' => 'float'
    ];

    // Relationships
    public function date()
    {
        return $this->belongsTo(DimDate::class, 'date_id', 'date_id');
    }

    public function product()
    {
        return $this->belongsTo(DimProduct::class, 'product_id', 'product_id');
    }

    public function store()
    {
        return $this->belongsTo(DimStore::class, 'store_id', 'store_id');
    }

    public function promotion()
    {
        return $this->belongsTo(DimPromotion::class, 'promotion_id', 'promotion_id');
    }

    // Scopes
    public function scopeByRegion($query, $region)
    {
        return $query->whereHas('store', function ($q) use ($region) {
            $q->where('region', $region);
        });
    }

    public function scopeByCategory($query, $category)
    {
        return $query->whereHas('product', function ($q) use ($category) {
            $q->where('category', $category);
        });
    }

    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereHas('date', function ($q) use ($startDate, $endDate) {
            $q->whereBetween('full_date', [$startDate, $endDate]);
        });
    }
}