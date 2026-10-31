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
        'department_id',
        'unit_id',
        'date_of_joining',
        'division_id',
        'designation_id',
        'reporting_person',
        'email',
        'dob',
        'section_info',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }
    public function user()
    {
        return $this->hasOne(User::class, 'employee_id', 'id');
    }
    // Employee.php

    public function locations()
    {
        return $this->hasManyThrough(
            EmployeeLocation::class,
            User::class,
            'employee_id',
            'user_id',
            'id',
            'id'
        );
    }
}
