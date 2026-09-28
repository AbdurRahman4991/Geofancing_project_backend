<?php

namespace App\Http\Controllers\Api\Hierarchy;
use App\Http\Controllers\Controller;
use App\Models\Zone;
use Illuminate\Http\Request;

class ZoneController extends Controller
{
public function index(Request $request)
{
    if (!auth()->user()->can('zone.view')) {
    abort(403, 'You do not have permission to zone view.');
    }     
    $zones = Zone::with('region')

        // ==============================
        // Filter by Region
        // ==============================
        ->when(
            $request->filled('region_id'),
            function ($query) use ($request) {
                $query->where(
                    'region_id',
                    $request->region_id
                );
            }
        )

        // ==============================
        // Search
        // ==============================
        ->when(
            $request->filled('search'),
            function ($query) use ($request) {

                $search = $request->search;

                $query->where(function ($q) use ($search) {

                    $q->where(
                        'name',
                        'like',
                        "%{$search}%"
                    );
                });
            }
        )
        ->latest()
        ->paginate(
            $request->get('per_page', 10)
        )

        ->withQueryString();

    return response()->json([
        'status' => 200,
        'data' => $zones
    ]);
}

    public function store(Request $request)
    {
        if (!auth()->user()->can('zone.create')) {
        abort(403, 'You do not have permission to zone create.');
        }         
        $validated = $request->validate([
            'region_id' => 'required|exists:regions,id',
            'name' => 'required|string|max:150',
            'status' => 'boolean',
        ]);

        $zone = Zone::create($validated);

        return response()->json([
            'status' => 201,
            'message' => 'Zone created successfully',
            'data' => $zone
        ], 201);
    }

    public function show($id)
    {
        if (!auth()->user()->can('zone.edit')) {
        abort(403, 'You do not have permission to zone edit.');
        }         
        $zone = Zone::with([
            'region',
            'divisions'
        ])->findOrFail($id);

        return response()->json([
            'status' => 200,
            'data' => $zone
        ]);
    }

    public function update(Request $request, $id)
    {
        if (!auth()->user()->can('zone.edit')) {
        abort(403, 'You do not have permission to zone edit.');
        }         
        $zone = Zone::findOrFail($id);

        $validated = $request->validate([
            'region_id' => 'required|exists:regions,id',
            'name' => 'required|string|max:150',
            'status' => 'boolean',
        ]);

        $zone->update($validated);

        return response()->json([
            'status' => 200,
            'message' => 'Zone updated successfully',
            'data' => $zone
        ]);
    }

    public function destroy($id)
    {
        if (!auth()->user()->can('zone.delete')) {
        abort(403, 'You do not have permission to zone delete.');
        }         
        $zone = Zone::findOrFail($id);

        $zone->delete();

        return response()->json([
            'status' => 200,
            'message' => 'Zone deleted successfully'
        ]);
    }
}