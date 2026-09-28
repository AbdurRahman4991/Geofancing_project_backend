<?php

namespace App\Services\Attendance;

use App\Models\AttendanceRule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Traits\CompanyScoped;

class AttendanceRuleService
{
    use CompanyScoped;
    public function index(Request $request)
    {
        if (!auth()->user()->can('attendance-role.view')) {
        abort(403, 'You do not have permission to view attendance role view.');
        }
        $query = AttendanceRule::with([
            'company:id,company_name',
            'user:id,name'
        ]);
        $this->applyCompanyScope($query);

        // 🔍 Search
        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->whereHas('company', function ($qc) use ($search) {
                    $qc->where('company_name', 'like', "%{$search}%");
                })
                ->orWhereHas('user', function ($qu) use ($search) {
                    $qu->where('name', 'like', "%{$search}%");
                });
            });
        }

        // 📄 Pagination
        return $query->latest()->paginate(
            $request->get('per_page', 10)
        );
    }

    public function store(Request $request)
    {
        if (!auth()->user()->can('attendance-role.create')) {
        abort(403, 'You do not have permission to create  attendance role.');
        }
        $attendanceRule = AttendanceRule::create($request->only([
            'user_id',
            'company_id',
            'office_in_time',
            'office_out_time',
            'weekend_holidays',
            'government_holidays',
            'is_active',
        ]));

        return $attendanceRule->load(['company:id,company_name', 'user:id,name']);
    }

    public function show($id)
    {
        if (!auth()->user()->can('attendance-role.edit')) {
        abort(403, 'You do not have permission to edit attendance role.');
        }        
        return AttendanceRule::with(['company:id,company_name', 'user:id,name'])
            ->findOrFail($id);
    }

    public function update(Request $request, $id)
    {
        if (!auth()->user()->can('attendance-role.edit')) {
        abort(403, 'You do not have permission to view  attendance role.');
        }
        $attendanceRule = AttendanceRule::findOrFail($id);

        $attendanceRule->update($request->only([
            'user_id',
            'company_id',
            'office_in_time',
            'office_out_time',
            'weekend_holidays',
            'government_holidays',
            'is_active',
        ]));

        return $attendanceRule->fresh(['company:id,company_name', 'user:id,name']);
    }

    public function destroy($id)
    {
        $attendanceRule = AttendanceRule::findOrFail($id);
        return $attendanceRule->delete();
    }
}
