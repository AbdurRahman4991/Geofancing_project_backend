<?php

namespace App\Services\Attendance;

use App\Models\AttendanceRule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class AttendanceRuleService
{
    public function index()
    {
        return AttendanceRule::with(['company:id,company_name', 'user:id,name'])
            ->latest()
            ->get();
    }

    public function store(Request $request)
    {
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
        return AttendanceRule::with(['company:id,company_name', 'user:id,name'])
            ->findOrFail($id);
    }

    public function update(Request $request, $id)
    {
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
