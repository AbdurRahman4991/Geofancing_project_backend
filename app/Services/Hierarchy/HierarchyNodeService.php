<?php

namespace App\Services\Hierarchy;

use App\Models\HierarchyLevel;
use App\Models\HierarchyNode;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class HierarchyNodeService
{
    public function paginate(int $companyId, array $filters = [])
    {
        return HierarchyNode::query()
            ->forCompany($companyId)
            ->with(['level:id,name,level_order', 'parent:id,name,hierarchy_level_id'])
            ->when(isset($filters['hierarchy_level_id']), fn ($query) => $query->where('hierarchy_level_id', $filters['hierarchy_level_id']))
            ->when(array_key_exists('parent_id', $filters), fn ($query) => $filters['parent_id'] === null || $filters['parent_id'] === ''
                ? $query->whereNull('parent_id')
                : $query->where('parent_id', $filters['parent_id']))
            ->when(isset($filters['status']), fn ($query) => $query->where('status', $filters['status']))
            ->when(!empty($filters['search']), fn ($query) => $query->where('name', 'like', '%' . $filters['search'] . '%'))
            ->orderBy('name')
            ->paginate(min(max((int) ($filters['per_page'] ?? 15), 1), 100))
            ->withQueryString();
    }

    public function find(int $companyId, int $id): HierarchyNode
    {
        return HierarchyNode::forCompany($companyId)
            ->with(['level', 'parent'])
            ->findOrFail($id);
    }

    public function create(int $companyId, array $data): HierarchyNode
    {
        $this->validate($companyId, $data);
        $this->assertLevelBelongsToCompany($companyId, (int) $data['hierarchy_level_id']);
        $this->assertParentIsValid($companyId, (int) $data['hierarchy_level_id'], $data['parent_id'] ?? null);

        $node = HierarchyNode::create($data + ['company_id' => $companyId]);

        return $this->find($companyId, $node->id);
    }

    public function update(int $companyId, int $id, array $data): HierarchyNode
    {
        $node = $this->find($companyId, $id);
        $this->validate($companyId, $data, $node);

        $levelId = (int) ($data['hierarchy_level_id'] ?? $node->hierarchy_level_id);
        $this->assertLevelBelongsToCompany($companyId, $levelId);
        $parentId = array_key_exists('parent_id', $data) ? $data['parent_id'] : $node->parent_id;
        $this->assertParentIsValid($companyId, $levelId, $parentId, $node);

        $node->update($data);

        return $this->find($companyId, $node->id);
    }

    public function delete(int $companyId, int $id): void
    {
        $this->find($companyId, $id)->delete();
    }

    private function validate(int $companyId, array $data, ?HierarchyNode $node = null): void
    {
        $levelId = $data['hierarchy_level_id'] ?? $node?->hierarchy_level_id;

        Validator::make($data, [
            'hierarchy_level_id' => [$node ? 'sometimes' : 'required', 'integer', 'exists:hierarchy_levels,id'],
            'parent_id' => ['sometimes', 'nullable', 'integer', 'exists:hierarchy_nodes,id'],
            'name' => [$node ? 'sometimes' : 'required', 'string', 'max:150'],
            'code' => ['sometimes', 'nullable', 'string', 'max:100', Rule::unique('hierarchy_nodes', 'code')
                ->where('company_id', $companyId)
                ->where('hierarchy_level_id', $levelId)
                ->ignore($node?->id)],
            'status' => ['sometimes', 'in:active,inactive'],
        ])->validate();
    }

    private function assertLevelBelongsToCompany(int $companyId, int $levelId): void
    {
        if (!HierarchyLevel::where('company_id', $companyId)->whereKey($levelId)->exists()) {
            throw ValidationException::withMessages([
                'hierarchy_level_id' => 'The selected hierarchy level does not belong to this company.',
            ]);
        }
    }

    private function assertParentIsValid(int $companyId, int $levelId, mixed $parentId, ?HierarchyNode $node = null): void
    {
        if ($parentId === null || $parentId === '') {
            return;
        }

        $parent = HierarchyNode::forCompany($companyId)->with('level:id,level_order')->find((int) $parentId);
        if (!$parent) {
            throw ValidationException::withMessages([
                'parent_id' => 'The selected parent node does not belong to this company.',
            ]);
        }

        if ($node && (int) $parent->id === (int) $node->id) {
            throw ValidationException::withMessages(['parent_id' => 'A node cannot be its own parent.']);
        }

        $childLevel = HierarchyLevel::where('company_id', $companyId)->findOrFail($levelId);
        if ($parent->level->level_order >= $childLevel->level_order) {
            throw ValidationException::withMessages([
                'parent_id' => 'The parent node must belong to a higher hierarchy level.',
            ]);
        }

        $ancestor = $parent;
        while ($ancestor) {
            if ($node && (int) $ancestor->id === (int) $node->id) {
                throw ValidationException::withMessages(['parent_id' => 'This parent would create a hierarchy cycle.']);
            }
            $ancestor = $ancestor->parent_id
                ? HierarchyNode::forCompany($companyId)->find($ancestor->parent_id)
                : null;
        }
    }
}
