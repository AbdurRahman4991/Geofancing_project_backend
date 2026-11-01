<?php

namespace App\Http\Controllers\Api\Category;

use App\Http\Controllers\Controller;
use App\Services\Category\SubCategoryService;
use Illuminate\Http\Request;

class SubCategoryController extends Controller
{
    public function __construct(private readonly SubCategoryService $service)
    {
    }

    public function index(Request $request)
    {
        $this->authorizeAction('view');
        return response()->json(['status' => 200, 'data' => $this->service->index($request)]);
    }

    public function store(Request $request)
    {
        $this->authorizeAction('create');
        return response()->json(['status' => 201, 'message' => 'Subcategory created successfully.', 'data' => $this->service->store($request)], 201);
    }

    public function show(Request $request, int $id)
    {
        $this->authorizeAction('view');
        return response()->json(['status' => 200, 'data' => $this->service->show($request, $id)]);
    }

    public function update(Request $request, int $id)
    {
        $this->authorizeAction('edit');
        return response()->json(['status' => 200, 'message' => 'Subcategory updated successfully.', 'data' => $this->service->update($request, $id)]);
    }

    public function destroy(Request $request, int $id)
    {
        $this->authorizeAction('delete');
        $this->service->destroy($request, $id);
        return response()->json(['status' => 200, 'message' => 'Subcategory deleted successfully.']);
    }

    private function authorizeAction(string $action): void
    {
        abort_unless(auth()->user()?->can('sub-category.' . $action), 403, 'You do not have permission to ' . $action . ' subcategories.');
    }
}
