<?php

namespace App\Http\Controllers\Attendance;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\AttendanceRule;
use App\Models\Geofence;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class AttendanceController extends Controller
{
    /**
     * 🟢 Employee Check-In
     */
    public function checkIn(Request $request)
    {
        $request->validate([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'device_id' => 'required|string',
        ]);

        $user = Auth::user();

        // ✅ Step 1: Get geofence for this employee (or default)
        $geofence = Geofence::where('user_id', $user->id)->first();

        if (!$geofence) {
            return response()->json(['success' => false, 'message' => 'Geofence not set for this employee!'], 404);
        }

            // ✅ Step 1.5: Get office time (user-specific or default)
        $attendanceRule = AttendanceRule::where('user_id', $user->id)->first();

        if (!$attendanceRule) {
            // যদি user-specific না পাওয়া যায় তাহলে default (user_id = null) নেওয়া হবে
            $attendanceRule = AttendanceRule::whereNull('user_id')->first();
        }

        if (!$attendanceRule) {
            return response()->json([
                'success' => false,
                'message' => 'Attendance rule not found!',
            ], 404);
        }

            // ✅ Step 2.5: Prevent multiple check-ins per day
        $alreadyCheckedIn = Attendance::where('user_id', $user->id)
            ->whereDate('check_in_time', Carbon::today())
            ->exists();

        if ($alreadyCheckedIn) {
            return response()->json([
                'success' => false,
                'message' => 'You have already checked in today!',
            ], 409);
        }

        // ✅ Step 2: Calculate distance dynamically
        $distance = $this->distance(
            $geofence->latitude,
            $geofence->longitude,
            $request->latitude,
            $request->longitude
        );

        if ($distance > $geofence->radius) {
            return response()->json([
                'success' => false,
                'message' => 'You are outside your allowed area!',
                'distance' => round($distance, 2)
            ], 403);
        }

            // ✅ Step 3: Determine lateness
        $officeInTime = Carbon::parse($attendanceRule->office_in_time, 'Asia/Dhaka');
        $checkInTime = Carbon::now();

        $lateMinutes = 0;
        $lateFormatted = null;

        if ($checkInTime->greaterThan($officeInTime)) {
            $lateMinutes = $officeInTime->diffInMinutes($checkInTime);

            $hours = floor($lateMinutes / 60);
            $minutes = $lateMinutes % 60;
            $lateFormatted = sprintf('%02d hour %02d minit', $hours, $minutes);
        }
        // ✅ Step 3: Create attendance record
        $attendance = Attendance::create([
            'user_id' => $user->id,
            'check_in_time' => $checkInTime,
            'check_in_latitude' => $request->latitude,
            'check_in_longitude' => $request->longitude,
            'device_id' => $request->device_id,
            'status' => 'Checked In',
            'distance_from_office' => $distance,
            'late' => $lateFormatted,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Checked in successfully!',
            'attendance' => $attendance
        ]);
    }

    /**
     * 🔴 Employee Check-Out
     */
    public function checkOut(Request $request)
    {
        $request->validate([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);

        $user = Auth::user();

        // আজকের দিনের চেকইন রেকর্ড খোঁজা
        $attendance = Attendance::where('user_id', $user->id)
            ->whereDate('check_in_time', Carbon::today('Asia/Dhaka')) // আজকের রেকর্ড
            ->latest()
            ->first();

        if (!$attendance) {
            return response()->json(['success' => false, 'message' => 'No check-in record found for today!'], 404);
        }

          // ✅ Checkout সময় (বাংলাদেশ টাইম অনুযায়ী)
        $checkOutTime = Carbon::now('Asia/Dhaka');
        $checkInTime = Carbon::parse($attendance->check_in_time, 'Asia/Dhaka');  
        $workMinutes = $checkInTime->diffInMinutes($checkOutTime);
        $hours = floor($workMinutes / 60);
        $minutes = $workMinutes % 60;
        $workHourFormatted = sprintf('%02d hour %02d minit', $hours, $minutes);

        $attendance->update([
            'check_out_time' => $checkOutTime,
            'check_out_latitude' => $request->latitude,
            'check_out_longitude' => $request->longitude,
            'work_hour' => $workHourFormatted,
            'status' => 'Checked Out',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Checked out successfully!',
            'attendance' => $attendance
        ]);
    }

    /**
     * ⚙️ Calculate distance between two points (Haversine formula)
     */
    private function distance($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371000; // মিটারে
        $latDelta = deg2rad($lat2 - $lat1);
        $lonDelta = deg2rad($lon2 - $lon1);

        $a = sin($latDelta / 2) ** 2 +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($lonDelta / 2) ** 2;
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }
}
