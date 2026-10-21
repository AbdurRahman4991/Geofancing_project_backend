<?php

namespace App\Services\Hierarchy;

use App\Models\EmployeeHierarchyAssignment;
use App\Models\Geofence;
use App\Models\HierarchyNode;
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

    /** Get current user's assigned hierarchy level. */
    public function getAssignedLevel(?EmployeeHierarchyAssignment $assignment = null): ?string
    {
        $assignment ??= $this->currentAssignment();
        if (!$assignment?->hierarchy_node_id) {
            return null;
        }

        $node = $assignment->hierarchyNode;
        $name = $node?->level?->name;
        return $node?->level?->code ?: ($name ? strtolower(str_replace(' ', '_', $name)) : 'node');
    }

    /** Get assigned hierarchy node ID. */
    public function getAssignedId(?EmployeeHierarchyAssignment $assignment = null): ?int
    {
        $assignment ??= $this->currentAssignment();
        return $assignment?->hierarchy_node_id ? (int) $assignment->hierarchy_node_id : null;
    }

    /** Restrict a User query to the authenticated user's hierarchy branch. */
    public function applyUserHierarchyAccess(Builder $query): Builder
    {
        $user = Auth::user();
        if (!$user || $user->hasRole('Super-Admin') || $user->hasAnyRole(['Company Admin', 'admin'])) {
            return $query;
        }

        $assignment = $this->currentAssignment();
        if (!$assignment?->hierarchy_node_id) {
            return $query->whereRaw('1 = 0');
        }

        $companyId = $assignment->hierarchyNode?->company_id;
        if (!$companyId) {
            return $query->whereRaw('1 = 0');
        }

        $nodeIds = $this->hierarchyNodeBranchIds((int) $companyId, (int) $assignment->hierarchy_node_id);
        return $query->whereHas('hierarchyAssignment', fn (Builder $assignmentQuery) =>
            $assignmentQuery->whereIn('hierarchy_node_id', $nodeIds)
        );
    }

    /** Apply hierarchy access restriction to Geofence query. */
    public function applyGeofenceAccess(Builder $query, ?EmployeeHierarchyAssignment $assignment = null): Builder
    {
        $user = Auth::user();
        if ($user?->hasRole('Super-Admin')) {
            return $query;
        }

        $companyId = $user?->company_id ?? $user?->employee?->company_id;
        if (!$companyId) {
            return $query->whereRaw('1 = 0');
        }

        $query->where('geofences.company_id', $companyId);
        if ($user?->hasRole(['Company Admin', 'admin'])) {
            return $query;
        }

        $assignment ??= $this->currentAssignment();
        if (!$assignment?->hierarchy_node_id) {
            return $query->whereRaw('1 = 0');
        }

        $nodeIds = $this->hierarchyNodeBranchIds((int) $companyId, (int) $assignment->hierarchy_node_id);
        return $query->whereIn('geofences.hierarchy_node_id', $nodeIds);
    }

    /** Check whether a specific Geofence is accessible. */
    public function canAccessGeofence(Geofence $geofence, ?EmployeeHierarchyAssignment $assignment = null): bool
    {
        $query = Geofence::query()->whereKey($geofence->id);
        $this->applyGeofenceAccess($query, $assignment);
        return $query->exists();
    }

    /** Check whether current user can access a specific Area. */
    public function canAccessArea(int $areaId, ?EmployeeHierarchyAssignment $assignment = null): bool
    {
        $query = Geofence::query()->where('area_id', $areaId);
        $this->applyGeofenceAccess($query, $assignment);
        return $query->exists();
    }

    /** Get all accessible Geofences for current user. */
    public function accessibleGeofences(?EmployeeHierarchyAssignment $assignment = null): Builder
    {
        return $this->applyGeofenceAccess(Geofence::query(), $assignment);
    }

    /** Get current user's hierarchy information. */
    public function getAccessInfo(): array
    {
        $assignment = $this->currentAssignment();
        if (!$assignment) {
            return ['has_access' => false, 'level' => null, 'id' => null, 'assignment' => null];
        }

        return [
            'has_access' => true,
            'level' => $this->getAssignedLevel($assignment),
            'id' => $this->getAssignedId($assignment),
            'assignment' => $assignment,
        ];
    }

    /** @return int[] */
    private function hierarchyNodeBranchIds(int $companyId, int $nodeId): array
    {
        $nodeIds = [$nodeId];
        $frontier = $nodeIds;
        while ($frontier) {
            $children = HierarchyNode::forCompany($companyId)
                ->whereIn('parent_id', $frontier)
                ->pluck('id')->map(fn ($id) => (int) $id)->all();
            $frontier = array_values(array_diff($children, $nodeIds));
            $nodeIds = array_merge($nodeIds, $frontier);
        }
        return $nodeIds;
    }
}