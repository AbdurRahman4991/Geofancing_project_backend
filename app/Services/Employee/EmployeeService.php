<?php


namespace App\Services\Employee;

use App\Models\Employee;
use Illuminate\Http\Request;

class EmployeeService
{
    // ✅ সব এমপ্লয়ি লিস্ট
    public function index()
    {
        return Employee::with(['company:id,company_name'])->latest()->get();
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
