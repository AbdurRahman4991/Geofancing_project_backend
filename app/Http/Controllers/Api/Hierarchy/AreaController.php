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
    public function index(Request $request)
    {
        if (!auth()->user()->can('area.view')) {
        abort(403, 'You do not have permission to area view.');
        }         
        $query = Area::with('territory');

        // Filter by territory_id
        if ($request->filled('territory_id')) {
            $query->where('territory_id', $request->territory_id);
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%") ;               
            });
        }

        // Pagination
        $areas = $query
            ->latest()
            ->paginate($request->get('per_page', 10))
            ->withQueryString();

        return response()->json([
            'status' => 200,
            'data' => $areas
        ]);
    }

    public function store(Request $request)
    {
        if (!auth()->user()->can('area.create')) {
        abort(403, 'You do not have permission to area create.');
        }         
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
        if (!auth()->user()->can('area.edit')) {
        abort(403, 'You do not have permission to area edit.');
        }         
        return response()->json([
            'status' => 200,
            'data' => Area::with('territory')->findOrFail($id)
        ]);
    }

    public function update(Request $request, $id)
    {
        if (!auth()->user()->can('area.edit')) {
        abort(403, 'You do not have permission to area edit.');
        }         
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
        if (!auth()->user()->can('area.delete')) {
        abort(403, 'You do not have permission to area delete.');
        }         
        Area::findOrFail($id)->delete();

        return response()->json([
            'status' => 200,
            'message' => 'Area deleted successfully'
        ]);
    }
}
