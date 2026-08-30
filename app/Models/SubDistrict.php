<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubDistrict extends Model
{
    protected $fillable = [
        'district_id',
        'name',
        'status',
    ];

    public function district()
    {
        return $this->belongsTo(District::class);
    }

    public function territories()
    {
        return $this->hasMany(Territory::class);
    }
}
