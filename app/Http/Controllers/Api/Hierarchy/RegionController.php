<?php

namespace App\Http\Controllers\Api\Hierarchy;
use App\Http\Controllers\Controller;
use App\Models\Region;
use Illuminate\Http\Request;


class RegionController extends Controller
{
    public function index()
    {
        $regions = Region::with('country')->latest()->get();

        return response()->json([
            'status' => 200,
            'data' => $regions
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'country_id' => 'required|exists:countries,id',
            'name' => 'required|string|max:150',
            'status' => 'boolean',
        ]);

        $region = Region::create($validated);

        return response()->json([
            'status' => 201,
            'message' => 'Region created successfully',
            'data' => $region
        ], 201);
    }

    public function show($id)
    {
        $region = Region::with([
            'country',
            'zones'
        ])->findOrFail($id);

        return response()->json([
            'status' => 200,
            'data' => $region
        ]);
    }

    public function update(Request $request, $id)
    {
        $region = Region::findOrFail($id);

        $validated = $request->validate([
            'country_id' => 'required|exists:countries,id',
            'name' => 'required|string|max:150',
            'status' => 'boolean',
        ]);

        $region->update($validated);

        return response()->json([
            'status' => 200,
            'message' => 'Region updated successfully',
            'data' => $region
        ]);
    }

    public function destroy($id)
    {
        $region = Region::findOrFail($id);

        $region->delete();

        return response()->json([
            'status' => 200,
            'message' => 'Region deleted successfully'
        ]);
    }
}