<?php

namespace App\Http\Controllers\Api\Geofence;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Geofence\GeofeneService;
use App\Services\Hierarchy\HierarchyAccessService;
use App\Models\Geofence;
use App\Traits\CompanyScoped;

class GeofenceController extends Controller
{
    protected $service;
    protected $hierarchyAccessService;

    public function __construct(GeofeneService $service, HierarchyAccessService $hierarchyAccessService)
    {
        $this->service = $service;
        $this->hierarchyAccessService = $hierarchyAccessService;
        
    }
    use CompanyScoped;
    // public function index(Request $request)
    // {
    //     return $this->service->index($request);
    // }
    public function index(Request $request)
{
    $query = Geofence::with([
        'company:id,company_name',
        'area:id,name,territory_id',
    ]);

    // Hierarchy access
    $this->hierarchyAccessService
        ->applyGeofenceAccess($query);

    if ($request->filled('search')) {
        $search = $request->search;

        $query->where(function ($q) use ($search) {
            $q->where('firm_name', 'like', "%{$search}%")
                ->orWhereHas('area', function ($areaQuery) use ($search) {
                    $areaQuery->where('name', 'like', "%{$search}%");
                });
        });
    }

    if ($request->filled('area_id')) {
        $query->where('area_id', $request->area_id);
    }

    return response()->json([
        'status' => 200,
        'message' => 'Geofence list retrieved successfully',
        'data' => $query
            ->latest()
            ->paginate($request->per_page ?? 10),
    ]);
}




    public function store(Request $request)
    {
        $request->validate([
            'company_id' => 'required|integer',
            'area_id' => 'required|integer',
            'firm_name' => 'required|string',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'radius' => 'required|integer|min:1',
        ]);

        $geofence = $this->service->store($request);
        return response()->json(['message' => 'Location created successfully', 'data' => $geofence]);
    }

    public function show($id)
    {
        $geofence = $this->service->show($id);
        return response()->json($geofence);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'company_id' => 'sometimes|integer|max:255',
            'area_id' => 'sometimes|integer',
            'firm_name' => 'required|string',
            'latitude' => 'sometimes|numeric',
            'longitude' => 'sometimes|numeric',
            'radius' => 'sometimes|integer|min:1',
        ]);

        $geofence = $this->service->update($request, $id);
        return response()->json(['message' => 'Location updated successfully', 'data' => $geofence]);
    }

    public function destroy($id)
    {
        $this->service->destroy($id);
        return response()->json(['message' => 'Location deleted successfully']);
    }
}
