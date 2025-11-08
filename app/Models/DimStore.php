<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DimStore extends Model
{
    protected $table = 'dim_store';
    protected $primaryKey = 'store_id';
    public $timestamps = false;
    public $incrementing = false;

    protected $fillable = [
        'store_id',
        'store_name',
        'address',
        'city',
        'region'
    ];

    // Relationships
    public function sales()
    {
        return $this->hasMany(FactSales::class, 'store_id', 'store_id');
    }

    public function promotionCoverages()
    {
        return $this->hasMany(FactPromotionCoverage::class, 'store_id', 'store_id');
    }
}