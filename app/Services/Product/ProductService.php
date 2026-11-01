<?php

namespace App\Services\Product;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\Unit;
use App\Traits\ScopesCompanyAccess;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ProductService
{
    use ScopesCompanyAccess;

    public function index(Request $request)
    {
        $query = $this->companyQuery($this->companyId($request))
            ->with(['category:id,name', 'subCategory:id,category_id,name', 'brand:id,name', 'unit:id,unit_name,short_name']);

        if ($request->filled('search')) {
            $search = $request->string('search')->trim()->toString();
            $query->where('product_name', 'like', "%{$search}%");
        }

        foreach (['category_id', 'sub_category_id', 'brand_id', 'unit_id', 'status'] as $filter) {
            if ($request->filled($filter)) {
                $query->where($filter, $request->input($filter));
            }
        }

        return $query->orderBy('product_name')
            ->paginate(min(max($request->integer('per_page', 10), 1), 100))
            ->withQueryString();
    }

    public function store(Request $request): Product
    {
        $companyId = $this->companyId($request);
        $data = $this->validatedData($request);
        $this->assertReferencesBelongToCompany($companyId, $data);

        return Product::create($data + ['company_id' => $companyId])
            ->load(['category:id,name', 'subCategory:id,category_id,name', 'brand:id,name', 'unit:id,unit_name,short_name']);
    }

    public function show(Request $request, int $id): Product
    {
        return $this->companyQuery($this->companyId($request))
            ->with(['category:id,name', 'subCategory:id,category_id,name', 'brand:id,name', 'unit:id,unit_name,short_name'])
            ->findOrFail($id);
    }

    public function update(Request $request, int $id): Product
    {
        $companyId = $this->companyId($request);
        $product = $this->companyQuery($companyId)->findOrFail($id);
        $data = $this->validatedData($request, true);
        $this->assertReferencesBelongToCompany($companyId, [
            'category_id' => $data['category_id'] ?? $product->category_id,
            'sub_category_id' => $data['sub_category_id'] ?? $product->sub_category_id,
            'brand_id' => $data['brand_id'] ?? $product->brand_id,
            'unit_id' => $data['unit_id'] ?? $product->unit_id,
        ]);
        $product->update($data);

        return $product->refresh()->load(['category:id,name', 'subCategory:id,category_id,name', 'brand:id,name', 'unit:id,unit_name,short_name']);
    }

    public function destroy(Request $request, int $id): void
    {
        $this->companyQuery($this->companyId($request))->findOrFail($id)->delete();
    }

    private function validatedData(Request $request, bool $updating = false): array
    {
        return $request->validate([
            'product_name' => [$updating ? 'sometimes' : 'required', 'string', 'max:255'],
            'category_id' => [$updating ? 'sometimes' : 'required', 'integer', 'exists:categories,id'],
            'sub_category_id' => ['sometimes', 'nullable', 'integer', 'exists:sub_categories,id'],
            'brand_id' => [$updating ? 'sometimes' : 'required', 'integer', 'exists:brands,id'],
            'unit_id' => [$updating ? 'sometimes' : 'required', 'integer', 'exists:units,id'],
            'description' => ['sometimes', 'nullable', 'string', 'max:5000'],
            'status' => ['sometimes', 'in:active,inactive'],
        ]);
    }

    private function assertReferencesBelongToCompany(int $companyId, array $data): void
    {
        $references = [
            'category_id' => [Category::class, 'Category'],
            'brand_id' => [Brand::class, 'Brand'],
            'unit_id' => [Unit::class, 'Unit'],
        ];

        foreach ($references as $field => [$model, $label]) {
            if (!$model::query()->where('company_id', $companyId)->whereKey($data[$field])->exists()) {
                throw ValidationException::withMessages([
                    $field => "The selected {$label} does not belong to this company.",
                ]);
            }
        }

        if (!empty($data['sub_category_id'])) {
            $subCategory = \App\Models\SubCategory::query()->where('company_id', $companyId)->whereKey($data['sub_category_id'])->first();
            if (!$subCategory || (int) $subCategory->category_id !== (int) $data['category_id']) {
                throw ValidationException::withMessages(['sub_category_id' => 'The selected subcategory must belong to the selected category and company.']);
            }
        }
    }

    private function companyQuery(int $companyId): Builder
    {
        $query = Product::query();
        $this->scopeToCurrentCompany($query);

        return $query->where('products.company_id', $companyId);
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
