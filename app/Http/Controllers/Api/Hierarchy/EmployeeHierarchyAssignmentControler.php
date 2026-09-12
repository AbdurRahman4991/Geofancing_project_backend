<?php

namespace App\Http\Controllers\Api\Hierarchy;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\EmployeeHierarchyAssignment;
use Illuminate\Support\Facades\DB;

class EmployeeHierarchyAssignmentControler extends Controller
{
    public function transfer(Request $request, $userId)
    {
        $request->validate([
            'country_id'      => 'nullable|exists:countries,id',
            'region_id'       => 'nullable|exists:regions,id',
            'zone_id'         => 'nullable|exists:zones,id',
            'division_id'     => 'nullable|exists:divisions,id',
            'district_id'     => 'nullable|exists:districts,id',
            'sub_district_id' => 'nullable|exists:sub_districts,id',
            'territory_id'    => 'nullable|exists:territories,id',
            'area_id'         => 'nullable|exists:areas,id',
            'effective_from'  => 'required|date',
            'reason'          => 'nullable|string',
        ]);

        DB::transaction(function () use ($request, $userId) {

            EmployeeHierarchyAssignment::where(
                'user_id',
                $userId
            )
            ->where('is_current', true)
            ->update([
                'is_current'  => false,
                'effective_to' => $request->effective_from,
            ]);

            EmployeeHierarchyAssignment::create([
                'user_id'          => $userId,
                'country_id'       => $request->country_id,
                'region_id'        => $request->region_id,
                'zone_id'          => $request->zone_id,
                'division_id'      => $request->division_id,
                'district_id'      => $request->district_id,
                'sub_district_id'  => $request->sub_district_id,
                'territory_id'     => $request->territory_id,
                'area_id'          => $request->area_id,
                'effective_from'   => $request->effective_from,
                'is_current'       => true,
                'assigned_by'      => auth()->id(),
                'reason'           => $request->reason,
            ]);
        });

        return response()->json([
            'status' => 200,
            'message' => 'Employee transferred successfully.',
        ]);
    }
}
