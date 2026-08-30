<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class division extends Model
{
    protected $fillable = [
        'zone_id',
        'name',
        'status',
    ];

    public function zone()
    {
        return $this->belongsTo(Zone::class);
    }

    public function districts()
    {
        return $this->hasMany(District::class);
    }
}
