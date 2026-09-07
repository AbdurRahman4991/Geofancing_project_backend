<?php


namespace App\Services\Geofence;

use App\Models\Geofence;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\Attendance;

class GeofeneService
{
   
   public function index(Request $request)
    {
        $query = Geofence::with([
            'company:id,company_name',
            'user:id,name,employee_id',
            'user.employee:id,name,employee_id',
        ]);

        if (auth()->user()->hasRole('super-admin')) {

            if ($request->filled('area_id')) {
                $query->where('area_id', $request->area_id);
            }

            if ($request->filled('search')) {
                $search = $request->search;

                $query->whereHas('user.employee', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                    ->orWhere('employee_id', 'like', "%{$search}%");
                });
            }

            $pagination = $query->latest()->paginate($request->per_page ?? 10);

            return response()->json([
                "status" => 200,
                "message" => "Geofence list retrieved successfully",
                "data" => $pagination
            ]);
        }

        $geofences = $query
            ->where('area_id', auth()->id())
            ->latest()
            ->get();

        $todayVisited = Attendance::where('user_id', auth()->id())
            ->whereDate('check_in_time', today())
            ->pluck('geofence_id')
            ->toArray();

        $geofences->each(function ($geofence) use ($todayVisited) {
            $geofence->checked = in_array($geofence->id, $todayVisited);
        });

        return response()->json([
            "status" => 200,
            "message" => "Geofence list retrieved successfully",
            "data" => [
                "current_page" => 1,
                "data" => $geofences,
                "total" => $geofences->count(),
                "per_page" => $geofences->count(),
                "last_page" => 1,
            ]
        ]);
    }



    public function store(Request $request)
    {
        $geofence = Geofence::create($request->only([
            'company_id',
            'area_id',
            'firm_name',
            'latitude',
            'longitude',
            'radius',
        ]));

        if ($request->hasFile('image')) {
            $geofence->uploadImage($request->file('image'));
        }

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
            'area_id',
            'firm_name',
            'latitude',
            'longitude',
            'radius',
        ]));
        
        if ($request->hasFile('image')) {
            $geofence->uploadImage($request->file('image'));
        }

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