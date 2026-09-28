<?php

namespace App\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class CompanyScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $user = auth()->user();

        if (!$user) {
            $builder->whereRaw('1 = 0');
            return;
        }

        // Super Admin → all companies
        if ($user->hasRole('Super-Admin')) {
            return;
        }

        $companyId = $user->employee?->company_id;

        if (!$companyId) {
            $builder->whereRaw('1 = 0');
            return;
        }

        $builder->where(
            $model->getTable() . '.company_id',
            $companyId
        );
    }
}
