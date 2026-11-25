<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CountryHiearchyNode extends Model
{
    protected $fillable = [
        'company_id', 'hierarchy_level_id', 'parent_id', 'name', 'code', 'status', 'latitude', 'longitude',
    ];

    protected $casts = ['latitude' => 'decimal:7', 'longitude' => 'decimal:7'];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function level(): BelongsTo
    {
        return $this->belongsTo(CountryHiearchyLevel::class, 'hierarchy_level_id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }
}
