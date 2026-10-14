<?php

namespace App\Services\Hierarchy;

use App\Models\EmployeeHierarchyAssignment;
use App\Models\HierarchyNode;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

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
            'hierarchyNode.level',
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

        if (!empty($filters['hierarchy_node_id'])) {
            $query->where('hierarchy_node_id', $filters['hierarchy_node_id']);
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
            'hierarchy_node_id' => $assignment->hierarchy_node_id,
            'hierarchy_node' => $assignment->hierarchyNode?->name,
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
            $this->assertHierarchyNodeMatchesUser($data['user_id'], $data['hierarchy_node_id'] ?? null);
            $assignment = EmployeeHierarchyAssignment::where('user_id', $data['user_id'])
                ->where('is_current', true)
                ->first();

            $attributes = [
                'is_current' => true,
                'assigned_by' => auth()->id(),
                // A legacy assignment payload without a node clears a prior node assignment.
                'hierarchy_node_id' => $data['hierarchy_node_id'] ?? null,
            ];

            foreach ([
                'country_id',
                'region_id',
                'zone_id',
                'division_id',
                'district_id',
                'sub_district_id',
                'territory_id',
                'area_id',
                'hierarchy_node_id',
                'effective_from',
                'reason',
            ] as $field) {
                if (array_key_exists($field, $data)) {
                    $attributes[$field] = $data[$field];
                } elseif (!$assignment) {
                    $attributes[$field] = null;
                }
            }

            if ($assignment) {
                $assignment->update($attributes);
            } else {
                $attributes['user_id'] = $data['user_id'];
                $assignment = EmployeeHierarchyAssignment::create($attributes);
            }

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
                'hierarchyNode.level',
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
            'hierarchyNode.level',
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
            $this->assertHierarchyNodeMatchesUser($data['user_id'], $data['hierarchy_node_id'] ?? null);

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
                'country_id' => $data['country_id'] ?? null,
                'region_id' => $data['region_id'] ?? null,
                'zone_id' => $data['zone_id'] ?? null,
                'division_id' => $data['division_id'] ?? null,
                'district_id' => $data['district_id'] ?? null,
                'sub_district_id' => $data['sub_district_id'] ?? null,
                'territory_id' => $data['territory_id'] ?? null,
                'area_id' => $data['area_id'] ?? null,
                'hierarchy_node_id' => $data['hierarchy_node_id'] ?? null,
                'effective_from' => $data['effective_from'] ?? null,
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
                'hierarchyNode.level',
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

    private function assertHierarchyNodeMatchesUser(int $userId, mixed $nodeId): void
    {
        if ($nodeId === null || $nodeId === '') {
            return;
        }

        $user = User::with('employee')->findOrFail($userId);
        $companyId = $user->company_id ?? $user->employee?->company_id;
        if (!$companyId || !HierarchyNode::forCompany((int) $companyId)->whereKey($nodeId)->exists()) {
            throw ValidationException::withMessages([
                'hierarchy_node_id' => 'The selected hierarchy node does not belong to the assigned user company.',
            ]);
        }
    }
}
