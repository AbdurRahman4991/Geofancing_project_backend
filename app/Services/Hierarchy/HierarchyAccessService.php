<?php

namespace App\Services\Hierarchy;

use App\Models\EmployeeHierarchyAssignment;
use App\Models\Geofence;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class HierarchyAccessService
{
    /**
     * Get current logged-in user's active hierarchy assignment.
     */
    public function currentAssignment(): ?EmployeeHierarchyAssignment
    {
        return EmployeeHierarchyAssignment::query()
            ->where('user_id', Auth::id())
            ->where('is_current', true)
            ->first();
    }

    /**
     * Get current user's assigned hierarchy level.
     *
     * Priority:
     * area
     * territory
     * sub_district
     * district
     * division
     * zone
     * region
     * country
     */
    public function getAssignedLevel(
        ?EmployeeHierarchyAssignment $assignment = null
    ): ?string {
        $assignment ??= $this->currentAssignment();

        if (!$assignment) {
            return null;
        }

        if (!empty($assignment->area_id)) {
            return 'area';
        }

        if (!empty($assignment->territory_id)) {
            return 'territory';
        }

        if (!empty($assignment->sub_district_id)) {
            return 'sub_district';
        }

        if (!empty($assignment->district_id)) {
            return 'district';
        }

        if (!empty($assignment->division_id)) {
            return 'division';
        }

        if (!empty($assignment->zone_id)) {
            return 'zone';
        }

        if (!empty($assignment->region_id)) {
            return 'region';
        }

        if (!empty($assignment->country_id)) {
            return 'country';
        }

        return null;
    }

    /**
     * Get assigned hierarchy ID.
     */
    public function getAssignedId(
        ?EmployeeHierarchyAssignment $assignment = null
    ): ?int {
        $assignment ??= $this->currentAssignment();

        if (!$assignment) {
            return null;
        }

        $level = $this->getAssignedLevel($assignment);

        if (!$level) {
            return null;
        }

        return match ($level) {
            'area'         => $assignment->area_id,
            'territory'    => $assignment->territory_id,
            'sub_district' => $assignment->sub_district_id,
            'district'     => $assignment->district_id,
            'division'     => $assignment->division_id,
            'zone'         => $assignment->zone_id,
            'region'       => $assignment->region_id,
            'country'      => $assignment->country_id,
            default        => null,
        };
    }

    /**
     * Apply hierarchy access restriction to Geofence query.
     *
     * Market/Geofence belongs to Area.
     *
     * User can access markets under his assigned hierarchy.
     */
    public function applyGeofenceAccess(
        Builder $query,
        ?EmployeeHierarchyAssignment $assignment = null
    ): Builder {
        $assignment ??= $this->currentAssignment();

        if (!$assignment) {
            // No active assignment = no access.
            return $query->whereRaw('1 = 0');
        }

        $level = $this->getAssignedLevel($assignment);

        switch ($level) {

            /**
             * Area Manager
             *
             * User can see only his Area's markets.
             */
            case 'area':

                $query->where(
                    'area_id',
                    $assignment->area_id
                );

                break;


            /**
             * Territory Manager
             *
             * User can see all Areas
             * under his Territory.
             */
            case 'territory':

                $query->whereHas('area', function ($q) use ($assignment) {

                    $q->where(
                        'territory_id',
                        $assignment->territory_id
                    );

                });

                break;


            /**
             * Sub District Manager
             *
             * Area
             * → Territory
             * → SubDistrict
             */
            case 'sub_district':

                $query->whereHas(
                    'area.territory',
                    function ($q) use ($assignment) {

                        $q->where(
                            'sub_district_id',
                            $assignment->sub_district_id
                        );

                    }
                );

                break;


            /**
             * District Manager
             */
            case 'district':

                $query->whereHas(
                    'area.territory.subDistrict',
                    function ($q) use ($assignment) {

                        $q->where(
                            'district_id',
                            $assignment->district_id
                        );

                    }
                );

                break;


            /**
             * Division Manager
             */
            case 'division':

                $query->whereHas(
                    'area.territory.subDistrict.district',
                    function ($q) use ($assignment) {

                        $q->where(
                            'division_id',
                            $assignment->division_id
                        );

                    }
                );

                break;


            /**
             * Zone Manager
             */
            case 'zone':

                $query->whereHas(
                    'area.territory.subDistrict.district.division',
                    function ($q) use ($assignment) {

                        $q->where(
                            'zone_id',
                            $assignment->zone_id
                        );

                    }
                );

                break;


            /**
             * Region Manager
             */
            case 'region':

                $query->whereHas(
                    'area.territory.subDistrict.district.division.zone',
                    function ($q) use ($assignment) {

                        $q->where(
                            'region_id',
                            $assignment->region_id
                        );

                    }
                );

                break;


            /**
             * Country Manager
             */
            case 'country':

                $query->whereHas(
                    'area.territory.subDistrict.district.division.zone.region',
                    function ($q) use ($assignment) {

                        $q->where(
                            'country_id',
                            $assignment->country_id
                        );

                    }
                );

                break;
        }

        return $query;
    }

    /**
     * Check whether a specific Geofence is accessible
     * by current logged-in user.
     */
    public function canAccessGeofence(
        Geofence $geofence,
        ?EmployeeHierarchyAssignment $assignment = null
    ): bool {
        $assignment ??= $this->currentAssignment();

        if (!$assignment) {
            return false;
        }

        $query = Geofence::query()
            ->whereKey($geofence->id);

        $this->applyGeofenceAccess(
            $query,
            $assignment
        );

        return $query->exists();
    }

    /**
     * Check whether current user can access a specific Area.
     */
    public function canAccessArea(
        int $areaId,
        ?EmployeeHierarchyAssignment $assignment = null
    ): bool {
        $assignment ??= $this->currentAssignment();

        if (!$assignment) {
            return false;
        }

        $query = Geofence::query()
            ->where('area_id', $areaId);

        $this->applyGeofenceAccess(
            $query,
            $assignment
        );

        return $query->exists();
    }

    /**
     * Get all accessible Geofences for current user.
     */
    public function accessibleGeofences(
        ?EmployeeHierarchyAssignment $assignment = null
    ): Builder {
        $assignment ??= $this->currentAssignment();

        $query = Geofence::query();

        return $this->applyGeofenceAccess(
            $query,
            $assignment
        );
    }

    /**
     * Get current user's hierarchy information.
     */
    public function getAccessInfo(): array
    {
        $assignment = $this->currentAssignment();

        if (!$assignment) {
            return [
                'has_access' => false,
                'level' => null,
                'id' => null,
                'assignment' => null,
            ];
        }

        return [
            'has_access' => true,
            'level' => $this->getAssignedLevel($assignment),
            'id' => $this->getAssignedId($assignment),
            'assignment' => $assignment,
        ];
    }
}