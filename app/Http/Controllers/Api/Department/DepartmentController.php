<?php

namespace App\Http\Controllers\Api\Department;

use App\Http\Controllers\Controller;
use App\Services\Department\DepartmentService;
use Illuminate\Http\Request;

class DepartmentController extends Controller
{
    public function __construct(private readonly DepartmentService $departmentService)
    {
    }

    public function index(Request $request)
    {
        return response()->json(['message' => 'Department list retrieved successfully', 'data' => $this->departmentService->index($request)]);
    }

    public function store(Request $request)
    {
        return response()->json(['message' => 'Department created successfully', 'data' => $this->departmentService->store($request)], 201);
    }

    public function show(int $id)
    {
        return response()->json(['data' => $this->departmentService->show($id)]);
    }

    public function update(Request $request, int $id)
    {
        return response()->json(['message' => 'Department updated successfully', 'data' => $this->departmentService->update($request, $id)]);
    }

    public function destroy(int $id)
    {
        $this->departmentService->destroy($id);
        return response()->json(['message' => 'Department deleted successfully']);
    }
}
