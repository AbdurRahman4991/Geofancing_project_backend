<?php

namespace App\Services\Category;

use App\Models\Category;
use App\Models\SubCategory;
use App\Traits\ScopesCompanyAccess;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class SubCategoryService
{
    use ScopesCompanyAccess;

    public function index(Request $request)
    {
        $query = $this->companyQuery($this->companyId($request))->with('category:id,name');

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->input('category_id'));
        }
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->string('search')->trim()->toString() . '%');
        }

        return $query->orderBy('name')->paginate(min(max($request->integer('per_page', 10), 1), 100))->withQueryString();
    }

    public function store(Request $request): SubCategory
    {
        $companyId = $this->companyId($request);
        $data = $this->validatedData($request, $companyId);

        return SubCategory::create($data + ['company_id' => $companyId])->load('category:id,name');
    }

    public function show(Request $request, int $id): SubCategory
    {
        return $this->companyQuery($this->companyId($request))->with('category:id,name')->findOrFail($id);
    }

    public function update(Request $request, int $id): SubCategory
    {
        $companyId = $this->companyId($request);
        $item = $this->companyQuery($companyId)->findOrFail($id);
        $item->update($this->validatedData($request, $companyId, $item));

        return $item->refresh()->load('category:id,name');
    }

    public function destroy(Request $request, int $id): void
    {
        $this->companyQuery($this->companyId($request))->findOrFail($id)->delete();
    }

    private function validatedData(Request $request, int $companyId, ?SubCategory $item = null): array
    {
        $data = $request->validate([
            'category_id' => [$item ? 'sometimes' : 'required', 'integer', 'exists:categories,id'],
            'name' => [$item ? 'sometimes' : 'required', 'string', 'max:255', Rule::unique('sub_categories', 'name')->where('company_id', $companyId)->where('category_id', $request->input('category_id', $item?->category_id))->ignore($item?->id)],
            'description' => ['sometimes', 'nullable', 'string', 'max:5000'],
            'status' => ['sometimes', 'in:active,inactive'],
        ]);

        $categoryId = $data['category_id'] ?? $item?->category_id;
        if (!$categoryId || !Category::query()->where('company_id', $companyId)->whereKey($categoryId)->exists()) {
            throw ValidationException::withMessages(['category_id' => 'The selected category does not belong to this company.']);
        }

        return $data;
    }

    private function companyQuery(int $companyId): Builder
    {
        $query = SubCategory::query();
        $this->scopeToCurrentCompany($query);

        return $query->where('sub_categories.company_id', $companyId);
    }

    private function companyId(Request $request): int
    {
        $user = $request->user();
        $companyId = $user?->hasRole('Super-Admin')
            ? ($request->input('company_id') ?? $request->query('company_id'))
            : ($user?->company_id ?? $user?->employee?->company_id);

        abort_unless($companyId, 422, 'A company_id is required for this account.');

        return (int) $companyId;
    }
}
