<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    protected $fillable = [
        'employee_id',
        'company_id',
        'phone',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }
    // Employee.php

    public function locations()
    {
        return $this->hasMany(EmployeeLocation::class);
    }
}
