<?php

namespace App\Services\Product;

use App\Models\Product;
use App\Models\ProductStock;
use App\Traits\ScopesCompanyAccess;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ProductStockService
{
    use ScopesCompanyAccess;

    public function index(Request $request)
    {
        $query = $this->companyQuery($this->companyId($request))
            ->with(['product:id,company_id,product_name,unit_id', 'product.unit:id,unit_name,short_name']);

        if ($request->filled('search')) {
            $search = $request->string('search')->trim()->toString();
            $query->whereHas('product', fn (Builder $productQuery) =>
                $productQuery->where('product_name', 'like', "%{$search}%"));
        }

        if ($request->filled('product_id')) {
            $query->where('product_id', $request->input('product_id'));
        }

        if ($request->filled('warehouse_id')) {
            $query->where('warehouse_id', $request->input('warehouse_id'));
        }

        return $query->latest('id')
            ->paginate(min(max($request->integer('per_page', 10), 1), 100))
            ->withQueryString();
    }

    public function store(Request $request): ProductStock
    {
        $companyId = $this->companyId($request);
        $data = $this->validatedData($request, $companyId);
        $this->assertProductBelongsToCompany($companyId, (int) $data['product_id']);
        $this->assertUniqueProductWarehouse($companyId, (int) $data['product_id'], (int) $data['warehouse_id']);
        $this->assertReservedQuantityDoesNotExceedStock(
            (float) $data['quantity'],
            (float) ($data['reserved_quantity'] ?? 0),
        );

        return ProductStock::create($data + ['company_id' => $companyId])
            ->load(['product:id,company_id,product_name,unit_id', 'product.unit:id,unit_name,short_name']);
    }

    public function show(Request $request, int $id): ProductStock
    {
        return $this->companyQuery($this->companyId($request))
            ->with(['product:id,company_id,product_name,unit_id', 'product.unit:id,unit_name,short_name'])
            ->findOrFail($id);
    }

    public function update(Request $request, int $id): ProductStock
    {
        $companyId = $this->companyId($request);
        $stock = $this->companyQuery($companyId)->findOrFail($id);
        $data = $this->validatedData($request, $companyId, $stock);
        $productId = (int) ($data['product_id'] ?? $stock->product_id);
        $warehouseId = (int) ($data['warehouse_id'] ?? $stock->warehouse_id);
        $quantity = (float) ($data['quantity'] ?? $stock->quantity);
        $reservedQuantity = (float) ($data['reserved_quantity'] ?? $stock->reserved_quantity);
        $this->assertProductBelongsToCompany($companyId, $productId);
        $this->assertUniqueProductWarehouse($companyId, $productId, $warehouseId, $stock->id);
        $this->assertReservedQuantityDoesNotExceedStock($quantity, $reservedQuantity);
        $stock->update($data);

        return $stock->refresh()->load(['product:id,company_id,product_name,unit_id', 'product.unit:id,unit_name,short_name']);
    }

    public function destroy(Request $request, int $id): void
    {
        $this->companyQuery($this->companyId($request))->findOrFail($id)->delete();
    }

    private function validatedData(Request $request, int $companyId, ?ProductStock $stock = null): array
    {
        return $request->validate([
            'product_id' => [
                $stock ? 'sometimes' : 'required',
                'integer',
                'exists:products,id',
            ],
            'warehouse_id' => [$stock ? 'sometimes' : 'required', 'integer', 'min:1'],
            'quantity' => [$stock ? 'sometimes' : 'required', 'numeric', 'min:0', 'max:999999999.999'],
            'reserved_quantity' => ['sometimes', 'numeric', 'min:0', 'max:999999999.999'],
            'reorder_level' => ['sometimes', 'numeric', 'min:0', 'max:999999999.999'],
        ]);
    }

    private function assertUniqueProductWarehouse(int $companyId, int $productId, int $warehouseId, ?int $ignoreId = null): void
    {
        $exists = ProductStock::query()
            ->where('company_id', $companyId)
            ->where('product_id', $productId)
            ->where('warehouse_id', $warehouseId)
            ->when($ignoreId, fn (Builder $query) => $query->where('id', '!=', $ignoreId))
            ->exists();

        if ($exists) {
            throw ValidationException::withMessages([
                'product_id' => 'This product already has a stock record for the selected warehouse.',
            ]);
        }
    }

    private function assertReservedQuantityDoesNotExceedStock(float $quantity, float $reservedQuantity): void
    {
        if ($reservedQuantity > $quantity) {
            throw ValidationException::withMessages([
                'reserved_quantity' => 'Reserved quantity cannot exceed the available stock quantity.',
            ]);
        }
    }

    private function assertProductBelongsToCompany(int $companyId, int $productId): void
    {
        if (!Product::query()->where('company_id', $companyId)->whereKey($productId)->exists()) {
            throw ValidationException::withMessages([
                'product_id' => 'The selected product does not belong to this company.',
            ]);
        }
    }

    private function companyQuery(int $companyId): Builder
    {
        $query = ProductStock::query();
        $this->scopeToCurrentCompany($query);

        return $query->where('product_stocks.company_id', $companyId);
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
