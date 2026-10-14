<?php

namespace App\Http\Controllers\Api\Employee;

use App\Http\Controllers\Controller;
use App\Models\EmployeeLocation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use App\Traits\ScopesCompanyAccess;
use App\Services\Employee\EmployeeLocationService;
use App\Services\Hierarchy\HierarchyAccessService;

class EmployeeLocationController extends Controller
{
    public function __construct(
        private EmployeeLocationService $service,
        private HierarchyAccessService $hierarchyAccessService
    )
    {
    }

    /**
     * Display a listing of the resource.
     */
     use ScopesCompanyAccess;
       
    public function index(Request $request)
    {
        if (!auth()->user()->can('employee-location.view')) {
            abort(403, 'You do not have permission to employee location view .');
        }
        $perPage = $request->per_page ?? 20;

        $user = auth()->user();
        $cacheKey = 'employee_locations_' . $user->id . '_' . md5(json_encode($request->all()));

        return Cache::remember($cacheKey, now()->addMinutes(5), function () use ($request, $perPage) {

            $query = EmployeeLocation::with([
                'user:id,name,employee_id,company_id',
                'user.employee:id,name,employee_id',
            ]);
            $this->scopeToCurrentCompany($query);
            $this->applyHierarchyScope($query);

            if ($request->filled('user_id')) {
                $query->where('user_id', $request->user_id);
            }

            // Search by Employee Name / Employee ID
            if ($request->filled('search')) {
                $search = $request->search;

                $query->whereHas('user', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhereHas('employee', function ($employeeQuery) use ($search) {
                            $employeeQuery->where('name', 'like', "%{$search}%")
                                ->orWhere('employee_id', 'like', "%{$search}%");
                        });
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
        if (!auth()->user()->can('employee-location.create')) {
            abort(403, 'You do not have permission to employee location create .');
        }
        $validated = $request->validate([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'activity_type' => 'nullable|string|max:255',
            'transport_mode' => 'nullable|string|max:255',
            'speed' => 'nullable|numeric|min:0',
        ]);

        $location = $this->service->createForUser(auth()->user(), $validated);

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
        if (!auth()->user()->can('employee-location.edit')) {
            abort(403, 'You do not have permission to employee location edit .');
        }        
        $query = EmployeeLocation::with(['user.employee']);
        $this->scopeToCurrentCompany($query);
        $this->applyHierarchyScope($query);
        $location = $query->findOrFail($id);

        return response()->json($location);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        if (!auth()->user()->can('employee-location.edit')) {
            abort(403, 'You do not have permission to employee location edit .');
        }        
        $query = EmployeeLocation::query();
        $this->scopeToCurrentCompany($query);
        $this->applyHierarchyScope($query);
        $location = $query->findOrFail($id);

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
        if (!auth()->user()->can('employee-location.delete')) {
            abort(403, 'You do not have permission to delete employee locations.');
        }

        $query = EmployeeLocation::query();
        $this->scopeToCurrentCompany($query);
        $this->applyHierarchyScope($query);
        $location = $query->findOrFail($id);

        $location->delete();

        return response()->json([
            'message' => 'Location deleted successfully'
        ]);
    }

   

    public function history(Request $request)
    {
        if (!auth()->user()->can('employee-location.view')) {
            abort(403, 'You do not have permission to employee location view .');
        }        
        $request->validate([
            'user_id' => 'sometimes|exists:users,id',
            'date' => 'nullable|date',
        ]);

        $date = $request->date ?? today()->toDateString();

        $query = EmployeeLocation::query();
        $this->scopeToCurrentCompany($query);
        $this->applyHierarchyScope($query);

        $query->where('user_id', $request->input('user_id', auth()->id()));
        $locations = $query
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

    private function applyHierarchyScope($query): void
    {
        $query->whereHas('user', function ($userQuery) {
            $this->hierarchyAccessService->applyUserHierarchyAccess($userQuery);
        });
    }
}
