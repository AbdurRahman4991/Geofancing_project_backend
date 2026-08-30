<?php

namespace App\Http\Controllers\Api\Hierarchy;

use App\Models\Zone;
use Illuminate\Http\Request;

class ZoneController extends Controller
{
    public function index()
    {
        $zones = Zone::with('region')->latest()->get();

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