<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MarketVisit extends Model
{
 protected $fillable = [
        // Company & Employee
        'company_id',
        'user_id',
        'geofence_id',

        // Check-in Location
        'check_in_latitude',
        'check_in_longitude',

        // Check-out Location
        'check_out_latitude',
        'check_out_longitude',

        // Visit Time
        'visit_start_time',
        'visit_end_time',

        // Visit Duration
        'duration',

        // Device Information
        'device_id',
    ];

    protected $casts = [
        // GPS Coordinates
        'check_in_latitude' => 'decimal:7',
        'check_in_longitude' => 'decimal:7',
        'check_out_latitude' => 'decimal:7',
        'check_out_longitude' => 'decimal:7',

        // Date & Time
        'visit_start_time' => 'datetime',
        'visit_end_time' => 'datetime',

        // Duration in minutes
        'duration' => 'decimal:2',
    ];

    public function geofence()
    {
        return $this->belongsTo(Geofence::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
