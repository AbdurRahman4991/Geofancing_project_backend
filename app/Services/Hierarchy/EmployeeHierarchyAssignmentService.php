<?php

namespace App\Services\Hierarchy;

use App\Models\EmployeeHierarchyAssignment;
use Illuminate\Support\Facades\DB;

class EmployeeHierarchyAssignmentService
{
    /**
     * Get all employee hierarchy assignments
     */
    public function getAll(array $filters = [])
{
    $query = EmployeeHierarchyAssignment::with([
        'user',
        'country',
        'region',
        'zone',
        'division',
        'district',
        'subDistrict',
        'territory',
        'area',
        'assignedBy',
    ]);

    // User filter
    if (!empty($filters['user_id'])) {
        $query->where('user_id', $filters['user_id']);
    }

    // Country filter
    if (!empty($filters['country_id'])) {
        $query->where('country_id', $filters['country_id']);
    }

    // Region filter
    if (!empty($filters['region_id'])) {
        $query->where('region_id', $filters['region_id']);
    }

    // Zone filter
    if (!empty($filters['zone_id'])) {
        $query->where('zone_id', $filters['zone_id']);
    }

    // Division filter
    if (!empty($filters['division_id'])) {
        $query->where('division_id', $filters['division_id']);
    }

    // District filter
    if (!empty($filters['district_id'])) {
        $query->where('district_id', $filters['district_id']);
    }

    // Sub District filter
    if (!empty($filters['sub_district_id'])) {
        $query->where(
            'sub_district_id',
            $filters['sub_district_id']
        );
    }

    // Territory filter
    if (!empty($filters['territory_id'])) {
        $query->where(
            'territory_id',
            $filters['territory_id']
        );
    }

    // Area filter
    if (!empty($filters['area_id'])) {
        $query->where(
            'area_id',
            $filters['area_id']
        );
    }

    // Current assignment filter
    if (isset($filters['is_current']) && $filters['is_current'] !== '') {
        $query->where(
            'is_current',
            filter_var(
                $filters['is_current'],
                FILTER_VALIDATE_BOOLEAN
            )
        );
    }

    // Search
    if (!empty($filters['search'])) {
        $search = $filters['search'];

        $query->where(function ($q) use ($search) {

            $q->where('reason', 'like', "%{$search}%")

                ->orWhereHas('user', function ($userQuery) use ($search) {
                    $userQuery
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                })

                ->orWhereHas('country', function ($countryQuery) use ($search) {
                    $countryQuery->where(
                        'name',
                        'like',
                        "%{$search}%"
                    );
                })

                ->orWhereHas('region', function ($regionQuery) use ($search) {
                    $regionQuery->where(
                        'name',
                        'like',
                        "%{$search}%"
                    );
                })

                ->orWhereHas('zone', function ($zoneQuery) use ($search) {
                    $zoneQuery->where(
                        'name',
                        'like',
                        "%{$search}%"
                    );
                })

                ->orWhereHas('division', function ($divisionQuery) use ($search) {
                    $divisionQuery->where(
                        'name',
                        'like',
                        "%{$search}%"
                    );
                })

                ->orWhereHas('district', function ($districtQuery) use ($search) {
                    $districtQuery->where(
                        'name',
                        'like',
                        "%{$search}%"
                    );
                })

                ->orWhereHas('subDistrict', function ($subDistrictQuery) use ($search) {
                    $subDistrictQuery->where(
                        'name',
                        'like',
                        "%{$search}%"
                    );
                })

                ->orWhereHas('territory', function ($territoryQuery) use ($search) {
                    $territoryQuery->where(
                        'name',
                        'like',
                        "%{$search}%"
                    );
                })

                ->orWhereHas('area', function ($areaQuery) use ($search) {
                    $areaQuery->where(
                        'name',
                        'like',
                        "%{$search}%"
                    );
                });
        });
    }

    $perPage = $filters['per_page'] ?? 10;

    $assignments = $query
        ->latest()
        ->paginate($perPage)
        ->withQueryString();

    // Transform each row
    $assignments = $query
    ->latest()
    ->paginate($perPage)
    ->withQueryString();

$assignments->getCollection()->transform(function ($assignment) {

    return [
        'id' => $assignment->id,
        'user' => $assignment->user?->name,
        'country' => $assignment->country?->name,
        'region' => $assignment->region?->name,
        'zone' => $assignment->zone?->name,
        'division' => $assignment->division?->name,
        'district' => $assignment->district?->name,
        'sub_district' => $assignment->subDistrict?->name,
        'territory' => $assignment->territory?->name,
        'area' => $assignment->area?->name,
        'effective_from' => $assignment->effective_from,
        'effective_to' => $assignment->effective_to,
        'is_current' => $assignment->is_current,
        'assigned_by' => $assignment->assignedBy?->name,
        'reason' => $assignment->reason,
    ];
});

return $assignments;
}

