<?php

namespace App\Http\Controllers\Api\Report;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Validation\Rule;
use Illuminate\Pagination\LengthAwarePaginator;

class MonthlyPerformanceController extends Controller
{
     public function index(Request $request)
    {
        $request->validate([
            'period' => [
                'nullable',
                Rule::in(['daily', 'weekly', 'monthly', 'yearly', 'custom'])
            ],

            'date' => ['nullable', 'date'],
            'from_date' => ['nullable', 'required_if:period,custom', 'date'],
            'to_date' => [
                'nullable',
                'required_if:period,custom',
                'date',
                'after_or_equal:from_date'
            ],

            'hierarchy_level_id' => ['nullable', 'integer'],
            'hierarchy_node_id' => ['nullable', 'integer'],
            'user_id' => ['nullable', 'integer'],
            'attendance_role_id' => ['nullable', 'integer'],

            'export' => [
                'nullable',
                Rule::in(['pdf', 'excel'])
            ],

            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $authUser = auth()->user();

        // =====================================
        // COMPANY SECURITY
        // =====================================

        $companyId = $authUser->company_id;

        $company = DB::table('companies')
            ->where('id', $companyId)
            ->first();

        // =====================================
        // PERIOD
        // =====================================

        $period = $request->period ?? 'monthly';

        $date = Carbon::parse(
            $request->date ?? now()->toDateString()
        );

        // =====================================
        // DATE RANGE
        // End date is exclusive
        // =====================================

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
                $fromDate = Carbon::parse($request->from_date)
                    ->startOfDay();

                $toDate = Carbon::parse($request->to_date)
                    ->addDay()
                    ->startOfDay();
                break;

            default:
                $fromDate = $date->copy()->startOfMonth();
                $toDate = $date->copy()->addMonth()->startOfMonth();
                break;
        }

        $start = $fromDate->copy();
        $end = $toDate->copy();

        // =====================================
        // 1. EMPLOYEE QUERY
        // =====================================

        $employeesQuery = DB::table('users')
            ->where('users.company_id', $companyId)
            ->where('users.status', 'active');

        if ($request->filled('user_id')) {
            $employeesQuery->where(
                'users.id',
                $request->user_id
            );
        }

        // Hierarchy Level Filter
        if ($request->filled('hierarchy_level_id')) {

            $employeesQuery->whereExists(function ($query) use (
                $request,
                $companyId
            ) {
                $query->select(DB::raw(1))
                    ->from('employee_hierarchy_assignments as eha')
                    ->join(
                        'hierarchy_nodes as hn',
                        'hn.id',
                        '=',
                        'eha.hierarchy_node_id'
                    )
                    ->whereColumn('eha.user_id', 'users.id')
                    ->where('eha.company_id', $companyId)
                    ->where('eha.is_current', true)
                    ->where(
                        'hn.hierarchy_level_id',
                        $request->hierarchy_level_id
                    );
            });
        }

        // Hierarchy Node Filter
        if ($request->filled('hierarchy_node_id')) {

            $employeesQuery->whereExists(function ($query) use (
                $request,
                $companyId
            ) {
                $query->select(DB::raw(1))
                    ->from('employee_hierarchy_assignments as eha')
                    ->whereColumn('eha.user_id', 'users.id')
                    ->where('eha.company_id', $companyId)
                    ->where(
                        'eha.hierarchy_node_id',
                        $request->hierarchy_node_id
                    )
                    ->where('eha.is_current', true);
            });
        }

        /*
         * Attendance Role Filter:
         * নিচের users.attendance_role_id কলামটি আপনার
         * users table-এ থাকলে এই filter ব্যবহার করবেন।
         */

        if ($request->filled('attendance_role_id')) {
            $employeesQuery->where(
                'users.attendance_role_id',
                $request->attendance_role_id
            );
        }

        $employees = $employeesQuery
            ->select(
                'users.id',
                'users.name',
                'users.employee_id',
                'users.target'
            )
            ->orderBy('users.name')
            ->get();

        $userIds = $employees->pluck('id')->all();

        // =====================================
        // 2. ATTENDANCE PERFORMANCE
        // =====================================

        $attendance = DB::table('attendances')
            ->whereIn('user_id', $userIds)
            ->whereBetween('check_in_time', [$start, $end])
            ->select(
                'user_id',

                DB::raw("
                    COUNT(DISTINCT DATE(check_in_time))
                    AS present_days
                "),

                DB::raw("
                    SUM(
                        CASE
                            WHEN LOWER(COALESCE(late, ''))
                            IN ('yes', 'true', '1', 'late')
                            THEN 1
                            ELSE 0
                        END
                    ) AS late_days
                "),

                DB::raw("
                    SUM(
                        CASE
                            WHEN check_out_time IS NOT NULL
                            THEN 1
                            ELSE 0
                        END
                    ) AS checkout_days
                "),

                DB::raw("
                    COALESCE(
                        SUM(NULLIF(work_hour, '')::NUMERIC),
                        0
                    ) AS total_work_hours
                ")
            )
            ->groupBy('user_id')
            ->get()
            ->keyBy('user_id');

        // =====================================
        // 3. MARKET VISIT PERFORMANCE
        // =====================================

        $visits = DB::table('market_visits')
            ->where('company_id', $companyId)
            ->whereIn('user_id', $userIds)
            ->whereBetween('visit_start_time', [$start, $end])
            ->select(
                'user_id',

                DB::raw('COUNT(*) AS total_visits'),

                DB::raw("
                    SUM(
                        CASE
                            WHEN visit_end_time IS NOT NULL
                            THEN 1 ELSE 0
                        END
                    ) AS completed_visits
                "),

                DB::raw("
                    SUM(
                        CASE
                            WHEN visit_end_time IS NULL
                            THEN 1 ELSE 0
                        END
                    ) AS incomplete_visits
                "),

                DB::raw("
                    COUNT(DISTINCT geofence_id)
                    AS unique_customers
                "),

                DB::raw("
                    COALESCE(SUM(duration), 0)
                    AS total_visit_duration
                ")
            )
            ->groupBy('user_id')
            ->get()
            ->keyBy('user_id');

        // =====================================
        // 4. SALES PERFORMANCE
        // =====================================

        $sales = DB::table('orders')
            ->where('orders.company_id', $companyId)
            ->whereIn('orders.created_by', $userIds)
            ->whereBetween('orders.created_at', [$start, $end])
            ->select(
                'orders.created_by as user_id',

                DB::raw('COUNT(orders.id) AS total_orders'),

                DB::raw("
                    COALESCE(SUM(orders.total), 0)
                    AS total_sales
                "),

                DB::raw("
                    COUNT(DISTINCT orders.geofence_id)
                    AS ordering_customers
                ")
            )
            ->groupBy('orders.created_by')
            ->get()
            ->keyBy('user_id');

        // =====================================
        // 5. ORDER ITEM PERFORMANCE
        // =====================================

        $orderItems = DB::table('order_items')
            ->join(
                'orders',
                'orders.id',
                '=',
                'order_items.order_id'
            )
            ->where('orders.company_id', $companyId)
            ->whereIn('orders.created_by', $userIds)
            ->whereBetween('orders.created_at', [$start, $end])
            ->select(
                'orders.created_by as user_id',

                DB::raw("
                    COALESCE(SUM(order_items.quantity), 0)
                    AS total_quantity
                "),

                DB::raw("
                    COUNT(order_items.id)
                    AS total_order_items
                ")
            )
            ->groupBy('orders.created_by')
            ->get()
            ->keyBy('user_id');

        // =====================================
        // 6. WORKING DAYS
        // Monday - Friday
        // =====================================

        $workingDays = $start->copy()
            ->startOfDay()
            ->diffInDaysFiltered(
                function ($date) {
                    return $date->isWeekday();
                },
                $end->copy()->startOfDay()
            );

        // =====================================
        // 7. MERGE EMPLOYEE PERFORMANCE
        // =====================================

        $data = $employees->map(function ($employee) use (
            $attendance,
            $visits,
            $sales,
            $orderItems,
            $workingDays
        ) {

            $att = $attendance->get($employee->id);
            $visit = $visits->get($employee->id);
            $sale = $sales->get($employee->id);
            $items = $orderItems->get($employee->id);

            $presentDays = (int) ($att->present_days ?? 0);

            $totalVisits = (int) ($visit->total_visits ?? 0);

            $totalSales = (float) ($sale->total_sales ?? 0);

            $totalOrders = (int) ($sale->total_orders ?? 0);

            return [

                'user_id' => $employee->id,

                'employee_name' => $employee->name,

                'employee_id' => $employee->employee_id,

                // Attendance
                'attendance' => [

                    'working_days' => $workingDays,

                    'present_days' => $presentDays,

                    'absent_days' => max(
                        0,
                        $workingDays - $presentDays
                    ),

                    'late_days' => (int) ($att->late_days ?? 0),

                    'checkout_days' => (int) ($att->checkout_days ?? 0),

                    'attendance_rate' => $workingDays > 0
                        ? round(
                            ($presentDays / $workingDays) * 100,
                            2
                        )
                        : 0,
                ],

                // Market Visit
                'field_visits' => [

                    'total_visits' => $totalVisits,

                    'completed_visits' => (int) (
                        $visit->completed_visits ?? 0
                    ),

                    'incomplete_visits' => (int) (
                        $visit->incomplete_visits ?? 0
                    ),

                    'unique_customers' => (int) (
                        $visit->unique_customers ?? 0
                    ),

                    'total_visit_duration' => (float) (
                        $visit->total_visit_duration ?? 0
                    ),
                ],

                // Working Hours
                'working_hours' => [

                    'total_hours' => round(
                        (float) ($att->total_work_hours ?? 0),
                        2
                    ),

                    'average_hours' => $presentDays > 0
                        ? round(
                            (float) ($att->total_work_hours ?? 0)
                            / $presentDays,
                            2
                        )
                        : 0,
                ],

                // Sales
                'sales' => [

                    'total_orders' => $totalOrders,

                    'total_order_items' => (int) (
                        $items->total_order_items ?? 0
                    ),

                    'total_quantity' => (float) (
                        $items->total_quantity ?? 0
                    ),

                    'total_sales' => round($totalSales, 2),

                    'average_order_value' => $totalOrders > 0
                        ? round(
                            $totalSales / $totalOrders,
                            2
                        )
                        : 0,

                    'ordering_customers' => (int) (
                        $sale->ordering_customers ?? 0
                    ),
                ],

                // Target
                'target_achievement' => [

                    'sales_target' => null,

                    'achieved_sales' => round($totalSales, 2),

                    'achievement_percentage' => null,

                    'remaining_target' => null,

                    'visit_target' => null,

                    'visit_achievement_percentage' => null,

                    'target' => (float) ($employee->target ?? 0),
                    'achieved' => (float) $totalSales,
                    'percentage' => $employee->target > 0
                        ? round(($totalSales / $employee->target) * 100, 2)
                        : 0,
                ],
            ];
        })->values();

        // =====================================
        // 8. SUMMARY
        // All filtered employees
        // =====================================
                
        $isIndividualFilter = $request->filled('user_id');

        if (!$isIndividualFilter) {

            // All Employee Summary
            $summary = [
                'total_employees' => $data->count(),

                'total_present_days' => $data->sum(
                    fn ($row) => $row['attendance']['present_days']
                ),

                'total_absent_days' => $data->sum(
                    fn ($row) => $row['attendance']['absent_days']
                ),

                'total_late_days' => $data->sum(
                    fn ($row) => $row['attendance']['late_days']
                ),

                'total_market_visits' => $data->sum(
                    fn ($row) => $row['field_visits']['total_visits']
                ),

                'total_orders' => $data->sum(
                    fn ($row) => $row['sales']['total_orders']
                ),

                'total_sales' => round(
                    $data->sum(
                        fn ($row) => $row['sales']['total_sales']
                    ),
                    2
                ),
            ];

        } else {

            // Individual User Summary
            $summary = [
                'total_employees' => $data->count(),

                'total_orders' => $data->sum(
                    fn ($row) => $row['sales']['total_orders']
                ),

                'total_sales' => round(
                    $data->sum(
                        fn ($row) => $row['sales']['total_sales']
                    ),
                    2
                ),
            ];
        }


        // =====================================
        // 9. EXPORT EXCEL
        // Export all filtered employees
        // =====================================

        if ($request->export === 'excel') {

            return Excel::download(
                new MonthlyPerformanceExport($data),
                'monthly_performance_report.xlsx'
            );
        }

        // =====================================
        // 10. EXPORT PDF
        // Export all filtered employees
        // =====================================

        if ($request->export === 'pdf') {

            $pdf = Pdf::loadView(
                'reports.monthly_performance_pdf',
                [
                    'data' => $data,
                    'summary' => $summary,
                    'company' => $company,

                    'filters' => [
                        'period' => $period,
                        'from_date' => $fromDate->toDateString(),
                        'to_date' => $toDate->copy()
                            ->subDay()
                            ->toDateString(),
                    ],
                ]
            )->setPaper('a4', 'landscape');

            return $pdf->download(
                'monthly_performance_report.pdf'
            );
        }

        // =====================================
        // 11. PAGINATION
        // Paginate after calculating full report
        // =====================================

        $perPage = $request->integer('per_page', 10);
        $page = $request->integer('page', 1);

        $total = $data->count();

        $pageData = $data
            ->slice(($page - 1) * $perPage, $perPage)
            ->values();

        $paginatedData = new LengthAwarePaginator(
            $pageData,
            $total,
            $perPage,
            $page,
            [
                'path' => $request->url(),
                'query' => $request->query(),
            ]
        );

        // =====================================
        // 12. RESPONSE
        // =====================================

        return response()->json([

            'success' => true,

            'message' => 'Monthly performance report retrieved successfully.',

            'filters' => [
               

                'period' => $period,

                'from_date' => $fromDate->toDateString(),

                'to_date' => $toDate->copy()
                    ->subDay()
                    ->toDateString(),

                'hierarchy_level_id' => $request->hierarchy_level_id,

                'hierarchy_node_id' => $request->hierarchy_node_id,

                'user_id' => $request->user_id,

                // 'attendance_role_id' => $request->attendance_role_id,
            ],

            'summary' => $summary,

            'pagination' => [

                'current_page' => $paginatedData->currentPage(),

                'per_page' => $paginatedData->perPage(),

                'total' => $paginatedData->total(),

                'last_page' => $paginatedData->lastPage(),

                'from' => $paginatedData->firstItem(),

                'to' => $paginatedData->lastItem(),
            ],

            'data' => $paginatedData->items(),

        ]);
    }
}
