<?php

namespace App\Services\Hierarchy;

use App\Models\User;
use App\Models\Area;

class HierarchyAccessService
{
    public function getAreaIds(User $user)
    {
        if ($user->hasRole('super-admin')) {
            return Area::pluck('id');
        }

        $assignment = $user->employee
            ?->currentHierarchy;

        if (!$assignment) {
            return collect();
        }

        if ($user->hasRole('area-manager')) {
            return collect([
                $assignment->area_id
            ])->filter();
        }

        if ($user->hasRole('territory-manager')) {
            return Area::where(
                'territory_id',
                $assignment->territory_id
            )->pluck('id');
        }

        if ($user->hasRole('sub-district-manager')) {
            return Area::whereHas(
                'territory.subDistrict',
                function ($query) use ($assignment) {
                    $query->where(
                        'id',
                        $assignment->sub_district_id
                    );
                }
            )->pluck('id');
        }

        if ($user->hasRole('district-manager')) {
            return Area::whereHas(
                'territory.subDistrict.district',
                function ($query) use ($assignment) {
                    $query->where(
                        'id',
                        $assignment->district_id
                    );
                }
            )->pluck('id');
        }

        return collect();
    }
}