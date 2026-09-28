<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;

trait CompanyScoped
{
    /**
     * Get logged-in user's company ID
     */
    protected function authCompanyId(): ?int
    {
        return auth()->user()?->employee?->company_id;
    }

    /**
     * Apply company-wise filtering
     */
    protected function applyCompanyScope(Builder $query): Builder
    {
        $user = auth()->user();

        // Super Admin → all companies
        if ($user?->hasRole('Super-Admin')) {
            return $query;
        }

        $companyId = $this->authCompanyId();

        // No company → no access
        if (!$companyId) {
            return $query->whereRaw('1 = 0');
        }

        return $query->where(
            $query->getModel()->getTable() . '.company_id',
            $companyId
        );
    }
}
