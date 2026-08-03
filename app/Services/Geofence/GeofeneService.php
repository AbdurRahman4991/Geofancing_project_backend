<?php


namespace App\Services\Geofence;

use App\Models\Geofence;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\Attendance;

class GeofeneService
{
    // public function index(Request $request)
    // {
    //     $query = Geofence::with([
    //         'company:id,company_name',
    //         'user:id,name,employee_id',
    //         'user.employee:id,name,employee_id',
    //     ]);

    //     if (auth()->user()->hasRole('super-admin')) {

    //         // User Filter
    //         if ($request->filled('user_id')) {
    //             $query->where('user_id', $request->user_id);
    //         }

    //         // Name / Employee ID Search
    //         if ($request->filled('search')) {
    //             $search = $request->search;

    //             $query->whereHas('user', function ($q) use ($search) {
    //                 $q->where('name', 'like', "%{$search}%")
    //                 ->orWhere('employee_id', 'like', "%{$search}%");
    //             });
    //         }

    //         return response()->json(
    //             $query->latest()->paginate($request->per_page ?? 10)
    //         );
    //     }

    //     // Employee শুধুমাত্র নিজের Geofence দেখবে
    //     return response()->json(
    //         $query->where('user_id', auth()->id())
    //             ->latest()
    //             ->get()
    //     );
    // }


    public function index(Request $request)
    {
        $query = Geofence::with([
            'company:id,company_name',
            'user:id,name,employee_id',
            'user.employee:id,name,employee_id',
        ]);

        if (auth()->user()->hasRole('super-admin')) {

            // আগের super-admin logic...
            if ($request->filled('user_id')) {
                $query->where('user_id', $request->user_id);
            }

            if ($request->filled('search')) {
                $search = $request->search;

                $query->whereHas('user', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                    ->orWhere('employee_id', 'like', "%{$search}%");
                });
            }

            return response()->json(
                $query->latest()->paginate($request->per_page ?? 10)
            );
        }

        // Employee-এর geofence
        $geofences = $query
            ->where('user_id', auth()->id())
            ->latest()
            ->get();

        // আজ যে geofence-এ check-in হয়েছে
        $todayVisited = Attendance::where('user_id', auth()->id())
            ->whereDate('check_in_time', today())
            ->pluck('geofence_id')
            ->toArray();

        // checked যোগ করা
        $geofences->each(function ($geofence) use ($todayVisited) {
            $geofence->checked = in_array($geofence->id, $todayVisited);
        });

        return response()->json($geofences);
    }

    public function store(Request $request)
    {
        $geofence = Geofence::create($request->only([
            'company_id',
            'user_id',
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
            'user_id',
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