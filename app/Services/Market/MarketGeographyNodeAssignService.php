<?php

namespace App\Services\Market;

use App\Models\HierarchyNode;
use App\Models\MarketGeographyNodeAssign;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class MarketGeographyNodeAssignService
{
    public function paginate(int $companyId, array $filters = [])
    {
        return MarketGeographyNodeAssign::query()
            ->where('company_id', $companyId)
            ->with(['marketNode:id,name,code,hierarchy_level_id', 'countryNode:id,name,code,hierarchy_level_id'])
            ->when(isset($filters['market_node_id']), fn ($query) => $query->where('market_node_id', $filters['market_node_id']))
            ->when(isset($filters['country_node_id']), fn ($query) => $query->where('country_node_id', $filters['country_node_id']))
            ->when(array_key_exists('is_active', $filters) && $filters['is_active'] !== '', fn ($query) => $query->where('is_active', filter_var($filters['is_active'], FILTER_VALIDATE_BOOLEAN)))
            ->orderBy('id', 'desc')
            ->paginate(min(max((int) ($filters['per_page'] ?? 15), 1), 100))
            ->withQueryString();
    }

    public function find(int $companyId, int $id): MarketGeographyNodeAssign
    {
        return MarketGeographyNodeAssign::where('company_id', $companyId)
            ->with(['marketNode:id,name,code,hierarchy_level_id', 'countryNode:id,name,code,hierarchy_level_id'])
            ->findOrFail($id);
    }

    public function create(int $companyId, array $data): MarketGeographyNodeAssign
    {
        $this->validate($companyId, $data);
        $this->assertNodesBelongToCompany($companyId, $data);

        $assignment = MarketGeographyNodeAssign::create($data + ['company_id' => $companyId]);

        return $this->find($companyId, $assignment->id);
    }

    public function update(int $companyId, int $id, array $data): MarketGeographyNodeAssign
    {
        $assignment = $this->find($companyId, $id);
        $this->validate($companyId, $data, $assignment);

        $nodeData = [
            'market_node_id' => $data['market_node_id'] ?? $assignment->market_node_id,
            'country_node_id' => $data['country_node_id'] ?? $assignment->country_node_id,
        ];
        $this->assertNodesBelongToCompany($companyId, $nodeData);

        $assignment->update($data);

        return $this->find($companyId, $assignment->id);
    }

    public function delete(int $companyId, int $id): void
    {
        $this->find($companyId, $id)->delete();
    }

    private function validate(int $companyId, array $data, ?MarketGeographyNodeAssign $assignment = null): void
    {
        Validator::make($data, [
            'market_node_id' => [$assignment ? 'sometimes' : 'required', 'integer', 'different:country_node_id', 'exists:hierarchy_nodes,id', Rule::unique('market_geography_node_assigns', 'market_node_id')->where('company_id', $companyId)->ignore($assignment?->id)],
            'country_node_id' => [$assignment ? 'sometimes' : 'required', 'integer', 'exists:hierarchy_nodes,id'],
            'is_active' => ['sometimes', 'boolean'],
        ])->validate();
    }

    private function assertNodesBelongToCompany(int $companyId, array $data): void
    {
        foreach (['market_node_id', 'country_node_id'] as $field) {
            if (!HierarchyNode::forCompany($companyId)->whereKey($data[$field])->exists()) {
                throw ValidationException::withMessages([
                    $field => 'The selected node does not belong to this company.',
                ]);
            }
        }
        if ((int) $data['market_node_id'] === (int) $data['country_node_id']) {
            throw ValidationException::withMessages([
                'country_node_id' => 'The market and country nodes must be different.',
            ]);
        }
    }
}
