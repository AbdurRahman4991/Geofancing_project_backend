<?php

namespace App\Services\Category;

use App\Models\Category;
use App\Traits\ScopesCompanyAccess;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class CategoryService
{
    use ScopesCompanyAccess;

    public function paginate(int $companyId, array $filters = [])
    {
        return $this->companyQuery($companyId)
            ->with('subcategories:id,company_id,category_id,name,status')
            ->when(isset($filters['status']), fn (Builder $query) => $query->where('status', $filters['status']))
            ->when(!empty($filters['search']), fn (Builder $query) => $query->where('name', 'like', '%' . $filters['search'] . '%'))
            ->orderBy('name')
            ->paginate(min(max((int) ($filters['per_page'] ?? 10), 1), 100))
            ->withQueryString();
    }

    public function find(int $companyId, int $id): Category
    {
        return $this->companyQuery($companyId)
            ->with('subcategories:id,company_id,category_id,name,status')
            ->findOrFail($id);
    }

    public function create(int $companyId, array $data): Category
    {
        $this->validate($companyId, $data);

        $category = Category::create($data + ['company_id' => $companyId]);

        return $this->find($companyId, $category->id);
    }

    public function update(int $companyId, int $id, array $data): Category
    {
        $category = $this->find($companyId, $id);
        $this->validate($companyId, $data, $category);

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
            'name' => [$category ? 'sometimes' : 'required', 'string', 'max:255', Rule::unique('categories', 'name')
                ->where('company_id', $companyId)
                ->ignore($category?->id)],
            'description' => ['sometimes', 'nullable', 'string', 'max:5000'],
            'status' => ['sometimes', 'in:active,inactive'],
        ])->validate();
    }

    private function companyQuery(int $companyId): Builder
    {
        $query = Category::query();
        $this->scopeToCurrentCompany($query);

        return $query->where('categories.company_id', $companyId);
    }
}
