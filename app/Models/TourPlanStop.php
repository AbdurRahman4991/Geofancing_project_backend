<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TourPlanStop extends Model
{
    protected $fillable = [
        'tour_plan_id', 'geofence_id', 'sequence', 'planned_arrival_time', 'notes',
    ];

    public function tourPlan(): BelongsTo
    {
        return $this->belongsTo(TourPlan::class);
    }

    public function geofence(): BelongsTo
    {
        return $this->belongsTo(Geofence::class);
    }
}
