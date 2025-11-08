<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DimWarehouse extends Model
{
    protected $table = 'dim_warehouse';
    protected $primaryKey = 'warehouse_id';
    public $timestamps = false;
    public $incrementing = false;

    protected $fillable = [
        'warehouse_id',
        'warehouse_name',
        'location_city',
        'location_region'
    ];

    // Relationships
    public function inventorySnapshots()
    {
        return $this->hasMany(FactInventorySnapshot::class, 'warehouse_id', 'warehouse_id');
    }

    public function procurements()
    {
        return $this->hasMany(FactProcurement::class, 'warehouse_id', 'warehouse_id');
    }
}