<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FactInventorySnapshot extends Model
{
    protected $table = 'fact_inventory_snapshot';
    public $timestamps = false;
    public $incrementing = false;

    // Composite primary key
    protected $primaryKey = ['date_id', 'product_id', 'warehouse_id'];

    protected $fillable = [
        'date_id',
        'product_id',
        'warehouse_id',
        'quantity_on_hand',
        'value_on_hand'
    ];

    protected $casts = [
        'value_on_hand' => 'float'
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

    public function warehouse()
    {
        return $this->belongsTo(DimWarehouse::class, 'warehouse_id', 'warehouse_id');
    }
}