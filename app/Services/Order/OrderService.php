<?php

namespace App\Services\Order;

use App\Models\Order;
use App\Models\Product;
use App\Models\ProductPrice;
use App\Services\Hierarchy\HierarchyAccessService;
use App\Traits\ScopesCompanyAccess;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class OrderService
{
    use ScopesCompanyAccess;

    public function __construct(private readonly HierarchyAccessService $hierarchyAccessService)
    {
    }

    public function index(Request $request)
    {
        $query = $this->companyQuery($this->companyId($request))
            ->with(['geofence:id,firm_name,address', 'items.product:id,product_name']);

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }
        if ($request->filled('search')) {
            $search = $request->string('search')->trim()->toString();
            $query->where(fn (Builder $q) => $q->where('order_number', 'like', "%{$search}%")
                ->orWhereHas('geofence', fn (Builder $g) => $g->where('firm_name', 'like', "%{$search}%")));
        }

        return $query->latest()->paginate(min(max($request->integer('per_page', 10), 1), 100))->withQueryString();
    }

    public function store(Request $request): Order
    {
        $companyId = $this->companyId($request);
        $data = $this->validatedData($request, $companyId);
        $this->assertGeofenceBelongsToCompany($companyId, $data['geofence_id']);
        $this->assertProductsBelongToCompany($companyId, $data['items']);

        return DB::transaction(function () use ($request, $companyId, $data) {
            $order = Order::create([
                'company_id' => $companyId,
                'geofence_id' => $data['geofence_id'],
                'created_by' => $request->user()->id,
                'order_number' => $data['order_number'],
                'status' => $data['status'] ?? 'pending',
                'notes' => $data['notes'] ?? null,
                'total' => 0,
            ]);
            $this->replaceItems($order, $data['items'], $companyId);

            return $order->load(['geofence:id,firm_name,address', 'items.product:id,product_name']);
        });
    }

    public function show(Request $request, int $id): Order
    {
        return $this->companyQuery($this->companyId($request))
            ->with(['geofence:id,firm_name,address', 'items.product:id,product_name'])->findOrFail($id);
    }

    public function update(Request $request, int $id): Order
    {
        $companyId = $this->companyId($request);
        $order = $this->companyQuery($companyId)->findOrFail($id);
        $data = $this->validatedData($request, $companyId, true, $order->id);
        if (isset($data['geofence_id'])) {
            $this->assertGeofenceBelongsToCompany($companyId, $data['geofence_id']);
        }
        if (isset($data['items'])) {
            $this->assertProductsBelongToCompany($companyId, $data['items']);
        }

        return DB::transaction(function () use ($order, $data, $companyId) {
            $order->update(collect($data)->except('items')->all());
            if (isset($data['items'])) {
                $this->replaceItems($order, $data['items'], $companyId);
            }

            return $order->refresh()->load(['geofence:id,firm_name,address', 'items.product:id,product_name']);
        });
    }

    public function destroy(Request $request, int $id): void
    {
        $this->companyQuery($this->companyId($request))->findOrFail($id)->delete();
    }

    private function validatedData(Request $request, int $companyId, bool $updating = false, ?int $ignoreId = null): array
    {
        $required = $updating ? 'sometimes' : 'required';
        $rules = [
            'order_number' => [$required, 'string', 'max:100', Rule::unique('orders', 'order_number')->where('company_id', $companyId)->ignore($ignoreId)],
            'geofence_id' => [$required, 'integer', 'exists:geofences,id'],
            'status' => ['sometimes', Rule::in(['pending', 'confirmed', 'processing', 'completed', 'cancelled','delivered'])],
            'notes' => ['sometimes', 'nullable', 'string', 'max:5000'],
            'items' => [$updating ? 'sometimes' : 'required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer', 'distinct', 'exists:products,id'],
            'items.*.quantity' => ['required', 'numeric', 'gt:0'],
        ];

        return $request->validate($rules);
    }

    private function replaceItems(Order $order, array $items, int $companyId): void
    {
        $order->items()->delete();
        $total = 0;
        $orderDate = now()->toDateString();

        foreach ($items as $index => $item) {
            $price = ProductPrice::query()
                ->where('company_id', $companyId)
                ->where('product_id', $item['product_id'])
                ->whereDate('effective_from', '<=', $orderDate)
                ->where(fn (Builder $query) => $query->whereNull('effective_to')
                    ->orWhereDate('effective_to', '>=', $orderDate))
                ->orderByDesc('effective_from')
                ->orderByDesc('id')
                ->lockForUpdate()
                ->first();

            if (!$price) {
                throw ValidationException::withMessages([
                    "items.{$index}.product_id" => 'No active sale price is configured for this product.',
                ]);
            }

            $unitPrice = (float) $price->sale_price;
            $lineTotal = round((float) $item['quantity'] * $unitPrice, 2);
            $order->items()->create([
                'product_id' => $item['product_id'],
                'quantity' => $item['quantity'],
                'unit_price' => $unitPrice,
                'line_total' => $lineTotal,
            ]);
            $total += $lineTotal;
        }
        $order->update(['total' => round($total, 2)]);
    }

    private function assertProductsBelongToCompany(int $companyId, array $items): void
    {
        $productIds = collect($items)->pluck('product_id')->unique();
        $count = Product::query()->where('company_id', $companyId)->whereIn('id', $productIds)->count();
        if ($count !== $productIds->count()) {
            throw ValidationException::withMessages(['items' => 'Every product must belong to this company.']);
        }
    }

    private function assertGeofenceBelongsToCompany(int $companyId, int $geofenceId): void
    {
        $isAccessible = $this->hierarchyAccessService->accessibleGeofences()
            ->where('company_id', $companyId)
            ->whereKey($geofenceId)
            ->exists();

        if (!$isAccessible) {
            throw ValidationException::withMessages(['geofence_id' => 'The selected geofence is outside your company or hierarchy access.']);
        }
    }

    private function companyQuery(int $companyId): Builder
    {
        $query = Order::query();
        $this->scopeToCurrentCompany($query);

        $accessibleGeofences = $this->hierarchyAccessService->accessibleGeofences()
            ->where('geofences.company_id', $companyId)
            ->select('geofences.id');

        return $query->where('orders.company_id', $companyId)
            ->whereIn('orders.geofence_id', $accessibleGeofences);
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
