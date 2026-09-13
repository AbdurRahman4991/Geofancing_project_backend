<?php

namespace App\Http\Controllers\Api\Hierarchy;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\EmployeeHierarchyAssignment;
use Illuminate\Support\Facades\DB;

class EmployeeHierarchyAssignmentControler extends Controller
{
    public function assignUser(Request $request)
    {
        EmployeeHierarchyAssignment::updateOrCreate(
    [
        'user_id' => $request->user_id,
    ],
    [
        'country_id'      => $request->country_id,
        'region_id'       => $request->region_id,
        'zone_id'         => $request->zone_id,
        'division_id'     => $request->division_id,
        'district_id'     => $request->district_id,
        'sub_district_id' => $request->sub_district_id,
        'territory_id'    => $request->territory_id,
        'area_id'         => $request->area_id,
        'effective_from'  => $request->effective_from,
        'is_current'      => true,
        'assigned_by'     => auth()->id(),
        'reason'          => $request->reason,
    ]
    );
        return response()->json([
        'status'  => 201,
        'message' => 'Employee transferred successfully.',
    ]);

    }

}
