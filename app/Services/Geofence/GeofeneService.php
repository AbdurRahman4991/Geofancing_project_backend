<?php

namespace App\Services\Geofence;

use App\Models\Geofence;
use App\Models\HierarchyNode;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use App\Services\Hierarchy\HierarchyAccessService;

class GeofeneService
{
    public function __construct(private HierarchyAccessService $hierarchyAccessService)
    {
    }

   
    public function index(Request $request)
    {
        if (!auth()->user()->can('market.view')) {
            abort(403, 'You do not have permission to view Market.');
        }

        $query = Geofence::with([
            'company:id,company_name',
            'area:id,name,territory_id',
            'hierarchyNode:id,name,hierarchy_level_id,parent_id',
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
            abort(403, 'You do not have permission to create market.');
        }        
        $data = $request->validate([
            'hierarchy_node_id' => ['nullable', 'integer', 'exists:hierarchy_nodes,id'],
        ]);
        $companyId = $request->input('company_id')
            ?? $request->user()?->company_id
            ?? $request->user()?->employee?->company_id;
        $this->assertHierarchyNodeCompany($data['hierarchy_node_id'] ?? null, $companyId);

        $geofence = Geofence::create($request->only([
            'company_id',
            'area_id',
            'hierarchy_node_id',
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
            'hierarchyNode:id,name,hierarchy_level_id,parent_id',
        ]);
    }

    public function show($id)
    {
        if (!auth()->user()->can('market.edit')) {
            abort(403, 'You do not have permission to edit market.');
        }        
        return $this->findAccessibleGeofence($id)->load([
            'company:id,company_name',
            'user:id,name',
            'hierarchyNode:id,name,hierarchy_level_id,parent_id',
        ]);
    }

    public function update(Request $request, $id)
    {
        if (!auth()->user()->can('market.edit')) {
            abort(403, 'You do not have permission to edit market.');
        }        
        $geofence = $this->findAccessibleGeofence($id);

        $data = $request->validate([
            'hierarchy_node_id' => ['sometimes', 'nullable', 'integer', 'exists:hierarchy_nodes,id'],
        ]);
        $companyId = $request->input('company_id', $geofence->company_id);
        $nodeId = array_key_exists('hierarchy_node_id', $data)
            ? $data['hierarchy_node_id']
            : $geofence->hierarchy_node_id;
        $this->assertHierarchyNodeCompany($nodeId, $companyId);

        $geofence->update($request->only([
            'company_id',
            'area_id',
            'hierarchy_node_id',
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
            'hierarchyNode:id,name,hierarchy_level_id,parent_id',
        ]);
    }

    public function destroy($id)
    {
        if (!auth()->user()->can('market.delete')) {
            abort(403, 'You do not have permission to delete market.');
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

    private function assertHierarchyNodeCompany(mixed $nodeId, mixed $companyId): void
    {
        if ($nodeId === null || $nodeId === '') {
            return;
        }

        if (!$companyId || !HierarchyNode::forCompany((int) $companyId)->whereKey($nodeId)->exists()) {
            throw ValidationException::withMessages([
                'hierarchy_node_id' => 'The selected hierarchy node does not belong to this company.',
            ]);
        }
    }
}
