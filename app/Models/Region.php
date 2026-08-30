<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Region extends Model
{
     protected $fillable = [
        'country_id',
        'name',
        'status',
    ];

    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    public function zones()
    {
        return $this->hasMany(Zone::class);
    }
}
