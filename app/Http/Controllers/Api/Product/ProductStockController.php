<?php

namespace App\Http\Controllers\Api\Product;

use App\Http\Controllers\Controller;
use App\Services\Product\ProductStockService;
use Illuminate\Http\Request;

class ProductStockController extends Controller
{
    public function __construct(private readonly ProductStockService $stockService)
    {
    }

    public function index(Request $request)
    {
        $this->authorizeAction('view');

        return response()->json([
            'message' => 'Product stock list retrieved successfully',
            'data' => $this->stockService->index($request),
        ]);
    }

    public function store(Request $request)
    {
        $this->authorizeAction('create');

        return response()->json([
            'message' => 'Product stock created successfully',
            'data' => $this->stockService->store($request),
        ], 201);
    }

    public function show(Request $request, int $id)
    {
        $this->authorizeAction('view');

        return response()->json(['data' => $this->stockService->show($request, $id)]);
    }

    public function update(Request $request, int $id)
    {
        $this->authorizeAction('edit');

        return response()->json([
            'message' => 'Product stock updated successfully',
            'data' => $this->stockService->update($request, $id),
        ]);
    }

    public function destroy(Request $request, int $id)
    {
        $this->authorizeAction('delete');
        $this->stockService->destroy($request, $id);

        return response()->json(['message' => 'Product stock deleted successfully']);
    }

    private function authorizeAction(string $action): void
    {
        abort_unless(auth()->user()?->can('product-stock.' . $action), 403, 'You do not have permission to ' . $action . ' product stocks.');
    }
}
