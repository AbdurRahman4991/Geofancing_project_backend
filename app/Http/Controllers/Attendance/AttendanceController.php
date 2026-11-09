<?php

namespace App\Http\Controllers\Attendance;

use App\Http\Controllers\Controller;
use App\Services\Attendance\AttendanceService;
use Illuminate\Http\Request;


class AttendanceController extends Controller
{
    protected $attendanceService;

    public function __construct(AttendanceService $attendanceService)
    {
        $this->attendanceService = $attendanceService;
    }

    public function checkIn(Request $request)
    {
        $request->validate([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'device_id' => 'required|string',
        ]);

        $response = $this->attendanceService->checkIn($request->all());
        return response()->json($response, $response['success'] ? 200 : 400);
    }

    public function checkOut(Request $request)
    {
        $request->validate([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);

        $response = $this->attendanceService->checkOut($request->all());
        return response()->json($response, $response['success'] ? 200 : 400);
    }

     public function update(Request $request, $attendanceId)
    {
        $validated = $request->validate([
            'status' => 'sometimes|required|string|in:Checked In,Checked Out,Auto Checked Out,Outside Area,Absent,Weekend Holyday,Goverment Holyday,Leave',
            'remarks' => 'sometimes|nullable|string',
        ]);

        if (empty($validated)) {
            return response()->json([
                'success' => false,
                'message' => 'Provide status or remarks to update.',
            ], 422);
        }

        $response = $this->attendanceService->updateAttendance($attendanceId, $validated);
        return response()->json($response, $response['success'] ? 200 : 400);
    }

    public function history(Request $request)
    {
        
        return $this->attendanceService->attendanceHistory($request);
        return response()->json($response, $response['success'] ? 200 : 400);

    }
}

