<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DimProduct extends Model
{
    protected $table = 'dim_product';
    protected $primaryKey = 'product_id';
    public $timestamps = false;
    public $incrementing = false;

    protected $fillable = [
        'product_id',
        'sku_number',
        'product_description',
        'category',
        'subcategory',
        'storage_type'
    ];

    // Relationships
    public function sales()
    {
        return $this->hasMany(FactSales::class, 'product_id', 'product_id');
    }

    public function inventorySnapshots()
    {
        return $this->hasMany(FactInventorySnapshot::class, 'product_id', 'product_id');
    }

    public function procurements()
    {
        return $this->hasMany(FactProcurement::class, 'product_id', 'product_id');
    }

    public function promotionCoverages()
    {
        return $this->hasMany(FactPromotionCoverage::class, 'product_id', 'product_id');
    }
}