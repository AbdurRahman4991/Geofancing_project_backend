<?php

namespace App\Services\Geofence;

use App\Models\Geofence;
use Illuminate\Http\Request;
use App\Services\Hierarchy\HierarchyAccessService;

class GeofeneService
{
    public function __construct(private HierarchyAccessService $hierarchyAccessService)
    {
    }

   
    public function index(Request $request)
    {
        if (!auth()->user()->can('market.view')) {
            abort(403, 'You do not have permission to view geofences.');
        }

        $query = Geofence::with([
            'company:id,company_name',
            'area:id,name,territory_id',
        ]);
        $this->hierarchyAccessService->applyGeofenceAccess($query);

        if ($request->filled('area_id')) {
            $query->where('area_id', $request->integer('area_id'));
        }

        if ($request->filled('search')) {
            $search = $request->string('search')->toString();
            $query->where(function ($q) use ($search) {
                $q->where('firm_name', 'like', "%{$search}%")
                    ->orWhereHas('area', function ($areaQuery) use ($search) {
                        $areaQuery->where('name', 'like', "%{$search}%");
                    });
            });
        }

        return response()->json([
            'status' => 200,
            'message' => 'Geofence list retrieved successfully',
            'data' => $query->latest()->paginate($request->integer('per_page', 10)),
        ]);
    }



    public function store(Request $request)
    {
        if (!auth()->user()->can('market.create')) {
            abort(403, 'You do not have permission to view employees.');
        }        
        $geofence = Geofence::create($request->only([
            'company_id',
            'area_id',
            'firm_name',
            'latitude',
            'longitude',
            'radius',
        ]));

        if ($request->hasFile('image')) {
            $geofence->uploadImage($request->file('image'));
        }

        return $geofence->load([
            'company:id,company_name',
            'user:id,name',
        ]);
    }

    public function show($id)
    {
        if (!auth()->user()->can('market.view')) {
            abort(403, 'You do not have permission to view employees.');
        }        
        return $this->findAccessibleGeofence($id)->load(['company:id,company_name', 'user:id,name']);
    }

    public function update(Request $request, $id)
    {
        if (!auth()->user()->can('market.edit')) {
            abort(403, 'You do not have permission to view employees.');
        }        
        $geofence = $this->findAccessibleGeofence($id);

        $geofence->update($request->only([
            'company_id',
            'area_id',
            'firm_name',
            'latitude',
            'longitude',
            'radius',
        ]));
        
        if ($request->hasFile('image')) {
            $geofence->uploadImage($request->file('image'));
        }

        return $geofence->fresh([
            'company:id,company_name',
            'user:id,name',
        ]);
    }

    public function destroy($id)
    {
        if (!auth()->user()->can('market.delete')) {
            abort(403, 'You do not have permission to delete geofences.');
        }

        $geofence = $this->findAccessibleGeofence($id);
        return $geofence->delete();
    }

    private function findAccessibleGeofence(int|string $id): Geofence
    {
        $query = Geofence::query()->whereKey($id);
        $this->hierarchyAccessService->applyGeofenceAccess($query);

        return $query->firstOrFail();
    }
}
