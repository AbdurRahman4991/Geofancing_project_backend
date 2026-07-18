<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
        protected $fillable = [
        'user_id',
        'check_in_time',
        'check_in_latitude',
        'check_in_longitude',
        'geofence_id',
        'check_out_time',
        'check_out_latitude',
        'check_out_longitude',
        'status',
        'distance_from_office',
        'device_id',
        'remarks',
        'late',
        'work_hour'
    ];

    protected $casts = [
    'check_in_time' => 'datetime',
    'check_out_time' => 'datetime',
    ];

    // public function user()
    // {
    //     return $this->belongsTo(User::class, 'employee_id');
    // }
     public function user()
    {
        return $this->belongsTo(User::class);
    }
}
