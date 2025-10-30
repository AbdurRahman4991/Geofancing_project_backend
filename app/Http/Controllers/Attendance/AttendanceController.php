<?php

namespace App\Http\Controllers\Attendance;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
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

        // ✅ Step 3: Create attendance record
        $attendance = Attendance::create([
            'user_id' => $user->id,
            'check_in_time' => Carbon::now(),
            'check_in_latitude' => $request->latitude,
            'check_in_longitude' => $request->longitude,
            'device_id' => $request->device_id,
            'status' => 'Checked In',
            'distance_from_office' => $distance,
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

        $attendance = Attendance::where('employee_id', $user->id)
            ->whereNull('check_out_time')
            ->latest()
            ->first();

        if (!$attendance) {
            return response()->json(['success' => false, 'message' => 'No active check-in found!'], 404);
        }

        $attendance->update([
            'check_out_time' => Carbon::now(),
            'check_out_latitude' => $request->latitude,
            'check_out_longitude' => $request->longitude,
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
