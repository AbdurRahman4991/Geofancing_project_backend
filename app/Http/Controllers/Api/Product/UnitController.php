<?php

namespace App\Http\Controllers\Api\Product;

use App\Http\Controllers\Controller;
use App\Services\Product\UnitService;
use Illuminate\Http\Request;

class UnitController extends Controller
{
    public function __construct(private readonly UnitService $unitService)
    {
    }

    public function index(Request $request)
    {
        $this->authorizeAction('view');

        return response()->json([
            'message' => 'Unit list retrieved successfully',
            'data' => $this->unitService->index($request),
        ]);
    }

    public function store(Request $request)
    {
        $this->authorizeAction('create');

        return response()->json([
            'message' => 'Unit created successfully',
            'data' => $this->unitService->store($request),
        ], 201);
    }

    public function show(Request $request, int $id)
    {
        $this->authorizeAction('view');

        return response()->json(['data' => $this->unitService->show($request, $id)]);
    }

    public function update(Request $request, int $id)
    {
        $this->authorizeAction('edit');

        return response()->json([
            'message' => 'Unit updated successfully',
            'data' => $this->unitService->update($request, $id),
        ]);
    }

    public function destroy(Request $request, int $id)
    {
        $this->authorizeAction('delete');
        $this->unitService->destroy($request, $id);

        return response()->json(['message' => 'Unit deleted successfully']);
    }

    private function authorizeAction(string $action): void
    {
        abort_unless(auth()->user()?->can('unit.' . $action), 403, 'You do not have permission to ' . $action . ' units.');
    }
}
