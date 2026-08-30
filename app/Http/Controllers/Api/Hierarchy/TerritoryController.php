<?php

namespace App\Http\Controllers\Api\Hierarchy;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Territory;

class TerritoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return response()->json([
            'status' => 200,
            'data' => Territory::with('subDistrict')->latest()->get()
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'sub_district_id' => 'required|exists:sub_districts,id',
            'name' => 'required|string|max:150',
            'status' => 'boolean',
        ]);

        $territory = Territory::create($validated);

        return response()->json([
            'status' => 201,
            'message' => 'Territory created successfully',
            'data' => $territory
        ], 201);
    }

    public function show($id)
    {
        return response()->json([
            'status' => 200,
            'data' => Territory::with([
                'subDistrict',
                'areas'
            ])->findOrFail($id)
        ]);
    }

    public function update(Request $request, $id)
    {
        $territory = Territory::findOrFail($id);

        $validated = $request->validate([
            'sub_district_id' => 'required|exists:sub_districts,id',
            'name' => 'required|string|max:150',
            'status' => 'boolean',
        ]);

        $territory->update($validated);

        return response()->json([
            'status' => 200,
            'message' => 'Territory updated successfully',
            'data' => $territory
        ]);
    }

    public function destroy($id)
    {
        Territory::findOrFail($id)->delete();

        return response()->json([
            'status' => 200,
            'message' => 'Territory deleted successfully'
        ]);
    }
}
