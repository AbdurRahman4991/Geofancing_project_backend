<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeLocation extends Model
{
    protected $fillable = [
        'user_id',
        'company_id',
        'latitude',
        'longitude',
        'activity_type',
        'transport_mode',
        'speed',
        'accuracy',
        'recorded_at',
    ];

    protected $casts = [
        'latitude' => 'float',
        'longitude' => 'float',
        'speed' => 'float',
        'accuracy' => 'float',
        'recorded_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

}
