<?php

namespace App\Http\Controllers\Api\Employee;

use App\Http\Controllers\Controller;
use App\Models\EmployeeLocation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use App\Models\Employee;

class EmployeeLocationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
       
    public function index(Request $request)
    {
        $perPage = $request->per_page ?? 20;

        $cacheKey = 'employee_locations_' . md5(json_encode($request->all()));

        return Cache::remember($cacheKey, now()->addMinutes(5), function () use ($request, $perPage) {

            $query = EmployeeLocation::with([
                'employee:id,name,employee_id',
            ]);

            // Employee Filter
            if ($request->filled('employee_id')) {
                $query->where('employee_id', $request->employee_id);
            }

            // Search by Employee Name / Employee ID
            if ($request->filled('search')) {
                $search = $request->search;

                $query->whereHas('employee', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                    ->orWhere('employee_id', 'like', "%{$search}%");
                });
            }

            // Single Date Filter
            if ($request->filled('date')) {
                $query->whereDate('created_at', $request->date);
            }

            // From Date
            if ($request->filled('from_date')) {
                $query->whereDate('created_at', '>=', $request->from_date);
            }

            // To Date
            if ($request->filled('to_date')) {
                $query->whereDate('created_at', '<=', $request->to_date);
            }

            return $query
                ->latest()
                ->paginate($perPage);
        });
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);

        $location = EmployeeLocation::create($validated);

        return response()->json([
            'message' => 'Location saved successfully',
            'data' => $location
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $location = EmployeeLocation::with('employee')->findOrFail($id);

        return response()->json($location);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $location = EmployeeLocation::findOrFail($id);

        $validated = $request->validate([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);

        $location->update($validated);

        return response()->json([
            'message' => 'Location updated successfully',
            'data' => $location
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $location = EmployeeLocation::findOrFail($id);

        $location->delete();

        return response()->json([
            'message' => 'Location deleted successfully'
        ]);
    }

   

    public function history(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'date' => 'nullable|date',
        ]);

        $date = $request->date ?? today()->toDateString();

        $locations = EmployeeLocation::where('employee_id', $request->employee_id)
            ->whereDate('created_at', $date)
            ->orderBy('created_at', 'asc')
            ->get([
                'id',
                'latitude',
                'longitude',
                'created_at',
            ]);

        return response()->json([
            'status' => true,
            'message' => 'Location history retrieved successfully',
            'data' => $locations,
        ]);
    }
}
