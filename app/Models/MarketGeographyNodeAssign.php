<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

class MarketGeographyNodeAssign extends Model
{
    protected $fillable = ['company_id', 'market_node_id', 'country_node_id', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function marketNode(): BelongsTo
    {
        return $this->belongsTo(HierarchyNode::class, 'market_node_id');
    }

    public function countryNode(): BelongsTo
    {
        return $this->belongsTo(HierarchyNode::class, 'country_node_id');
    }
}
