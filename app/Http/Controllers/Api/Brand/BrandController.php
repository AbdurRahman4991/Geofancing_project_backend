<?php

namespace App\Http\Controllers\Api\Brand;

use App\Http\Controllers\Controller;
use App\Services\Brand\BrandService;
use Illuminate\Http\Request;

class BrandController extends Controller
{
    public function __construct(private readonly BrandService $brandService)
    {
    }

    public function index(Request $request)
    {
        $this->authorizeAction('view');

        return response()->json([
            'message' => 'Brand list retrieved successfully',
            'data' => $this->brandService->index($request),
        ]);
    }

    public function store(Request $request)
    {
        $this->authorizeAction('create');

        return response()->json([
            'message' => 'Brand created successfully',
            'data' => $this->brandService->store($request),
        ], 201);
    }

    public function show(Request $request, int $id)
    {
        $this->authorizeAction('view');

        return response()->json(['data' => $this->brandService->show($request, $id)]);
    }

    public function update(Request $request, int $id)
    {
        $this->authorizeAction('edit');

        return response()->json([
            'message' => 'Brand updated successfully',
            'data' => $this->brandService->update($request, $id),
        ]);
    }

    public function destroy(Request $request, int $id)
    {
        $this->authorizeAction('delete');
        $this->brandService->destroy($request, $id);

        return response()->json(['message' => 'Brand deleted successfully']);
    }

    private function authorizeAction(string $action): void
    {
        abort_unless(auth()->user()?->can('brand.' . $action), 403, 'You do not have permission to ' . $action . ' brands.');
    }
}
