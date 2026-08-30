<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class District extends Model
{
    protected $fillable = [
        'division_id',
        'name',
        'status',
    ];

    public function division()
    {
        return $this->belongsTo(Division::class);
    }

    public function subDistricts()
    {
        return $this->hasMany(SubDistrict::class);
    }
}
