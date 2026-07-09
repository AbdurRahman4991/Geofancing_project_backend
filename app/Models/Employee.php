<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    protected $fillable = [
        'name',
        'employee_id',
        'company_id',
        'phone',
        'status',
        'nature_of_employment',
        'department',
        'unit',
        'date_of_joining',
        'division',
        'designation',
        'reporting_person',
        'email',
        'dob',
        'section_info',
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
