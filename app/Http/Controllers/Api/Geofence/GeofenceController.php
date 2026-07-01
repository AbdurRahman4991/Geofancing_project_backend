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

    // public function index(Request $request)
    // {
    //     $data = $this->service->index();
    //     return response()->json($data);
    // }
        public function index(Request $request)
    {
        return response()->json(
            $this->service->index($request)
        );
    }

    public function store(Request $request)
    {
        $request->validate([
            'company_id' => 'required|integer',
            'user_id' => 'required|integer',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'radius' => 'required|integer|min:1',
        ]);

        $geofence = $this->service->store($request);
        return response()->json(['message' => 'Geofence created successfully', 'data' => $geofence]);
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
            'user_id' => 'sometimes|integer',
            'latitude' => 'sometimes|numeric',
            'longitude' => 'sometimes|numeric',
            'radius' => 'sometimes|integer|min:1',
        ]);

        $geofence = $this->service->update($request, $id);
        return response()->json(['message' => 'Geofence updated successfully', 'data' => $geofence]);
    }

    public function destroy($id)
    {
        $this->service->destroy($id);
        return response()->json(['message' => 'Geofence deleted successfully']);
    }
}
