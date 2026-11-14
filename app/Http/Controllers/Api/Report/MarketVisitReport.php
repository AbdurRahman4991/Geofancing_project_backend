<?php

namespace App\Http\Controllers\Api\Report;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Carbon\Carbon;

class MarketVisitReport extends Controller
{
    
public function index(Request $request)
{
    $request->validate([
        'period' => [
            'nullable',
            Rule::in(['daily', 'weekly', 'monthly', 'yearly', 'custom'])
        ],
        'date' => ['nullable', 'date'],
        'from_date' => ['nullable', 'date'],
        'to_date' => ['nullable', 'date', 'after_or_equal:from_date'],

        'hierarchy_level_id' => ['nullable', 'integer'],
        'hierarchy_node_id' => ['nullable', 'integer'],
        'user_id' => ['nullable', 'integer'],

        'visit_status' => [
            'nullable',
            Rule::in(['all', 'visited', 'none_visited'])
        ],

        'page' => ['nullable', 'integer', 'min:1'],
        'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
    ]);

    $user = auth()->user();

    // =====================================
    // COMPANY SECURITY
    // =====================================

    $companyId = $user->company_id;

    $period = $request->period ?? 'daily';
    $visitStatus = $request->visit_status ?? 'all';

    // =====================================
    // DATE RANGE
    // =====================================

    $date = Carbon::parse($request->date ?? now()->toDateString());

    switch ($period) {

        case 'daily':
            $fromDate = $date->copy()->startOfDay();
            $toDate = $date->copy()->addDay()->startOfDay();
            break;

        case 'weekly':
            $fromDate = $date->copy()->startOfWeek();
            $toDate = $date->copy()->addWeek()->startOfWeek();
            break;

        case 'monthly':
            $fromDate = $date->copy()->startOfMonth();
            $toDate = $date->copy()->addMonth()->startOfMonth();
            break;

        case 'yearly':
            $fromDate = $date->copy()->startOfYear();
            $toDate = $date->copy()->addYear()->startOfYear();
            break;

        case 'custom':
            $fromDate = Carbon::parse($request->from_date)->startOfDay();
            $toDate = Carbon::parse($request->to_date)
                ->addDay()
                ->startOfDay();
            break;
    }

    // =====================================
    // BASE QUERY
    // MAIN TABLE: GEOFENCES
    // =====================================

    $query = DB::table('geofences as g')

        ->leftJoin('hierarchy_nodes as hn', function ($join) {
            $join->on('hn.id', '=', 'g.hierarchy_node_id');
        })

        ->leftJoin('hierarchy_levels as hl', function ($join) {
            $join->on('hl.id', '=', 'hn.hierarchy_level_id');
        })

        // Market Visit LEFT JOIN
        ->leftJoin('market_visits as mv', function ($join) use (
            $companyId,
            $fromDate,
            $toDate,
            $request
        ) {
            $join->on('mv.geofence_id', '=', 'g.id')
                ->where('mv.company_id', $companyId)
                ->where('mv.visit_start_time', '>=', $fromDate)
                ->where('mv.visit_start_time', '<', $toDate);

            // Selected User Filter
            if ($request->filled('user_id')) {
                $join->where('mv.user_id', $request->user_id);
            }
        })

        ->leftJoin('users as u', 'u.id', '=', 'mv.user_id')

        ->where('g.company_id', $companyId);

    // =====================================
    // HIERARCHY LEVEL FILTER
    // =====================================

    if ($request->filled('hierarchy_level_id')) {
        $query->where(
            'hn.hierarchy_level_id',
            $request->hierarchy_level_id
        );
    }

    // =====================================
    // HIERARCHY NODE FILTER
    // =====================================

    if ($request->filled('hierarchy_node_id')) {
        $query->where(
            'g.hierarchy_node_id',
            $request->hierarchy_node_id
        );
    }

    // =====================================
    // VISIT STATUS FILTER
    // =====================================

    if ($visitStatus === 'visited') {

        // Only visited markets
        $query->whereNotNull('mv.id');

    } elseif ($visitStatus === 'none_visited') {

        // Only markets without visits
        $query->whereNull('mv.id');
    }

    // =====================================
    // SUMMARY
    // =====================================

    $summary = (clone $query)
        ->reorder()
        ->selectRaw('
            COUNT(DISTINCT mv.id) as total_visits,

            COUNT(DISTINCT mv.user_id) as total_employees,

            COUNT(DISTINCT g.id) as total_markets,

            COUNT(DISTINCT g.id) FILTER (
                WHERE mv.id IS NOT NULL
            ) as visited_markets,

            COUNT(DISTINCT g.id) FILTER (
                WHERE mv.id IS NULL
            ) as none_visited_markets,

            COUNT(DISTINCT mv.id) FILTER (
                WHERE mv.visit_end_time IS NOT NULL
            ) as completed_visits,

            COUNT(DISTINCT mv.id) FILTER (
                WHERE mv.visit_end_time IS NULL
                AND mv.id IS NOT NULL
            ) as active_visits,

            COALESCE(SUM(mv.duration), 0) as total_duration
        ')
        ->first();

    // =====================================
    // DETAIL REPORT
    // =====================================

    $query->select([
        'g.id as geofence_id',
        'g.firm_name as market_name',
        'g.address as market_address',

        'hl.id as hierarchy_level_id',
        'hl.name as hierarchy_level',

        'hn.id as hierarchy_node_id',
        'hn.name as hierarchy_node',

        'mv.id as visit_id',
        'mv.user_id',
        'u.name as employee_name',

        'mv.visit_start_time',
        'mv.visit_end_time',
        'mv.duration',

        'mv.check_in_latitude',
        'mv.check_in_longitude',

        'mv.check_out_latitude',
        'mv.check_out_longitude',
    ]);

    $query->orderBy('g.id')
        ->orderByDesc('mv.visit_start_time');

    // =====================================
    // PAGINATION
    // =====================================

    $perPage = $request->input('per_page', 20);

    $markets = $query->paginate($perPage);

    // =====================================
    // RESPONSE
    // =====================================

    return response()->json([
        'success' => true,
        'message' => 'Market visit report retrieved successfully.',

        'filters' => [
            'period' => $period,
            'from_date' => $fromDate->toDateString(),
            'to_date' => $toDate->copy()->subDay()->toDateString(),

            'hierarchy_level_id' => $request->hierarchy_level_id,
            'hierarchy_node_id' => $request->hierarchy_node_id,
            'user_id' => $request->user_id,

            'visit_status' => $visitStatus,
        ],

        'summary' => $summary,

        'data' => $markets,
    ]);
}

}
