<?php

namespace App\Http\Controllers\Api\Hierarchy;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Area;

class AreaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return response()->json([
            'status' => 200,
            'data' => Area::with('territory')->latest()->get()
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'territory_id' => 'required|exists:territories,id',
            'name' => 'required|string|max:150',
            'status' => 'boolean',
        ]);

        $area = Area::create($validated);

        return response()->json([
            'status' => 201,
            'message' => 'Area created successfully',
            'data' => $area
        ], 201);
    }

    public function show($id)
    {
        return response()->json([
            'status' => 200,
            'data' => Area::with('territory')->findOrFail($id)
        ]);
    }

    public function update(Request $request, $id)
    {
        $area = Area::findOrFail($id);

        $validated = $request->validate([
            'territory_id' => 'required|exists:territories,id',
            'name' => 'required|string|max:150',
            'status' => 'boolean',
        ]);

        $area->update($validated);

        return response()->json([
            'status' => 200,
            'message' => 'Area updated successfully',
            'data' => $area
        ]);
    }

    public function destroy($id)
    {
        Area::findOrFail($id)->delete();

        return response()->json([
            'status' => 200,
            'message' => 'Area deleted successfully'
        ]);
    }
}
