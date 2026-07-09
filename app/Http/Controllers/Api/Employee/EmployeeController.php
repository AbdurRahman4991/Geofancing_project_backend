<?php

namespace App\Http\Controllers\Api\Employee;

use App\Http\Controllers\Controller;
use App\Services\Employee\EmployeeService;
use Illuminate\Http\Request;

class EmployeeController extends Controller
{
    protected $employeeService;

    public function __construct(EmployeeService $employeeService)
    {
        $this->employeeService = $employeeService;
    }

    public function index(Request $request)
    {
        return $this->employeeService->index($request);
    }

    // public function store(Request $request)
    // {
    //     $employee = $this->employeeService->store($request);
    //     return response()->json(['message' => 'Employee created successfully', 'data' => $employee]);
    // }
    public function syncEmployees()
    {
        $result = $this->employeeService->syncEmployees();

        return response()->json([
            'success' => true,
            'message' => 'Employees synchronized successfully.',
            'data' => $result,
        ]);
    }

    public function show($id)
    {
        $employee = $this->employeeService->show($id);
        return response()->json(['data' => $employee]);
    }

    public function update(Request $request, $id)
    {
        $employee = $this->employeeService->update($request, $id);
        return response()->json(['message' => 'Employee updated successfully', 'data' => $employee]);
    }

    public function destroy($id)
    {
        $this->employeeService->destroy($id);
        return response()->json(['message' => 'Employee deleted successfully']);
    }
}
