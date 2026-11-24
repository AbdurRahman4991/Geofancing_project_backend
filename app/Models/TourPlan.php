<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TourPlan extends Model
{
    protected $fillable = [
        'company_id', 'user_id', 'created_by', 'plan_date', 'status', 'notes',
        'reviewed_by', 'reviewed_at', 'review_note',
    ];

    protected $casts = [
        'plan_date' => 'date',
        'reviewed_at' => 'datetime',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function stops(): HasMany
    {
        return $this->hasMany(TourPlanStop::class)->orderBy('sequence');
    }
}
