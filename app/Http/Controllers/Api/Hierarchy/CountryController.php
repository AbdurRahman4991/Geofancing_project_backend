<?php

namespace App\Http\Controllers\Api\Hierarchy;
use App\Http\Controllers\Controller;
use App\Models\Country;
use Illuminate\Http\Request;

class CountryController extends Controller
{
    // GET /countries
    public function index()
    {
        if (!auth()->user()->can('country.view')) {
        abort(403, 'You do not have permission to country view.');
        }         
        $countries = Country::latest()->get();

        return response()->json([
            'status' => 200,
            'data' => $countries
        ]);
    }

    // POST /countries
    public function store(Request $request)
    {
        if (!auth()->user()->can('country.create')) {
        abort(403, 'You do not have permission to country create.');
        }         
        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'code' => 'nullable|string|max:10|unique:countries,code',
            'status' => 'boolean',
        ]);

        $country = Country::create($validated);

        return response()->json([
            'status' => 201,
            'message' => 'Country created successfully',
            'data' => $country
        ], 201);
    }

    // GET /countries/{id}
    public function show($id)
    {
        if (!auth()->user()->can('country.edit')) {
        abort(403, 'You do not have permission to country edit.');
        }         
        $country = Country::with('regions')->findOrFail($id);

        return response()->json([
            'status' => 200,
            'data' => $country
        ]);
    }

    // PUT /countries/{id}
    public function update(Request $request, $id)
    {
        if (!auth()->user()->can('country.edit')) {
        abort(403, 'You do not have permission to country edit.');
        } 
        $country = Country::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'code' => 'nullable|string|max:10|unique:countries,code,' . $id,
            'status' => 'boolean',
        ]);

        $country->update($validated);

        return response()->json([
            'status' => 200,
            'message' => 'Country updated successfully',
            'data' => $country
        ]);
    }

    // DELETE /countries/{id}
    public function destroy($id)
    {
        if (!auth()->user()->can('country.delete')) {
        abort(403, 'You do not have permission to countyr delete.');
        }         
        $country = Country::findOrFail($id);

        $country->delete();

        return response()->json([
            'status' => 200,
            'message' => 'Country deleted successfully'
        ]);
    }
}