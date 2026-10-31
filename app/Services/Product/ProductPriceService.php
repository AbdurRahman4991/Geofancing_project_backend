<?php

namespace App\Services\Product;

use App\Models\Product;
use App\Models\ProductPrice;
use App\Traits\ScopesCompanyAccess;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;

class ProductPriceService
{
    use ScopesCompanyAccess;

    public function index(Request $request)
    {
        $query = $this->companyQuery($this->companyId($request))
            ->with('product:id,company_id,product_name');

        if ($request->filled('product_id')) {
            $query->where('product_id', $request->input('product_id'));
        }

        if ($request->filled('search')) {
            $search = $request->string('search')->trim()->toString();
            $query->whereHas('product', fn (Builder $productQuery) =>
                $productQuery->where('product_name', 'like', "%{$search}%"));
        }

        return $query->orderByDesc('effective_from')
            ->orderByDesc('id')
            ->paginate(min(max($request->integer('per_page', 10), 1), 100))
            ->withQueryString();
    }

    public function store(Request $request): ProductPrice
    {
        $companyId = $this->companyId($request);
        $data = $this->validatedData($request);
        $this->assertProductBelongsToCompany($companyId, (int) $data['product_id']);
        $this->assertEffectiveDateRange($data['effective_from'], $data['effective_to'] ?? null);

        return ProductPrice::create($data + ['company_id' => $companyId])
            ->load('product:id,company_id,product_name');
    }

    public function show(Request $request, int $id): ProductPrice
    {
        return $this->companyQuery($this->companyId($request))
            ->with('product:id,company_id,product_name')
            ->findOrFail($id);
    }

    public function update(Request $request, int $id): ProductPrice
    {
        $companyId = $this->companyId($request);
        $price = $this->companyQuery($companyId)->findOrFail($id);
        $data = $this->validatedData($request, true);
        $productId = (int) ($data['product_id'] ?? $price->product_id);
        $this->assertProductBelongsToCompany($companyId, $productId);
        $this->assertEffectiveDateRange(
            $data['effective_from'] ?? $price->effective_from->toDateString(),
            array_key_exists('effective_to', $data)
                ? $data['effective_to']
                : $price->effective_to?->toDateString(),
        );
        $price->update($data);

        return $price->refresh()->load('product:id,company_id,product_name');
    }

    public function destroy(Request $request, int $id): void
    {
        $this->companyQuery($this->companyId($request))->findOrFail($id)->delete();
    }

    private function validatedData(Request $request, bool $updating = false): array
    {
        return $request->validate([
            'product_id' => [$updating ? 'sometimes' : 'required', 'integer', 'exists:products,id'],
            'purchase_price' => [$updating ? 'sometimes' : 'required', 'numeric', 'min:0', 'max:999999999999.99'],
            'sale_price' => [$updating ? 'sometimes' : 'required', 'numeric', 'min:0', 'max:999999999999.99'],
            'mrp' => [$updating ? 'sometimes' : 'required', 'numeric', 'min:0', 'max:999999999999.99'],
            'discount' => ['sometimes', 'numeric', 'min:0', 'max:100'],
            'tax' => ['sometimes', 'numeric', 'min:0', 'max:100'],
            'effective_from' => [$updating ? 'sometimes' : 'required', 'date'],
            'effective_to' => ['sometimes', 'nullable', 'date'],
        ]);
    }

    private function assertProductBelongsToCompany(int $companyId, int $productId): void
    {
        if (!Product::query()->where('company_id', $companyId)->whereKey($productId)->exists()) {
            throw ValidationException::withMessages([
                'product_id' => 'The selected product does not belong to this company.',
            ]);
        }
    }

    private function assertEffectiveDateRange(string $effectiveFrom, ?string $effectiveTo): void
    {
        if ($effectiveTo !== null && Carbon::parse($effectiveTo)->lt(Carbon::parse($effectiveFrom))) {
            throw ValidationException::withMessages([
                'effective_to' => 'The effective end date must be on or after the effective start date.',
            ]);
        }
    }

    private function companyQuery(int $companyId): Builder
    {
        $query = ProductPrice::query();
        $this->scopeToCurrentCompany($query);

        return $query->where('product_prices.company_id', $companyId);
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
