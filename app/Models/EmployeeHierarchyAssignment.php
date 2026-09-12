<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeHierarchyAssignment extends Model
{
    protected $fillable = [
       // 'employee_id',
        'country_id',
        'region_id',
        'zone_id',
        'division_id',
        'district_id',
        'sub_district_id',
        'territory_id',
        'area_id',
        'effective_from',
        'effective_to',
        'is_current',
        'assigned_by',
        'reason',
    ];

    protected $casts = [
        'effective_from' => 'date',
        'effective_to'   => 'date',
        'is_current'    => 'boolean',
    ];

    // public function employee()
    // {
    //     return $this->belongsTo(Employee::class);
    // }

    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    public function region()
    {
        return $this->belongsTo(Region::class);
    }

    public function zone()
    {
        return $this->belongsTo(Zone::class);
    }

    public function division()
    {
        return $this->belongsTo(Division::class);
    }

    public function district()
    {
        return $this->belongsTo(District::class);
    }

    public function subDistrict()
    {
        return $this->belongsTo(SubDistrict::class);
    }

    public function territory()
    {
        return $this->belongsTo(Territory::class);
    }

    public function area()
    {
        return $this->belongsTo(Area::class);
    }

    public function assignedBy()
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }
}
