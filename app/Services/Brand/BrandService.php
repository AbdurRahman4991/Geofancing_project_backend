<?php

namespace App\Services\Brand;

use App\Models\Brand;
use App\Traits\ScopesCompanyAccess;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class BrandService
{
    use ScopesCompanyAccess;

    public function index(Request $request)
    {
        $query = $this->companyQuery((int) $this->companyId($request));

        if ($request->filled('search')) {
            $search = $request->string('search')->trim()->toString();
            $query->where(function (Builder $builder) use ($search) {
                $builder->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        return $query->orderBy('name')
            ->paginate(min(max($request->integer('per_page', 10), 1), 100))
            ->withQueryString();
    }

    public function store(Request $request): Brand
    {
        $companyId = $this->companyId($request);
        $data = $this->validatedData($request, $companyId);

        return Brand::create($data + ['company_id' => $companyId]);
    }

    public function show(Request $request, int $id): Brand
    {
        return $this->companyQuery($this->companyId($request))->findOrFail($id);
    }

    public function update(Request $request, int $id): Brand
    {
        $companyId = $this->companyId($request);
        $brand = $this->companyQuery($companyId)->findOrFail($id);
        $brand->update($this->validatedData($request, $companyId, $brand));

        return $brand->refresh();
    }

    public function destroy(Request $request, int $id): void
    {
        $this->companyQuery($this->companyId($request))->findOrFail($id)->delete();
    }

    private function validatedData(Request $request, int $companyId, ?Brand $brand = null): array
    {
        return $request->validate([
            'name' => [
                $brand ? 'sometimes' : 'required',
                'string',
                'max:255',
                Rule::unique('brands', 'name')->where('company_id', $companyId)->ignore($brand?->id),
            ],
            'description' => ['sometimes', 'nullable', 'string', 'max:5000'],
            'status' => ['sometimes', 'in:active,inactive'],
        ]);
    }

    private function companyQuery(int $companyId): Builder
    {
        $query = Brand::query();
        $this->scopeToCurrentCompany($query);

        return $query->where('brands.company_id', $companyId);
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
