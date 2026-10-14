<?php

namespace App\Services\Attendance;

use App\Models\Attendance;
use App\Models\AttendanceRule;
use App\Models\Geofence;
use App\Models\Employee;
use App\Models\EmployeeHierarchyAssignment;
use App\Models\HierarchyNode;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class AttendanceService
{
    
    public function checkIn($data)
    {
        if (!auth()->user()->can('checkin.create')) {
            abort(403, 'You do not have permission to create checkin.');
        } 
        $user = Auth::user();

        $assignment = EmployeeHierarchyAssignment::where('user_id', $user->id)
            ->where('is_current', true)
            ->first();

        if (!$assignment) {
            return [
                'success' => false,
                'message' => 'No active hierarchy assignment found for this user.'
            ];
        }

        $geofences = $this->geofencesForAssignment($assignment, $user);

        if ($geofences->isEmpty()) {
            return [
                'success' => false,
                'message' => 'No geofence is configured for this hierarchy assignment.'
            ];
        }

        // =============================================
        // Get attendance rule
        // =============================================

        // Prefer a rule assigned directly to this user.
        $attendanceRule = AttendanceRule::where('user_id', $user->id)->first();

        if (!$attendanceRule) {
            $employee = $user->employee ?? Employee::find($user->employee_id);
            $companyId = $user->company_id ?? $employee?->company_id;

            if ($companyId) {
                $attendanceRule = AttendanceRule::where('company_id', $companyId)
                    ->whereNull('user_id')
                    ->first();
            }
        }

        if (!$attendanceRule) {
            return [
                'success' => false,
                'message' => 'Attendance rule not found!'
            ];
        }

        // =============================================
        // Find matched geofence
        // =============================================

        $matchedGeofence = null;
        $distance = 0;

        foreach ($geofences as $geofence) {

            $distance = $this->calculateDistance(
                $geofence->latitude,
                $geofence->longitude,
                $data['latitude'],
                $data['longitude']
            );

            if ($distance <= $geofence->radius) {
                $matchedGeofence = $geofence;
                break;
            }
        }

        if (!$matchedGeofence) {
            return [
                'success' => false,
                'message' => 'You are outside all allowed office areas!'
            ];
        }

        // =============================================
        // Prevent duplicate check-in
        // =============================================

        $alreadyCheckedIn = Attendance::where('user_id', $user->id)
            ->where('geofence_id', $matchedGeofence->id)
            ->whereDate('check_in_time', Carbon::today())
            ->exists();

        if ($alreadyCheckedIn) {
            return [
                'success' => false,
                'message' => 'You have already checked in at this location today!'
            ];
        }

        // =============================================
        // Late calculation
        // =============================================

        $officeInTime = Carbon::parse(
            $attendanceRule->office_in_time,
            'Asia/Dhaka'
        );

        $checkInTime = Carbon::now('Asia/Dhaka');

        $lateFormatted = null;

        if ($checkInTime->greaterThan($officeInTime)) {

            $lateMinutes = $officeInTime->diffInMinutes($checkInTime);

            $hours = floor($lateMinutes / 60);
            $minutes = $lateMinutes % 60;

            $lateFormatted = sprintf(
                '%02d hour %02d minute',
                $hours,
                $minutes
            );
        }

        // =============================================
        // Save attendance
        // =============================================

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'geofence_id' => $matchedGeofence->id,
            'check_in_time' => $checkInTime,
            'check_in_latitude' => $data['latitude'],
            'check_in_longitude' => $data['longitude'],
            'device_id' => $data['device_id'],
            'status' => 'Checked In',
            'distance_from_office' => $distance,
            'late' => $lateFormatted,
        ]);

        return [
            'success' => true,
            'message' => 'Checked in successfully!',
            'attendance' => $attendance
        ];
    }

    /**
     * 🔴 Handle Employee Check-Out
     */
    
    public function checkOut($data)
    {
        if (!auth()->user()->can('checkout.create')) {
            abort(403, 'You do not have permission to create checkout.');
        }         

         $user = Auth::user();

        $assignment = EmployeeHierarchyAssignment::where('user_id', $user->id)
            ->where('is_current', true)
            ->first();

        if (!$assignment) {
            return [
                'success' => false,
                'message' => 'No active hierarchy assignment found for this user.'
            ];
        }

        $geofences = $this->geofencesForAssignment($assignment, $user);

        if ($geofences->isEmpty()) {
            return [
                'success' => false,
                'message' => 'No geofence is configured for this hierarchy assignment.'
            ];
        }


        // ==========================================
        // Step 2: Find matched geofence
        // ==========================================
        $matchedGeofence = null;
        $distance = 0;

        foreach ($geofences as $geofence) {

            $distance = $this->calculateDistance(
                $geofence->latitude,
                $geofence->longitude,
                $data['latitude'],
                $data['longitude']
            );

            if ($distance <= $geofence->radius) {
                $matchedGeofence = $geofence;
                break;
            }
        }

        if (!$matchedGeofence) {
            return [
                'success' => false,
                'message' => 'You are outside all allowed office areas!'
            ];
        }

        // ==========================================
        // Step 3: Find today's attendance for
        // this geofence only
        // ==========================================
        $attendance = Attendance::where('user_id', $user->id)
            ->where('geofence_id', $matchedGeofence->id)
            ->whereDate('check_in_time', Carbon::today('Asia/Dhaka'))
            ->whereNull('check_out_time')
            ->latest()
            ->first();

        if (!$attendance) {
            return [
                'success' => false,
                'message' => 'No active check-in found for this office!'
            ];
        }

        // ==========================================
        // Step 4: Calculate work hour
        // ==========================================
        $checkOutTime = Carbon::now('Asia/Dhaka');

        $checkInTime = Carbon::parse(
            $attendance->check_in_time,
            'Asia/Dhaka'
        );

        $workMinutes = $checkInTime->diffInMinutes($checkOutTime);

        $hours = floor($workMinutes / 60);
        $minutes = $workMinutes % 60;

        $workHourFormatted = sprintf(
            '%02d hour %02d minute',
            $hours,
            $minutes
        );

        // ==========================================
        // Step 5: Update attendance
        // ==========================================
        $attendance->update([
            'check_out_time' => $checkOutTime,
            'check_out_latitude' => $data['latitude'],
            'check_out_longitude' => $data['longitude'],
            'work_hour' => $workHourFormatted,
            'status' => 'Checked Out',
        ]);

        return [
            'success' => true,
            'message' => 'Checked out successfully!',
            'attendance' => $attendance
        ];
    }

    /**
     * ⚙️ Haversine distance formula
     */
    /** Resolve geofences from either a node assignment or a legacy area assignment. */
    private function geofencesForAssignment(EmployeeHierarchyAssignment $assignment, $user)
    {
        if ($assignment->hierarchy_node_id) {
            $node = HierarchyNode::find($assignment->hierarchy_node_id);
            if (!$node) {
                return collect();
            }

            $nodeIds = [(int) $node->id];
            $frontier = $nodeIds;
            while ($frontier) {
                $children = HierarchyNode::forCompany((int) $node->company_id)
                    ->whereIn('parent_id', $frontier)
                    ->pluck('id')
                    ->map(fn ($id) => (int) $id)
                    ->all();
                $frontier = array_values(array_diff($children, $nodeIds));
                $nodeIds = array_merge($nodeIds, $frontier);
            }

            return Geofence::query()
                ->where('company_id', $node->company_id)
                ->whereIn('hierarchy_node_id', $nodeIds)
                ->get();
        }

        if (!$assignment->area_id) {
            return collect();
        }

        $companyId = $user->company_id ?? $user->employee?->company_id;
        if (!$companyId) {
            return collect();
        }

        return Geofence::query()
            ->where('company_id', $companyId)
            ->where('area_id', $assignment->area_id)
            ->get();
    }

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
        if (!auth()->user()->can('attendance.view')) {
            abort(403, 'You do not have permission to attendance history.');
        }         
        $user = Auth::user();
        $query = Attendance::query();

        if ($user->hasRole('Super-Admin')) {
            // All companies. Optional user filter and search are allowed.
            if ($request->filled('user_id')) {
                $query->where('user_id', $request->user_id);
            }

            if ($request->filled('search')) {
                $search = $request->search;

                $query->whereHas('user', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('employee_id', 'like', "%{$search}%");
                });
            }
        } elseif ($user->hasRole('Company Admin')) {
            $companyId = $user->employee?->company_id;

            if (!$companyId) {
                $query->whereRaw('1 = 0');
            } else {
                $query->whereHas('user.employee', function ($q) use ($companyId) {
                    $q->where('company_id', $companyId);
                });
            }
        } elseif ($user->hasRole('territory-manager')) {
            $territoryId = $user->hierarchyAssignment?->territory_id;

            if (!$territoryId) {
                $query->whereRaw('1 = 0');
            } else {
                $query->whereHas('user.hierarchyAssignment', function ($q) use ($territoryId) {
                    $q->where('territory_id', $territoryId);
                });
            }
        } elseif ($user->hasRole('area-manager')) {
            $areaId = $user->hierarchyAssignment?->area_id;

            if (!$areaId) {
                $query->whereRaw('1 = 0');
            } else {
                $query->whereHas('user.hierarchyAssignment', function ($q) use ($areaId) {
                    $q->where('area_id', $areaId);
                });
            }
        } else {
            // Employees and any unrecognized roles see only their own attendance.
            $query->where('user_id', $user->id);
        }

        // Year Filter
        if ($request->filled('year')) {
            $query->whereYear('created_at', $request->year);
        }

        // Month Filter
        if ($request->filled('month')) {
            $month = $this->getMonthNumber($request->month);

            if ($month) {
                $query->whereMonth('created_at', $month);
            }
        }

        // Late Filter
        if ($request->filled('late') && $request->late == 'yes') {
            $query->whereNotNull('late');
        }

        // Status Filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $attendance = $query
            ->with(['user:id,name,employee_id', 'geofence:id,firm_name'])
            ->latest()
            ->paginate($request->per_page ?? 20);

        return response()->json([
            'status' => 200,
            'data' => $attendance,
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
