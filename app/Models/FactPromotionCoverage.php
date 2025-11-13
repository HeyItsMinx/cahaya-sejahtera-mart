<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FactPromotionCoverage extends Model
{
    protected $table = 'fact_promotion_coverage';
    public $timestamps = false;
    public $incrementing = false;

    // Composite primary key
    protected $primaryKey = ['product_id', 'store_id', 'promotion_id'];

    protected $fillable = [
        'date_id',
        'product_id',
        'store_id',
        'promotion_id'
    ];


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

    public function sales()
    {
        return $this->hasOne(FactSales::class)
            ->where('product_id', $this->product_id)
            ->where('store_id', $this->store_id)
            ->where('promotion_id', $this->promotion_id);
    }
}