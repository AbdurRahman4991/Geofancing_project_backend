<?php

namespace App\Http\Controllers\Api\Market;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Geofence;
use App\Models\MarketVisit;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MarketVisitController extends Controller
{
    public function CreateMarketVisit(Request $request)
    {
        // 1. Permission Check
        $user = auth()->user();

        if (!$user->can('market-visit.create')) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to track market visits.',
            ], 403);
        }

        // 2. Validate Request
        $data = $request->validate([
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'device_id' => 'nullable|string|max:255',
        ]);

        if (!$user->company_id) {
            return response()->json([
                'success' => false,
                'message' => 'Employee company not found.',
            ], 422);
        }

        $latitude = (float) $data['latitude'];
        $longitude = (float) $data['longitude'];

        $now = Carbon::now();

        return DB::transaction(function () use (
            $user,
            $latitude,
            $longitude,
            $data,
            $now
        ) {

            // =========================================
            // 3. Get Company Geofences
            // =========================================

            $geofences = Geofence::where(
                'company_id',
                $user->company_id
            )->get();

            // =========================================
            // 4. Find Matching Geofence
            // =========================================

            $matchedGeofence = null;
            $nearestDistance = PHP_FLOAT_MAX;

            foreach ($geofences as $geofence) {

                $distance = $this->calculateDistance(
                    (float) $geofence->latitude,
                    (float) $geofence->longitude,
                    $latitude,
                    $longitude
                );

                // Check employee inside geofence radius
                if (
                    $distance <= (float) $geofence->radius &&
                    $distance < $nearestDistance
                ) {
                    $matchedGeofence = $geofence;
                    $nearestDistance = $distance;
                }
            }

            // =========================================
            // 5. Find Employee Active Visit
            // =========================================

            $activeVisit = MarketVisit::where(
                'company_id',
                $user->company_id
            )
                ->where('user_id', $user->id)
                ->whereNull('visit_end_time')
                ->lockForUpdate()
                ->first();

            // =========================================
            // 6. Already Inside Same Customer
            // =========================================

            if (
                $activeVisit &&
                $matchedGeofence &&
                $activeVisit->geofence_id == $matchedGeofence->id
            ) {
                return response()->json([
                    'success' => true,
                    'action' => 'already_checked_in',
                    'message' => 'You are already visiting this customer.',
                    'visit_id' => $activeVisit->id,
                    'geofence_id' => $matchedGeofence->id,
                    'distance' => round($nearestDistance, 2),
                ]);
            }

            // =========================================
            // 7. Checkout Previous Customer
            // =========================================

            if ($activeVisit) {

                $startTime = Carbon::parse(
                    $activeVisit->visit_start_time
                );

                $durationMinutes = round(
                    $startTime->diffInSeconds($now) / 60,
                    2
                );

                $activeVisit->update([
                    'visit_end_time' => $now,

                    'check_out_latitude' => $latitude,

                    'check_out_longitude' => $longitude,

                    'duration' => $durationMinutes,
                ]);
            }

            // =========================================
            // 8. Outside All Customer Geofences
            // =========================================

            if (!$matchedGeofence) {

                return response()->json([
                    'success' => true,

                    'action' => $activeVisit
                        ? 'checked_out'
                        : 'outside_geofence',

                    'message' => $activeVisit
                        ? 'Customer visit checked out successfully.'
                        : 'You are outside all customer areas.',
                ]);
            }

            // =========================================
            // 9. Automatic Check-in New Customer
            // =========================================

            $visit = MarketVisit::create([

                'company_id' => $user->company_id,

                'user_id' => $user->id,

                'geofence_id' => $matchedGeofence->id,

                // Check-in GPS Location
                'check_in_latitude' => $latitude,

                'check_in_longitude' => $longitude,

                // Check-in Time
                'visit_start_time' => $now,

                // Check-out will be updated later
                'check_out_latitude' => null,

                'check_out_longitude' => null,

                'visit_end_time' => null,

                'duration' => null,

                'device_id' => $data['device_id'] ?? null,
            ]);

            return response()->json([
                'success' => true,

                'action' => 'checked_in',

                'message' => 'Customer visit started successfully.',

                'visit' => $visit,

                'customer' => [
                    'id' => $matchedGeofence->id,
                    'name' => $matchedGeofence->firm_name,
                ],

                'distance' => round($nearestDistance, 2),
            ]);
        }, 3);
    }


    /**
     * Calculate distance between two GPS coordinates.
     *
     * Return distance in meters.
     */
    private function calculateDistance(
        float $lat1,
        float $lon1,
        float $lat2,
        float $lon2
    ): float {

        $earthRadius = 6371000;

        $latDelta = deg2rad($lat2 - $lat1);

        $lonDelta = deg2rad($lon2 - $lon1);

        $a = sin($latDelta / 2) ** 2
            + cos(deg2rad($lat1))
            * cos(deg2rad($lat2))
            * sin($lonDelta / 2) ** 2;

        $c = 2 * atan2(
            sqrt($a),
            sqrt(1 - $a)
        );

        return $earthRadius * $c;
    }

    
}
