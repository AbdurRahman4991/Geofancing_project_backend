<?php

namespace App\Http\Controllers\Api\Hierarchy;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SubDistrict;

class SubDistrictController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return response()->json([
            'status' => 200,
            'data' => SubDistrict::with('district')->latest()->get()
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'district_id' => 'required|exists:districts,id',
            'name' => 'required|string|max:150',
            'status' => 'boolean',
        ]);

        $subDistrict = SubDistrict::create($validated);

        return response()->json([
            'status' => 201,
            'message' => 'Sub District created successfully',
            'data' => $subDistrict
        ], 201);
    }

    public function show($id)
    {
        return response()->json([
            'status' => 200,
            'data' => SubDistrict::with([
                'district',
                'territories'
            ])->findOrFail($id)
        ]);
    }

    public function update(Request $request, $id)
    {
        $subDistrict = SubDistrict::findOrFail($id);

        $validated = $request->validate([
            'district_id' => 'required|exists:districts,id',
            'name' => 'required|string|max:150',
            'status' => 'boolean',
        ]);

        $subDistrict->update($validated);

        return response()->json([
            'status' => 200,
            'message' => 'Sub District updated successfully',
            'data' => $subDistrict
        ]);
    }

    public function destroy($id)
    {
        SubDistrict::findOrFail($id)->delete();

        return response()->json([
            'status' => 200,
            'message' => 'Sub District deleted successfully'
        ]);
    }
}
