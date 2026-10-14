<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;

trait ScopesCompanyDirectoryAccess
{
    /**
     * Super Admins can access every company. Other users are limited to
     * the company linked to their account or employee record.
     */
    protected function scopeToAccessibleCompanies(Builder $query): Builder
    {
        $user = auth()->user();

        if ($user?->hasRole('Super-Admin')) {
            return $query;
        }

        $companyId = $user?->company_id ?? $user?->employee?->company_id;

        if (!$companyId) {
            return $query->whereRaw('1 = 0');
        }

        return $query->where($query->getModel()->qualifyColumn('id'), $companyId);
    }
}
