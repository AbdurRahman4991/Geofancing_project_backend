<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Geofence extends Model
{
        protected $fillable = [
        'company_id',
        'user_id',
        'latitude',
        'longitude',
        'radius',
    ];

     public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
