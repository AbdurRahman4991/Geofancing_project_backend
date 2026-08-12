<?php

namespace App\Http\Controllers\Api\Employee;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Employee;
use App\Models\EmployeeLocation;

class EmployeeLocationEmployeeController extends Controller
{
     public function employeesLocationEmployee(Request $request)
    {       
        $search = $request->search;

        $employeeIds = EmployeeLocation::query()
            ->whereNotNull('employee_id')
            ->distinct()
            ->pluck('employee_id');

        $employees = Employee::query()
            ->whereIn('id', $employeeIds)
            ->when($search, function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                    ->orWhere('employee_id', 'like', "%{$search}%");
                });
            })
            ->select('id', 'name', 'employee_id')
            ->orderBy('name')
            ->limit(20)
            ->get();

        return response()->json([
            'status' => true,
            'data' => $employees,
        ]);
    }
}
