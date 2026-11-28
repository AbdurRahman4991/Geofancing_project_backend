<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApprovalWorkflowStep extends Model
{
    protected $fillable = ['workflow_id', 'step_order', 'name', 'approver_type', 'department_id', 'approver_employee_id', 'approver_role'];

    public function workflow(): BelongsTo { return $this->belongsTo(ApprovalWorkflow::class, 'workflow_id'); }
    public function department(): BelongsTo { return $this->belongsTo(Department::class); }
    public function approverEmployee(): BelongsTo { return $this->belongsTo(Employee::class, 'approver_employee_id'); }
}
