<?php


namespace App\Services\Employee;

use Illuminate\Support\Facades\Http;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class EmployeeService
{
    
    // public function index(Request $request)
    // {
    //     $query = Employee::with(['company:id,company_name']);

    //     // Search by Name, Employee ID, or Phone
    //     if ($request->filled('search')) {
    //         $search = $request->search;

    //         $query->where(function ($q) use ($search) {
    //             $q->where('name', 'like', "%{$search}%")
    //             ->orWhere('employee_id', 'like', "%{$search}%")
    //             ->orWhere('phone', 'like', "%{$search}%");
    //         });
    //     }

    //     // Filter by Department
    //     if ($request->filled('department')) {
    //         $query->where('department', 'like', '%' . $request->department . '%');
    //     }

    //     // Latest First
    //     $query->latest();

    //     // Pagination
    //     $employees = $query->paginate($request->per_page ?? 10);

    //     return response()->json($employees);
    // }


    public function index(Request $request)
    {
        $cacheKey = 'employees_' . md5(json_encode([
            'search' => $request->search,
            'department' => $request->department,
            'page' => $request->page,
            'per_page' => $request->per_page,
        ]));

        $employees = Cache::remember($cacheKey, now()->addMinutes(10), function () use ($request) {

            $query = Employee::with(['company:id,company_name']);

            if ($request->filled('search')) {
                $search = $request->search;

                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                    ->orWhere('employee_id', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
                });
            }

            if ($request->filled('department')) {
                $query->where('department', 'like', '%' . $request->department . '%');
            }

            return $query->latest()->paginate($request->per_page ?? 10);
        });

        return response()->json($employees);
    }

    // ✅ নতুন এমপ্লয়ি তৈরি
    public function store(Request $request)
    {
        $data = $request->validate([
            'employee_id' => 'required|string|unique:employees,employee_id',
            'company_id' => 'required|exists:companies,id',
            'phone' => 'required|string',
        ]);

        return Employee::create($data);
    }
  

    public function syncEmployees()
    {
        $response = Http::get('http://192.168.20.22:8001/api/Employee/GetEmployeeList');

        if (!$response->successful()) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch employee data.',
            ], 500);
        }

        $employees = $response->json('data');

        foreach ($employees as $item) {

            Employee::updateOrCreate(
                [
                    // Unique Key
                    'employee_id' => $item['employeeID'],
                ],
                [
                    'name' => $item['name'],
                    'company_id' => $item['buid'], // প্রয়োজনে mapping করতে হবে
                    'phone' => $item['mobileNo'] ?? null,
                    'status' => $item['isActive'] ? 1 : 0,

                    'nature_of_employment' => null,

                    'department' => $item['departmentName'],
                    'unit' => $item['businessUnitName'],
                    'date_of_joining' => $item['dateOfJoining'],

                    'division' => $item['divisionName'],
                    'designation' => $item['designation'],

                    'reporting_person' => null,

                    'email' => null,
                    'dob' => null,

                    'section_info' => trim($item['sectionName']),

                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }

        return response()->json([
            'success' => true,
            'message' => 'Employees synchronized successfully.',
            'total' => count($employees),
        ]);
    }

    // ✅ নির্দিষ্ট এমপ্লয়ি দেখানো
    public function show($id)
    {
        return Employee::with(['company:id,company_name'])->findOrFail($id);
    }

    // ✅ এমপ্লয়ি আপডেট করা
    public function update(Request $request, $id)
    {
        $employee = Employee::findOrFail($id);

        $data = $request->validate([
            'employee_id' => 'required|string|unique:employees,employee_id,' . $employee->id,
            'company_id' => 'required|exists:companies,id',
            'phone' => 'required|string',
        ]);

        $employee->update($data);
        return $employee;
    }

    // ✅ এমপ্লয়ি ডিলিট করা
    public function destroy($id)
    {
        $employee = Employee::findOrFail($id);
        return $employee->delete();
    }
}