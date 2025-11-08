<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DimVendor extends Model
{
    protected $table = 'dim_vendor';
    protected $primaryKey = 'vendor_id';
    public $timestamps = false;
    public $incrementing = false;

    protected $fillable = [
        'vendor_id',
        'vendor_name',
        'address',
        'city',
        'region',
        'phone_number'
    ];

    // Relationships
    public function procurements()
    {
        return $this->hasMany(FactProcurement::class, 'vendor_id', 'vendor_id');
    }
}