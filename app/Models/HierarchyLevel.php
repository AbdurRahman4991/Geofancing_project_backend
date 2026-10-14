<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HierarchyLevel extends Model
{
    protected $fillable = ['company_id', 'name', 'code', 'level_order', 'is_active'];

    protected $casts = ['level_order' => 'integer', 'is_active' => 'boolean'];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function nodes(): HasMany
    {
        return $this->hasMany(HierarchyNode::class, 'hierarchy_level_id');
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('level_order');
    }
}