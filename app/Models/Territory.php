<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Territory extends Model
{
    protected $fillable = [
        'sub_district_id',
        'name',
        'status',
    ];

    public function subDistrict()
    {
        return $this->belongsTo(SubDistrict::class);
    }

    public function areas()
    {
        return $this->hasMany(Area::class);
    }
}
