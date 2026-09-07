<?php

namespace App\Http\Controllers\Api\Geofence;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Geofence\GeofeneService;

class GeofenceController extends Controller
{
    protected $service;

    public function __construct(GeofeneService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request)
    {
        return $this->service->index($request);
    }
    public function store(Request $request)
    {
        $request->validate([
            'company_id' => 'required|integer',
            'area_id' => 'required|integer',
            'firm_name' => 'required|string',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'radius' => 'required|integer|min:1',
        ]);

        $geofence = $this->service->store($request);
        return response()->json(['message' => 'Location created successfully', 'data' => $geofence]);
    }

    public function show($id)
    {
        $geofence = $this->service->show($id);
        return response()->json($geofence);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'company_id' => 'sometimes|integer|max:255',
            'area_id' => 'sometimes|integer',
            'firm_name' => 'required|string',
            'latitude' => 'sometimes|numeric',
            'longitude' => 'sometimes|numeric',
            'radius' => 'sometimes|integer|min:1',
        ]);

        $geofence = $this->service->update($request, $id);
        return response()->json(['message' => 'Location updated successfully', 'data' => $geofence]);
    }

    public function destroy($id)
    {
        $this->service->destroy($id);
        return response()->json(['message' => 'Location deleted successfully']);
    }
}
