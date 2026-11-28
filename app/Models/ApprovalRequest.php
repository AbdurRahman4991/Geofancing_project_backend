<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ApprovalRequest extends Model
{
    protected $fillable = ['company_id', 'workflow_id', 'module', 'document_id', 'requested_by', 'current_step', 'status', 'submitted_at', 'completed_at'];
    protected $casts = ['submitted_at' => 'datetime', 'completed_at' => 'datetime', 'current_step' => 'integer'];

    public function workflow(): BelongsTo { return $this->belongsTo(ApprovalWorkflow::class, 'workflow_id'); }
    public function requester(): BelongsTo { return $this->belongsTo(User::class, 'requested_by'); }
    public function steps(): HasMany { return $this->hasMany(ApprovalRequestStep::class)->orderBy('step_order'); }
    public function currentStep(): HasMany { return $this->steps()->where('step_order', $this->current_step); }
}
