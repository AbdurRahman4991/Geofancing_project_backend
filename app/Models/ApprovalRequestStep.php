<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApprovalRequestStep extends Model
{
    protected $fillable = ['approval_request_id', 'step_order', 'name', 'approver_type', 'assigned_approver_ids', 'status', 'action_by', 'action_at', 'remarks'];
    protected $casts = ['assigned_approver_ids' => 'array', 'action_at' => 'datetime', 'step_order' => 'integer'];

    public function approvalRequest(): BelongsTo { return $this->belongsTo(ApprovalRequest::class); }
    public function actor(): BelongsTo { return $this->belongsTo(User::class, 'action_by'); }
}
