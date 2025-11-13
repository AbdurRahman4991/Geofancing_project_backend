<?php


namespace App\Services\Geofence;

use App\Models\Geofence;
use Illuminate\Http\Request;

class GeofeneService
{
     public function index()
    {
        return Geofence::with(['company:id,company_name', 'user:id,name'])
            ->latest()
            ->get();
    }

    public function store(Request $request)
    {
        $geofence = Geofence::create($request->only([
            'company_id',
            'user_id',
            'latitude',
            'longitude',
            'radius',
        ]));

        return $geofence->load([
            'company:id,company_name',
            'user:id,name',
        ]);
    }

    public function show($id)
    {
        return Geofence::with(['company:id,company_name', 'user:id,name'])
            ->findOrFail($id);
    }

    public function update(Request $request, $id)
    {
        $geofence = Geofence::findOrFail($id);

        $geofence->update($request->only([
            'company_id',
            'user_id',
            'latitude',
            'longitude',
            'radius',
        ]));

        return $geofence->fresh([
            'company:id,company_name',
            'user:id,name',
        ]);
    }

    public function destroy($id)
    {
        $geofence = Geofence::findOrFail($id);
        return $geofence->delete();
    }
}
