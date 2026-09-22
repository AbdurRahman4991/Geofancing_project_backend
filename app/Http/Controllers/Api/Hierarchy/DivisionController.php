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
public function index(Request $request)
{
    $divisions = Division::with('zone')

        // ==============================
        // Filter by Zone
        // ==============================
        ->when(
            $request->filled('zone_id'),
            function ($query) use ($request) {
                $query->where(
                    'zone_id',
                    $request->zone_id
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
        'data' => $divisions
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
