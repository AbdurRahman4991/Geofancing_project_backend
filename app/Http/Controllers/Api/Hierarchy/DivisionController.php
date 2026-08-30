<?php

namespace App\Http\Controllers\Api\Hierarchy;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Division;

class DivisionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return response()->json([
            'status' => 200,
            'data' => Division::with('zone')->latest()->get()
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'zone_id' => 'required|exists:zones,id',
            'name' => 'required|string|max:150',
            'status' => 'boolean',
        ]);

        $division = Division::create($validated);

        return response()->json([
            'status' => 201,
            'message' => 'Division created successfully',
            'data' => $division
        ], 201);
    }

    public function show($id)
    {
        return response()->json([
            'status' => 200,
            'data' => Division::with([
                'zone',
                'districts'
            ])->findOrFail($id)
        ]);
    }

    public function update(Request $request, $id)
    {
        $division = Division::findOrFail($id);

        $validated = $request->validate([
            'zone_id' => 'required|exists:zones,id',
            'name' => 'required|string|max:150',
            'status' => 'boolean',
        ]);

        $division->update($validated);

        return response()->json([
            'status' => 200,
            'message' => 'Division updated successfully',
            'data' => $division
        ]);
    }

    public function destroy($id)
    {
        Division::findOrFail($id)->delete();

        return response()->json([
            'status' => 200,
            'message' => 'Division deleted successfully'
        ]);
    }
}
