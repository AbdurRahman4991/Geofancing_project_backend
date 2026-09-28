<?php

namespace App\Http\Controllers\Api\Hierarchy;
use App\Http\Controllers\Controller;
use App\Models\Region;
use Illuminate\Http\Request;


class RegionController extends Controller
{
    public function index()
    {
        if (!auth()->user()->can('region.view')) {
        abort(403, 'You do not have permission to region view.');
        }        
        $regions = Region::with('country')->latest()->get();

        return response()->json([
            'status' => 200,
            'data' => $regions
        ]);
    }

    public function store(Request $request)
    {
        if (!auth()->user()->can('region.create')) {
        abort(403, 'You do not have permission to region create.');
        }         
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
        if (!auth()->user()->can('region.edit')) {
        abort(403, 'You do not have permission to region edit.');
        }         
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
        if (!auth()->user()->can('region.edit')) {
        abort(403, 'You do not have permission to region edit.');
        }         
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
        if (!auth()->user()->can('region.delete')) {
        abort(403, 'You do not have permission to region delete.');
        }         
        $region = Region::findOrFail($id);

        $region->delete();

        return response()->json([
            'status' => 200,
            'message' => 'Region deleted successfully'
        ]);
    }
}