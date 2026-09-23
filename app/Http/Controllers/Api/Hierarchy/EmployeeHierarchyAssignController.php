<?php

namespace App\Http\Controllers\Api\Hierarchy;

use App\Http\Controllers\Controller;
use App\Services\Hierarchy\EmployeeHierarchyAssignmentService;
use Illuminate\Http\Request;

class EmployeeHierarchyAssignController extends Controller
{
    protected EmployeeHierarchyAssignmentService $service;

    public function __construct(
        EmployeeHierarchyAssignmentService $service
    ) {
        $this->service = $service;
    }

    public function index(Request $request)
    {
        $assignments = $this->service->getAll(
            $request->all()
        );

        return response()->json([
            'status' => 200,
            'message' => 'Employee hierarchy assignments fetched successfully.',
            'data' => $assignments,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'country_id' => ['nullable', 'integer', 'exists:countries,id'],
            'region_id' => ['nullable', 'integer', 'exists:regions,id'],
            'zone_id' => ['nullable', 'integer', 'exists:zones,id'],
            'division_id' => ['nullable', 'integer', 'exists:divisions,id'],
            'district_id' => ['nullable', 'integer', 'exists:districts,id'],
            'sub_district_id' => ['nullable', 'integer', 'exists:sub_districts,id'],
            'territory_id' => ['nullable', 'integer', 'exists:territories,id'],
            'area_id' => ['nullable', 'integer', 'exists:areas,id'],
            'effective_from' => ['nullable', 'date'],
            'reason' => ['nullable', 'string', 'max:1000'],
        ]);

        $assignment = $this->service->create($validated);

        return response()->json([
            'status' => 201,
            'message' => 'Employee hierarchy assignment created successfully.',
            'data' => $assignment,
        ], 201);
    }

    public function show(int $id)
    {
        $assignment = $this->service->getById($id);

        if (!$assignment) {
            return response()->json([
                'status' => 404,
                'message' => 'Employee hierarchy assignment not found.',
            ], 404);
        }

        return response()->json([
            'status' => 200,
            'data' => $assignment,
        ]);
    }

    public function update(Request $request, int $id)
    {
        $validated = $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'country_id' => ['nullable', 'integer', 'exists:countries,id'],
            'region_id' => ['nullable', 'integer', 'exists:regions,id'],
            'zone_id' => ['nullable', 'integer', 'exists:zones,id'],
            'division_id' => ['nullable', 'integer', 'exists:divisions,id'],
            'district_id' => ['nullable', 'integer', 'exists:districts,id'],
            'sub_district_id' => ['nullable', 'integer', 'exists:sub_districts,id'],
            'territory_id' => ['nullable', 'integer', 'exists:territories,id'],
            'area_id' => ['nullable', 'integer', 'exists:areas,id'],
            'effective_from' => ['nullable', 'date'],
            'reason' => ['nullable', 'string', 'max:1000'],
        ]);

        $assignment = $this->service->update($id, $validated);

        if (!$assignment) {
            return response()->json([
                'status' => 404,
                'message' => 'Employee hierarchy assignment not found.',
            ], 404);
        }

        return response()->json([
            'status' => 200,
            'message' => 'Employee hierarchy assignment updated successfully.',
            'data' => $assignment,
        ]);
    }

    public function destroy(int $id)
    {
        $deleted = $this->service->delete($id);

        if (!$deleted) {
            return response()->json([
                'status' => 404,
                'message' => 'Employee hierarchy assignment not found.',
            ], 404);
        }

        return response()->json([
            'status' => 200,
            'message' => 'Employee hierarchy assignment deleted successfully.',
        ]);
    }
}
