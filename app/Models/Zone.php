<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Zone extends Model
{
    protected $fillable = [
        'region_id',
        'name',
        'status',
    ];

    public function region()
    {
        return $this->belongsTo(Region::class);
    }

    public function divisions()
    {
        return $this->hasMany(Division::class);
    }
}
