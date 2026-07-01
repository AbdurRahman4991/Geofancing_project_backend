<?php

namespace App\Services\Attendance;

use App\Models\Attendance;
use App\Models\AttendanceRule;
use App\Models\Geofence;
use App\Models\Employee;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class AttendanceService
{
    /**
     * 🟢 Handle Employee Check-In
     */
    public function checkIn($data)
    {
        $user = Auth::user();

        // ✅ Step 1: Get geofence
        $geofence = Geofence::where('user_id', $user->id)->first();
        if (!$geofence) {
            return ['success' => false, 'message' => 'Geofence not set for this employee!'];
        }

        // ✅ Step 2: Get attendance rule
        $attendanceRule = AttendanceRule::where('user_id', $user->id)->first();

        if (!$attendanceRule) {
            
            $employee = Employee::where('employee_id', $user->employee_id)->first();

            if ($employee) {
                $attendanceRule = AttendanceRule::where('company_id', $employee->company_id)
                                                ->whereNull('user_id')
                                                ->first();
            }
            return ['success' => false, 'message' => 'Attendance rule not found!'];
        }

        // $attendanceRule = AttendanceRule::where('user_id', $user->id)->first() ?? AttendanceRule::whereNull('user_id')->first();
        // if (!$attendanceRule) {
        //     return ['success' => false, 'message' => 'Attendance rule not found!'];
        // }

        // ✅ Step 3: Prevent multiple check-ins
        if (Attendance::where('user_id', $user->id)->whereDate('check_in_time', Carbon::today())->exists()) {
            return ['success' => false, 'message' => 'You have already checked in today!'];
        }

        // ✅ Step 4: Calculate distance
        $distance = $this->calculateDistance(
            $geofence->latitude,
            $geofence->longitude,
            $data['latitude'],
            $data['longitude']
        );

        if ($distance > $geofence->radius) {
            return [
                'success' => false,
                'message' => 'You are outside your allowed area!',
                'distance' => round($distance, 2)
            ];
        }

        // ✅ Step 5: Determine lateness
        $officeInTime = Carbon::parse($attendanceRule->office_in_time, 'Asia/Dhaka');
        $checkInTime = Carbon::now('Asia/Dhaka');

        $lateFormatted = null;
        if ($checkInTime->greaterThan($officeInTime)) {
            $lateMinutes = $officeInTime->diffInMinutes($checkInTime);
            $hours = floor($lateMinutes / 60);
            $minutes = $lateMinutes % 60;
            $lateFormatted = sprintf('%02d hour %02d minute', $hours, $minutes);
        }

        // ✅ Step 6: Create attendance record
        $attendance = Attendance::create([
            'user_id' => $user->id,
            'check_in_time' => $checkInTime,
            'check_in_latitude' => $data['latitude'],
            'check_in_longitude' => $data['longitude'],
            'device_id' => $data['device_id'],
            'status' => 'Checked In',
            'distance_from_office' => $distance,
            'late' => $lateFormatted,
        ]);

        return ['success' => true, 'message' => 'Checked in successfully!', 'attendance' => $attendance];
    }

    /**
     * 🔴 Handle Employee Check-Out
     */
    public function checkOut($data)
    {        
        $user = Auth::user();

        $attendance = Attendance::where('user_id', $user->id)
            ->whereDate('check_in_time', Carbon::today('Asia/Dhaka'))
            ->latest()
            ->first();

        if (!$attendance) {
            return ['success' => false, 'message' => 'No check-in record found for today!'];
        }

            // ✅ Step 2: Get geofence for user
        $geofence = Geofence::where('user_id', $user->id)->first();
        if (!$geofence) {
            return ['success' => false, 'message' => 'Geofence not set for this employee!'];
        }

        // ✅ Step 3: Calculate distance from geofence
        $distance = $this->calculateDistance(
            $geofence->latitude,
            $geofence->longitude,
            $data['latitude'],
            $data['longitude']
        );

        if ($distance > $geofence->radius) {
            return [
                'success' => false,
                'message' => 'You are outside your allowed area! Check-out denied.',
                'distance' => round($distance, 2)
            ];
        }

        $checkOutTime = Carbon::now('Asia/Dhaka');
        $checkInTime = Carbon::parse($attendance->check_in_time, 'Asia/Dhaka');

        $workMinutes = $checkInTime->diffInMinutes($checkOutTime);
        $hours = floor($workMinutes / 60);
        $minutes = $workMinutes % 60;
        $workHourFormatted = sprintf('%02d hour %02d minute', $hours, $minutes);

        $attendance->update([
            'check_out_time' => $checkOutTime,
            'check_out_latitude' => $data['latitude'],
            'check_out_longitude' => $data['longitude'],
            'work_hour' => $workHourFormatted,
            'status' => 'Checked Out',
        ]);

        return ['success' => true, 'message' => 'Checked out successfully!', 'attendance' => $attendance];
    }

    /**
     * ⚙️ Haversine distance formula
     */
    private function calculateDistance($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371000; // meters
        $latDelta = deg2rad($lat2 - $lat1);
        $lonDelta = deg2rad($lon2 - $lon1);

        $a = sin($latDelta / 2) ** 2 +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($lonDelta / 2) ** 2;
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }

    public function attendanceHistory(Request $request)
    {
       
        $user = Auth::user();
        // $query = Attendance::where('user_id', $user->id);
        $query = Attendance::where('user_id', 1);

        if ($request->filled('year')) {
            $query->whereYear('created_at', $request->year);
        }

        if ($request->filled('month')) {
            $month = $this->getMonthNumber($request->month);
            if ($month) {
                $query->whereMonth('created_at', $month);
            }
        }

        if ($request->filled('late') && $request->late == 'yes') {
            $query->whereNotNull('late');
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $attendance = $query->orderBy('created_at', 'desc')->paginate(32);

        return response()->json([
            "status" => 200,
            "data" => $attendance
        ]);
    }


    /**
     * 🔄 Convert month name or number to numeric value
     */
    private function getMonthNumber($month)
    {
        $months = [
            'january' => 1, 'february' => 2, 'march' => 3,
            'april' => 4, 'may' => 5, 'june' => 6,
            'july' => 7, 'august' => 8, 'september' => 9,
            'october' => 10, 'november' => 11, 'december' => 12,
        ];

        if (is_numeric($month)) {
            return (int) $month;
        }

        $month = strtolower(trim($month));
        return $months[$month] ?? null;
    }
}
