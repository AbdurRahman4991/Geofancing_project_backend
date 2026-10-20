<?php

namespace App\Services\Hierarchy;

use App\Models\EmployeeHierarchyAssignment;
use App\Models\HierarchyNode;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class EmployeeHierarchyAssignmentService
{
    /**
     * Get all employee hierarchy assignments
     */
    public function getAll(array $filters = [])
    {
        $query = $this->scopeForCurrentUser(EmployeeHierarchyAssignment::query())->with([
            'user',
            'company',
            'hierarchyNode.level',
            'assignedBy',
        ]);

        // User filter
        if (!empty($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        // Super Admin may narrow results to one company; other users are scoped below.
        if (!empty($filters['company_id']) && auth()->user()?->hasRole('Super-Admin')) {
            $query->where('company_id', $filters['company_id']);
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

                $q->whereHas('user', function ($userQuery) use ($search) {
                        $userQuery
                            ->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    })
                    ->orWhereHas('hierarchyNode', function ($nodeQuery) use ($search) {
                        $nodeQuery->where('name', 'like', "%{$search}%")
                            ->orWhere('code', 'like', "%{$search}%")
                            ->orWhereHas('level', function ($levelQuery) use ($search) {
                                $levelQuery->where('name', 'like', "%{$search}%")
                                    ->orWhere('code', 'like', "%{$search}%");
                            });
                    });
            });
        }

        $perPage = $filters['per_page'] ?? 10;

        $assignments = $query
            ->latest()
            ->paginate($perPage)
            ->withQueryString();

        $assignments->getCollection()->transform(function ($assignment) {
            return [
                'id' => $assignment->id,
                'user' => $assignment->user?->name,
                'company_id' => $assignment->company_id,
                'company' => $assignment->company?->name,
                'hierarchy_node_id' => $assignment->hierarchy_node_id,
                'hierarchy_node' => $assignment->hierarchyNode?->name,
                'hierarchy_level' => $assignment->hierarchyNode?->level?->name,
                'hierarchy_level_code' => $assignment->hierarchyNode?->level?->code,
                'is_current' => $assignment->is_current,
                'assigned_by' => $assignment->assignedBy?->name,
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
            $user = User::with('employee')->findOrFail($data['user_id']);
            $companyId = (int) ($user->company_id ?? $user->employee?->company_id);
            if (!$companyId) {
                throw ValidationException::withMessages(['user_id' => 'The selected user is not associated with a company.']);
            }
            $this->assertCompanyAccess($companyId);
            $this->assertHierarchyNodeMatchesUser($data['user_id'], $data['hierarchy_node_id'] ?? null);
            $assignment = EmployeeHierarchyAssignment::where('user_id', $data['user_id'])
                ->where('company_id', $companyId)
                ->where('is_current', true)
                ->first();

            $attributes = [
                'company_id' => $companyId,
                'is_current' => true,
                'assigned_by' => auth()->id(),
                'hierarchy_node_id' => $data['hierarchy_node_id'] ?? null,
            ];

            if ($assignment) {
                $assignment->update($attributes);
            } else {
                $attributes['user_id'] = $data['user_id'];
                $assignment = EmployeeHierarchyAssignment::create($attributes);
            }

            return $assignment->load([
                'user',
                'company',                
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
        return $this->scopeForCurrentUser(EmployeeHierarchyAssignment::query())->with([
            'user',
            'company',            
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

        $assignment = $this->scopeForCurrentUser(EmployeeHierarchyAssignment::query())->find($id);

        if (!$assignment) {
            return null;
        }

        return DB::transaction(function () use (
            $assignment,
            $data
        ) {
            $user = User::with('employee')->findOrFail($data['user_id']);
            $companyId = (int) ($user->company_id ?? $user->employee?->company_id);
            if (!$companyId) {
                throw ValidationException::withMessages(['user_id' => 'The selected user is not associated with a company.']);
            }
            $this->assertCompanyAccess($companyId);
            $this->assertHierarchyNodeMatchesUser($data['user_id'], $data['hierarchy_node_id'] ?? null);

            /*
             * Same user-এর অন্য assignment inactive
             */
            $this->scopeForCurrentUser(EmployeeHierarchyAssignment::query())->where(
                'user_id',
                $data['user_id']
            )
                ->where('company_id', $companyId)
                ->where('id', '!=', $assignment->id)
                ->update([
                    'is_current' => false,
                ]);

            /*
             * Current assignment update
             */
            $assignment->update([
                'user_id' => $data['user_id'],
                'company_id' => $companyId,
                'hierarchy_node_id' => $data['hierarchy_node_id'] ?? null,                
                'is_current' => true,
                'assigned_by' => auth()->id(),
            ]);

            return $assignment->load([
                'user',
                'company',                
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
        $assignment = $this->scopeForCurrentUser(EmployeeHierarchyAssignment::query())->find($id);

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

        $actor = auth()->user();
        if (!$actor || $actor->hasRole('Super-Admin') || $actor->hasAnyRole(['Company Admin', 'admin'])) {
            return;
        }

        $actorCompanyId = $actor->company_id ?? $actor->employee?->company_id;
        $actorAssignment = EmployeeHierarchyAssignment::query()
            ->where('user_id', $actor->id)
            ->where('company_id', $actorCompanyId)
            ->where('is_current', true)
            ->first();
        if (!$actorAssignment?->hierarchy_node_id || !in_array(
            (int) $nodeId,
            $this->hierarchyNodeBranchIds((int) $actorCompanyId, (int) $actorAssignment->hierarchy_node_id),
            true
        )) {
            throw ValidationException::withMessages([
                'hierarchy_node_id' => 'The selected hierarchy node is outside your hierarchy access.',
            ]);
        }
    }

    private function scopeForCurrentUser(Builder $query): Builder
    {
        $user = auth()->user();
        if (!$user) {
            return $query->whereRaw('1 = 0');
        }
        if ($user->hasRole('Super-Admin')) {
            return $query;
        }

        $companyId = $user->company_id ?? $user->employee?->company_id;
        if (!$companyId) {
            return $query->whereRaw('1 = 0');
        }
        $query->where('company_id', $companyId);
        if ($user->hasAnyRole(['Company Admin', 'admin'])) {
            return $query;
        }

        $current = EmployeeHierarchyAssignment::query()
            ->where('user_id', $user->id)
            ->where('company_id', $companyId)
            ->where('is_current', true)
            ->first();
        if (!$current?->hierarchy_node_id) {
            return $query->whereRaw('1 = 0');
        }

        return $query->whereIn(
            'hierarchy_node_id',
            $this->hierarchyNodeBranchIds((int) $companyId, (int) $current->hierarchy_node_id)
        );
    }

    /** @return int[] */
    private function hierarchyNodeBranchIds(int $companyId, int $nodeId): array
    {
        $nodeIds = [$nodeId];
        $frontier = $nodeIds;
        while ($frontier !== []) {
            $children = HierarchyNode::forCompany((int) $companyId)
                ->whereIn('parent_id', $frontier)
                ->pluck('id')->map(fn ($id) => (int) $id)->all();
            $frontier = array_values(array_diff($children, $nodeIds));
            $nodeIds = array_merge($nodeIds, $frontier);
        }
        return $nodeIds;
    }

    private function assertCompanyAccess(int $targetCompanyId): void
    {
        $user = auth()->user();
        if ($user?->hasRole('Super-Admin')) {
            return;
        }
        $companyId = $user?->company_id ?? $user?->employee?->company_id;
        if (!$companyId || (int) $companyId !== $targetCompanyId) {
            throw ValidationException::withMessages(['user_id' => 'The selected user is outside your company access.']);
        }
    }
}
