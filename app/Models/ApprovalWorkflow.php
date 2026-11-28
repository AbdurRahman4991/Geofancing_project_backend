<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ApprovalWorkflow extends Model
{
    protected $fillable = ['company_id', 'name', 'module', 'is_active'];
    protected $casts = ['is_active' => 'boolean'];

    public function company(): BelongsTo { return $this->belongsTo(Company::class); }
    public function steps(): HasMany { return $this->hasMany(ApprovalWorkflowStep::class, 'workflow_id')->orderBy('step_order'); }
    public function requests(): HasMany { return $this->hasMany(ApprovalRequest::class, 'workflow_id'); }
}
