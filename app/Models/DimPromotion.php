<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DimPromotion extends Model
{
    protected $table = 'dim_promotion';
    protected $primaryKey = 'promotion_id';
    public $timestamps = false;
    public $incrementing = false;

    protected $fillable = [
        'promotion_id',
        'promotion_name',
        'start_date',
        'end_date'
    ];

    // Relationships
    public function startDate()
    {
        return $this->belongsTo(DimDate::class, 'start_date', 'date_id');
    }

    public function endDate()
    {
        return $this->belongsTo(DimDate::class, 'end_date', 'date_id');
    }

    public function sales()
    {
        return $this->hasMany(FactSales::class, 'promotion_id', 'promotion_id');
    }

    public function promotionCoverages()
    {
        return $this->hasMany(FactPromotionCoverage::class, 'promotion_id', 'promotion_id');
    }
}