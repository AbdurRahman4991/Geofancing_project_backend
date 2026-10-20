<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeHierarchyAssignment extends Model
{
    protected $fillable = [
        'user_id',
        'company_id',
        'hierarchy_node_id',        
        'is_current',
        'assigned_by',        
    ];

    protected $casts = [
        'is_current'    => 'boolean',
    ];
    
    public function assignedBy()
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    public function hierarchyNode()
    {
        return $this->belongsTo(HierarchyNode::class, 'hierarchy_node_id');
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}
