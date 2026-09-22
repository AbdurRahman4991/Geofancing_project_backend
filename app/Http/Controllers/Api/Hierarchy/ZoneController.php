<?php

namespace App\Http\Controllers\Api\Hierarchy;
use App\Http\Controllers\Controller;
use App\Models\Zone;
use Illuminate\Http\Request;

class ZoneController extends Controller
{
public function index(Request $request)
{
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
        $zone = Zone::findOrFail($id);

        $zone->delete();

        return response()->json([
            'status' => 200,
            'message' => 'Zone deleted successfully'
        ]);
    }
}