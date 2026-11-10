<?php

namespace App\Http\Controllers\Api\Product;

use App\Http\Controllers\Controller;
use App\Services\Product\ProductService;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function __construct(private readonly ProductService $productService)
    {
    }

    public function index(Request $request)
    {
        $this->authorizeAction('view');

        return response()->json([
            'message' => 'Product list retrieved successfully',
            'data' => $this->productService->index($request),
        ]);
    }

    public function store(Request $request)
    {
        $this->authorizeAction('create');

        return response()->json([
            'message' => 'Product created successfully',
            'data' => $this->productService->store($request),
        ], 201);
    }

    public function catalog(Request $request)
    {
        $this->authorizeAction('catalog-view');

        return response()->json([
            'message' => 'Product catalog retrieved successfully',
            'data' => $this->productService->catalog($request),
        ]);
    }


    public function show(Request $request, int $id)
    {
        $this->authorizeAction('edit');

        return response()->json(['data' => $this->productService->show($request, $id)]);
    }

    public function update(Request $request, int $id)
    {
        $this->authorizeAction('update');

        return response()->json([
            'message' => 'Product updated successfully',
            'data' => $this->productService->update($request, $id),
        ]);
    }

    public function destroy(Request $request, int $id)
    {
        $this->authorizeAction('delete');
        $this->productService->destroy($request, $id);

        return response()->json(['message' => 'Product deleted successfully']);
    }

    private function authorizeAction(string $action): void
    {
        abort_unless(auth()->user()?->can('product.' . $action), 403, 'You do not have permission to ' . $action . ' products.');
    }
}
