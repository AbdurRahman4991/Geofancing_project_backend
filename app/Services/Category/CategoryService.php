<?php

namespace App\Services\Category;

use App\Models\Category;
use App\Traits\ScopesCompanyAccess;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class CategoryService
{
    use ScopesCompanyAccess;

    public function paginate(int $companyId, array $filters = [])
    {
        return $this->companyQuery($companyId)
            ->with(['parent:id,name', 'subcategories:id,company_id,parent_id,name,status'])
            ->when(array_key_exists('parent_id', $filters), function (Builder $query) use ($filters) {
                $filters['parent_id'] === null || $filters['parent_id'] === ''
                    ? $query->whereNull('parent_id')
                    : $query->where('parent_id', $filters['parent_id']);
            })
            ->when(isset($filters['status']), fn (Builder $query) => $query->where('status', $filters['status']))
            ->when(!empty($filters['search']), fn (Builder $query) => $query->where('name', 'like', '%' . $filters['search'] . '%'))
            ->orderBy('name')
            ->paginate(min(max((int) ($filters['per_page'] ?? 10), 1), 100))
            ->withQueryString();
    }

    public function find(int $companyId, int $id): Category
    {
        return $this->companyQuery($companyId)
            ->with(['parent:id,name', 'subcategories:id,company_id,parent_id,name,status'])
            ->findOrFail($id);
    }

    public function create(int $companyId, array $data): Category
    {
        $this->validate($companyId, $data);
        $this->assertParentIsCategory($companyId, $data['parent_id'] ?? null);

        $category = Category::create($data + ['company_id' => $companyId]);

        return $this->find($companyId, $category->id);
    }

    public function update(int $companyId, int $id, array $data): Category
    {
        $category = $this->find($companyId, $id);
        $this->validate($companyId, $data, $category);
        $parentId = array_key_exists('parent_id', $data) ? $data['parent_id'] : $category->parent_id;
        $this->assertParentIsCategory($companyId, $parentId, $category);

        $category->update($data);

        return $this->find($companyId, $category->id);
    }

    public function delete(int $companyId, int $id): void
    {
        $this->find($companyId, $id)->delete();
    }

    private function validate(int $companyId, array $data, ?Category $category = null): void
    {
        Validator::make($data, [
            'parent_id' => ['sometimes', 'nullable', 'integer', 'exists:categories,id'],
            'name' => [$category ? 'sometimes' : 'required', 'string', 'max:255', Rule::unique('categories', 'name')
                ->where('company_id', $companyId)
                ->where('parent_id', array_key_exists('parent_id', $data) ? $data['parent_id'] : $category?->parent_id)
                ->ignore($category?->id)],
            'description' => ['sometimes', 'nullable', 'string', 'max:5000'],
            'status' => ['sometimes', 'in:active,inactive'],
        ])->validate();
    }

    private function assertParentIsCategory(int $companyId, mixed $parentId, ?Category $category = null): void
    {
        if ($parentId === null || $parentId === '') {
            return;
        }

        $parent = $this->companyQuery($companyId)->find((int) $parentId);
        if (!$parent) {
            throw ValidationException::withMessages([
                'parent_id' => 'The selected parent category does not belong to this company.',
            ]);
        }

        if ($parent->parent_id !== null) {
            throw ValidationException::withMessages([
                'parent_id' => 'A subcategory cannot be used as a parent category.',
            ]);
        }

        if ($category && (int) $parent->id === (int) $category->id) {
            throw ValidationException::withMessages(['parent_id' => 'A category cannot be its own parent.']);
        }

        if ($category && $category->parent_id === null && $category->subcategories()->exists()) {
            throw ValidationException::withMessages([
                'parent_id' => 'A category with subcategories cannot become a subcategory.',
            ]);
        }
    }

    private function companyQuery(int $companyId): Builder
    {
        $query = Category::query();
        $this->scopeToCurrentCompany($query);

        return $query->where('categories.company_id', $companyId);
    }
}
