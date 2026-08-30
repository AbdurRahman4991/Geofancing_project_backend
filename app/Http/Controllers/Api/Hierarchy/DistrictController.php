<?php

namespace App\Http\Controllers\Api\Hierarchy;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\District;
class DistrictController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return response()->json([
            'status' => 200,
            'data' => District::with('division')->latest()->get()
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'division_id' => 'required|exists:divisions,id',
            'name' => 'required|string|max:150',
            'status' => 'boolean',
        ]);

        $district = District::create($validated);

        return response()->json([
            'status' => 201,
            'message' => 'District created successfully',
            'data' => $district
        ], 201);
    }

    public function show($id)
    {
        return response()->json([
            'status' => 200,
            'data' => District::with([
                'division',
                'subDistricts'
            ])->findOrFail($id)
        ]);
    }

    public function update(Request $request, $id)
    {
        $district = District::findOrFail($id);

        $validated = $request->validate([
            'division_id' => 'required|exists:divisions,id',
            'name' => 'required|string|max:150',
            'status' => 'boolean',
        ]);

        $district->update($validated);

        return response()->json([
            'status' => 200,
            'message' => 'District updated successfully',
            'data' => $district
        ]);
    }

    public function destroy($id)
    {
        District::findOrFail($id)->delete();

        return response()->json([
            'status' => 200,
            'message' => 'District deleted successfully'
        ]);
    }
}
