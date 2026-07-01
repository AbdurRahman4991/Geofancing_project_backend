<?php


namespace App\Services\Geofence;

use App\Models\Geofence;
use Illuminate\Http\Request;

class GeofeneService
{
    //  public function index()
    // {
    //     return Geofence::with(['company:id,company_name', 'user:id,name'])
    //         ->latest()
    //         ->get();
    // }
    public function index(Request $request)
{
    $search = $request->input('search');
    $perPage = $request->input('per_page', 10);

    $query = Geofence::with(['company:id,company_name', 'user:id,name']);

    // Search
    if ($search) {
        $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
                ->orWhere('address', 'like', "%{$search}%")
                ->orWhere('latitude', 'like', "%{$search}%")
                ->orWhere('longitude', 'like', "%{$search}%");
        });
    }

    // Pagination
    $pagination = $query->latest()->paginate($perPage);

    // Return same format as Company API
    return [
        "status" => 200,
        "message" => "Geofence list retrieved successfully",
        "data" => $pagination
    ];
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
