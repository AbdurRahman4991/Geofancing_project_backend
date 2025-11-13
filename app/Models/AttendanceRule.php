<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AttendanceRule extends Model
{
      protected $fillable = [
        'user_id',
        'company_id',
        'office_in_time',
        'office_out_time',
        'weekend_holidays',
        'government_holidays',
        'is_active',
    ];

    protected $casts = [
        'weekend_holidays' => 'array',
        'government_holidays' => 'array',
        'is_active' => 'boolean',
    ];

    // 🔹 User সম্পর্ক
    public function user()
    {
        return $this->belongsTo(User::class);
    }    
    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}
