<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Area extends Model
{
    protected $fillable = [
        'territory_id',
        'name',
        'status',
    ];

    public function territory()
    {
        return $this->belongsTo(Territory::class);
    }

    public function farms()
    {
        return $this->hasMany(Farm::class);
    }
}
