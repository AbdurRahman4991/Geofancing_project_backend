<?php

namespace App\Services\Employee;

use App\Models\AttendanceRule;
use App\Models\EmployeeLocation;
use App\Models\User;

class EmployeeLocationService
{
    /**
     * Save a location for the authenticated user when tracking is enabled.
     * A user-specific attendance rule takes precedence over the company rule.
     */
    public function createForUser(User $user, array $data): EmployeeLocation
    {
        $companyId = $user->company_id;

        if (!$companyId) {
            abort(422, 'No company is assigned to this user account.');
        }

        $rule = AttendanceRule::where('user_id', $user->id)->first();

        if (!$rule) {
            $rule = AttendanceRule::where('company_id', $companyId)
                ->whereNull('user_id')
                ->first();
        }

        if (!$rule || !$rule->tracking_enabled) {
            abort(403, 'Location tracking is disabled for this user.');
        }
    
        return EmployeeLocation::create([
            'user_id' => $user->id,
            'company_id' => $companyId,
            'latitude' => $data['latitude'],
            'longitude' => $data['longitude'],
            'activity_type' => $data['activity_type'] ?? null,
            'transport_mode' => $data['transport_mode'] ?? null,
            'speed' => $data['speed'] ?? null,
        ]);
    }
}
