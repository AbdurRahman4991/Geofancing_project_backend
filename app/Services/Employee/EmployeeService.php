<?php


namespace App\Services\Employee;

use App\Models\Employee;
use Illuminate\Http\Request;
use App\Http\Requests\EmployRequest;

class EmployeeService
{
    // ✅ সব এমপ্লয়ি লিস্ট
    // public function index()
    // {
    //     return Employee::with(['company:id,company_name'])->latest()->get();
    // }
    public function index(Request $request)
{
    $query = Employee::with(['company:id,company_name']); // relationship

    // 🔍 Search
    if ($request->filled('search')) {
        $search = $request->search;

        $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")
                ->orWhere('phone', 'like', "%{$search}%")
                ->orWhere('designation', 'like', "%{$search}%")
                ->orWhereHas('company', function ($c) use ($search) {
                    $c->where('company_name', 'like', "%{$search}%");
                });
        });
    }

    // ↕ Sorting
    $orderBy = $request->get('orderBy', 'id');   // default column
    $orderDir = $request->get('order', 'desc');  // default direction
    $query->orderBy($orderBy, $orderDir);

    // 📄 Pagination
    $perPage = $request->get('limit', 10);
    $employees = $query->paginate($perPage);

    return response()->json([
        'status'  => 200,
        'message' => 'Employee list retrieved successfully',
        'data'    => $employees
    ]);
}



    // ✅ নতুন এমপ্লয়ি তৈরি
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'employee_id' => 'required|string|unique:employees,employee_id',
            'company_id' => 'nullable|exists:companies,id',

            'phone' => 'required|string|max:20',
            'status' => 'required|in:active,inactive,terminated',
            'nature_of_employment' => 'required|string',

            'department' => 'nullable|string|max:255',
            'unit' => 'nullable|string|max:255',
            'division' => 'nullable|string|max:255',
            'designation' => 'nullable|string|max:255',
            'reporting_person' => 'nullable|string|max:255',

            'date_of_joining' => 'required|date',

            'email' => 'nullable|email',
            'dob' => 'nullable|date',
            'section_info' => 'nullable|string|max:255',
        ]);

        return Employee::create($data);
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
             'name' => 'required|string|max:255',
            'company_id' => 'nullable|exists:companies,id',
            'phone' => 'required|string|max:20',
            'status' => 'required|in:active,inactive,terminated',
            'nature_of_employment' => 'required|string',
            'department' => 'nullable|string|max:255',
            'unit' => 'nullable|string|max:255',
            'division' => 'nullable|string|max:255',
            'designation' => 'nullable|string|max:255',
            'reporting_person' => 'nullable|string|max:255',
            'date_of_joining' => 'required|date',
            'email' => 'nullable|email',
            'dob' => 'nullable|date',
            'section_info' => 'nullable|string|max:255',
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
