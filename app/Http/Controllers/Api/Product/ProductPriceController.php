<?php

namespace App\Http\Controllers\Api\Product;

use App\Http\Controllers\Controller;
use App\Services\Product\ProductPriceService;
use Illuminate\Http\Request;

class ProductPriceController extends Controller
{
    public function __construct(private readonly ProductPriceService $priceService)
    {
    }

    public function index(Request $request)
    {
        $this->authorizeAction('view');

        return response()->json([
            'message' => 'Product prices retrieved successfully',
            'data' => $this->priceService->index($request),
        ]);
    }

    public function store(Request $request)
    {
        $this->authorizeAction('create');

        return response()->json([
            'message' => 'Product price created successfully',
            'data' => $this->priceService->store($request),
        ], 201);
    }

    public function show(Request $request, int $id)
    {
        $this->authorizeAction('view');

        return response()->json(['data' => $this->priceService->show($request, $id)]);
    }

    public function update(Request $request, int $id)
    {
        $this->authorizeAction('edit');

        return response()->json([
            'message' => 'Product price updated successfully',
            'data' => $this->priceService->update($request, $id),
        ]);
    }

    public function destroy(Request $request, int $id)
    {
        $this->authorizeAction('delete');
        $this->priceService->destroy($request, $id);

        return response()->json(['message' => 'Product price deleted successfully']);
    }

    private function authorizeAction(string $action): void
    {
        abort_unless(auth()->user()?->can('product-price.' . $action), 403, 'You do not have permission to ' . $action . ' product prices.');
    }
}
