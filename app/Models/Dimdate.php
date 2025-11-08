<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DimDate extends Model
{
    protected $table = 'dim_date';
    protected $primaryKey = 'date_id';
    public $timestamps = false;
    public $incrementing = false;

    protected $fillable = [
        'date_id',
        'full_date',
        'day_of_week',
        'day_number_in_month',
        'month_name',
        'month_number',
        'quarter',
        'year',
        'is_weekend',
        'is_holiday'
    ];

    protected $casts = [
        'full_date' => 'date',
        'is_weekend' => 'boolean',
        'is_holiday' => 'boolean'
    ];

    // Relationships
    public function sales()
    {
        return $this->hasMany(FactSales::class, 'date_id', 'date_id');
    }

    public function inventorySnapshots()
    {
        return $this->hasMany(FactInventorySnapshot::class, 'date_id', 'date_id');
    }

    public function promotionCoverages()
    {
        return $this->hasMany(FactPromotionCoverage::class, 'date_id', 'date_id');
    }
}