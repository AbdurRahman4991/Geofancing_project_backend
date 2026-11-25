<?php

namespace App\Services\CountryHierarchy;

use App\Models\CountryHiearchyLevel;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class CountryHiearchyLevelService
{
    public function paginate(int $companyId, array $filters = [])
    {
        return CountryHiearchyLevel::query()
            ->where('company_id', $companyId)
            ->withCount('nodes')
            ->when(array_key_exists('is_active', $filters), fn ($query) => $query->where('is_active', filter_var($filters['is_active'], FILTER_VALIDATE_BOOLEAN)))
            ->when(!empty($filters['search']), fn ($query) => $query->where('name', 'like', '%' . $filters['search'] . '%'))
            ->ordered()
            ->paginate(min(max((int) ($filters['per_page'] ?? 15), 1), 100))
            ->withQueryString();
    }

    public function find(int $companyId, int $id): CountryHiearchyLevel
    {
        return CountryHiearchyLevel::where('company_id', $companyId)->withCount('nodes')->findOrFail($id);
    }

    public function create(int $companyId, array $data): CountryHiearchyLevel
    {
        $this->validate($companyId, $data);
        return CountryHiearchyLevel::create($data + ['company_id' => $companyId]);
    }

    public function update(int $companyId, int $id, array $data): CountryHiearchyLevel
    {
        $level = $this->find($companyId, $id);
        $this->validate($companyId, $data, $level);
        if (isset($data['level_order']) && (int) $data['level_order'] !== $level->level_order && $level->nodes()->exists()) {
            throw ValidationException::withMessages(['level_order' => 'A level with nodes cannot be moved.']);
        }
        $level->update($data);
        return $level->refresh()->loadCount('nodes');
    }

    public function delete(int $companyId, int $id): void
    {
        $level = $this->find($companyId, $id);
        if ($level->nodes()->exists()) {
            throw ValidationException::withMessages(['level' => 'Delete this level’s nodes before deleting the level.']);
        }
        $level->delete();
    }

    private function validate(int $companyId, array $data, ?CountryHiearchyLevel $level = null): void
    {
        Validator::make($data, [
            'name' => [$level ? 'sometimes' : 'required', 'string', 'max:150', Rule::unique('country_hiearchy_levels', 'name')->where('company_id', $companyId)->ignore($level?->id)],
            'code' => ['sometimes', 'nullable', 'string', 'max:100'],
            'level_order' => [$level ? 'sometimes' : 'required', 'integer', 'min:1', Rule::unique('country_hiearchy_levels', 'level_order')->where('company_id', $companyId)->ignore($level?->id)],
            'is_active' => ['sometimes', 'boolean'],
        ])->validate();
    }
}