    /**
     * Create new assignment
     */
    public function create(array $data): EmployeeHierarchyAssignment
    {
        return DB::transaction(function () use ($data) {

            /*
             * Previous current assignment inactive করে দিচ্ছি
             */
            EmployeeHierarchyAssignment::where(
                'user_id',
                $data['user_id']
            )->update([
                'is_current' => false,
            ]);

            /*
             * New assignment create
             */
            $assignment = EmployeeHierarchyAssignment::create([
                'user_id' => $data['user_id'],
                'country_id' => $data['country_id'],
                'region_id' => $data['region_id'],
                'zone_id' => $data['zone_id'],
                'division_id' => $data['division_id'],
                'district_id' => $data['district_id'],
                'sub_district_id' => $data['sub_district_id'],
                'territory_id' => $data['territory_id'],
                'area_id' => $data['area_id'],
                'effective_from' => $data['effective_from'],
                'reason' => $data['reason'] ?? null,
                'is_current' => true,
                'assigned_by' => auth()->id(),
            ]);

            return $assignment->load([
                'user',
                'country',
                'region',
                'zone',
                'division',
                'district',
                'subDistrict',
                'territory',
                'area',
                'assignedBy',
            ]);
        });
    }

    /**
     * Get single assignment
     */
    public function getById(int $id): ?EmployeeHierarchyAssignment
    {
        return EmployeeHierarchyAssignment::with([
            'user',
            'country',
            'region',
            'zone',
            'division',
            'district',
            'subDistrict',
            'territory',
            'area',
            'assignedBy',
        ])->find($id);
    }

    /**
     * Update assignment
     */
    public function update(
        int $id,
        array $data
    ): ?EmployeeHierarchyAssignment {

        $assignment = EmployeeHierarchyAssignment::find($id);

        if (!$assignment) {
            return null;
        }

        return DB::transaction(function () use (
            $assignment,
            $data
        ) {

            /*
             * Same user-এর অন্য assignment inactive
             */
            EmployeeHierarchyAssignment::where(
                'user_id',
                $data['user_id']
            )
                ->where('id', '!=', $assignment->id)
                ->update([
                    'is_current' => false,
                ]);

            /*
             * Current assignment update
             */
            $assignment->update([
                'user_id' => $data['user_id'],
                'country_id' => $data['country_id'],
                'region_id' => $data['region_id'],
                'zone_id' => $data['zone_id'],
                'division_id' => $data['division_id'],
                'district_id' => $data['district_id'],
                'sub_district_id' => $data['sub_district_id'],
                'territory_id' => $data['territory_id'],
                'area_id' => $data['area_id'],
                'effective_from' => $data['effective_from'],
                'reason' => $data['reason'] ?? null,
                'is_current' => true,
                'assigned_by' => auth()->id(),
            ]);

            return $assignment->load([
                'user',
                'country',
                'region',
                'zone',
                'division',
                'district',
                'subDistrict',
                'territory',
                'area',
                'assignedBy',
            ]);
        });
    }

    /**
     * Delete assignment
     */
    public function delete(int $id): bool
    {
        $assignment = EmployeeHierarchyAssignment::find($id);

        if (!$assignment) {
            return false;
        }

        return (bool) $assignment->delete();
    }
}