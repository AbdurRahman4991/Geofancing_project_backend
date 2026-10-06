<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;

trait ScopesCompanyAccess
{
    /**
     * Limit a query to the authenticated user's company.
     * Super admins retain access to every company.
     */
    protected function scopeToCurrentCompany(Builder $query, ?string $companyRelation = null): Builder
    {
        $user = auth()->user();

        if ($user?->hasRole('Super-Admin')) {
            return $query;
        }

        $companyId = $user?->company_id ?? $user?->employee?->company_id;
        $table = $query->getModel()->getTable();

        return $query->where(function (Builder $companyQuery) use ($companyId, $table, $companyRelation) {
            if (!$companyId) {
                $companyQuery->whereRaw('1 = 0');
                return;
            }

            $companyQuery->where($table . '.company_id', $companyId);

            if ($companyRelation && method_exists($companyQuery->getModel(), $companyRelation)) {
                $companyQuery->orWhereHas($companyRelation, function (Builder $relatedQuery) use ($companyId) {
                    $relatedQuery->where('company_id', $companyId);
                });
            }
        });
    }
}
