<?php

namespace App\Http\Controllers\Api\Report;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

class AttendanceReportController extends Controller
{
     public function index(Request $request)
    {
        $validated = $request->validate([
            'period' => [
                'nullable',
                Rule::in(['daily', 'weekly', 'monthly', 'yearly', 'custom']),
            ],
            'date' => ['nullable', 'date'],
            'from_date' => ['nullable', 'date'],
            'to_date' => ['nullable', 'date', 'after_or_equal:from_date'],
            'export' => ['nullable', Rule::in(['excel', 'pdf']),],

            'user_id' => ['nullable', 'integer'],
            'hierarchy_level_id' => ['nullable', 'integer'],
            'hierarchy_node_id' => ['nullable', 'integer'],

            'status' => [
                'nullable',
                Rule::in([
                    'Checked In',
                    'Checked Out',
                    'Auto Checked Out',
                    'Outside Area',
                    'Absent',
                    'Weekend Holyday',
                    'Goverment Holyday',
                    'Leave',
                ]),
            ],

            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $companyId = auth()->user()->company_id;

        if (!$companyId) {
            return response()->json([
                'message' => 'Company not found for this user.',
            ], 403);
        }

        $company = DB::table('companies')
        ->where('id', $companyId)
        ->first();

        // -----------------------------------------
        // 1. Date range
        // -----------------------------------------

        $period = $validated['period'] ?? 'daily';

        $date = Carbon::parse($validated['date'] ?? now()->toDateString());

        switch ($period) {
            case 'weekly':
                $fromDate = $date->copy()->startOfWeek();
                $toDate = $date->copy()->endOfWeek();
                break;

            case 'monthly':
                $fromDate = $date->copy()->startOfMonth();
                $toDate = $date->copy()->endOfMonth();
                break;

            case 'yearly':
                $fromDate = $date->copy()->startOfYear();
                $toDate = $date->copy()->endOfYear();
                break;

            case 'custom':
                if (
                    empty($validated['from_date']) ||
                    empty($validated['to_date'])
                ) {
                    return response()->json([
                        'message' => 'from_date and to_date are required for custom report.',
                    ], 422);
                }

                $fromDate = Carbon::parse($validated['from_date']);
                $toDate = Carbon::parse($validated['to_date']);
                break;

            default:
                $fromDate = $date->copy()->startOfDay();
                $toDate = $date->copy()->endOfDay();
                break;
        }

        $fromDateString = $fromDate->toDateString();
        $toDateString = $toDate->toDateString();

        // -----------------------------------------
        // 2. Main report query
        // -----------------------------------------

        $query = DB::table('attendances')
            ->join('users', 'users.id', '=', 'attendances.user_id')

            ->leftJoin('employee_hierarchy_assignments as assignments', function ($join) {
                $join->on('assignments.user_id', '=', 'users.id')
                    ->on('assignments.company_id', '=', 'attendances.company_id')
                    ->where('assignments.is_current', true);
            })

            ->leftJoin(
                'hierarchy_nodes',
                'hierarchy_nodes.id',
                '=',
                'assignments.hierarchy_node_id'
            )

            ->leftJoin(
                'hierarchy_levels',
                'hierarchy_levels.id',
                '=',
                'hierarchy_nodes.hierarchy_level_id'
            )

            ->where('attendances.company_id', $companyId)
            ->whereBetween('attendances.check_in_date', [
                $fromDateString,
                $toDateString,
            ]);

        // -----------------------------------------
        // 3. Optional filters
        // -----------------------------------------

        if (!empty($validated['user_id'])) {
            $query->where('attendances.user_id', $validated['user_id']);
        }

        if (!empty($validated['hierarchy_level_id'])) {
            $query->where(
                'hierarchy_levels.id',
                $validated['hierarchy_level_id']
            );
        }

        if (!empty($validated['hierarchy_node_id'])) {
            $query->where(
                'hierarchy_nodes.id',
                $validated['hierarchy_node_id']
            );
        }

        if (!empty($validated['status'])) {
            $query->where('attendances.status', $validated['status']);
        }

        // -----------------------------------------
        // 4. Summary
        // -----------------------------------------

        $summaryQuery = clone $query;

        $summary = $summaryQuery->selectRaw("
            COUNT(DISTINCT attendances.id) as total_attendance,

            COUNT(DISTINCT CASE
                WHEN attendances.status = 'Checked In'
                THEN attendances.id
            END) as checked_in,

            COUNT(DISTINCT CASE
                WHEN attendances.status IN (
                    'Checked Out',
                    'Auto Checked Out'
                )
                THEN attendances.id
            END) as checked_out,

            COUNT(DISTINCT CASE
                WHEN attendances.status = 'Absent'
                THEN attendances.id
            END) as absent,

            COUNT(DISTINCT CASE
                WHEN attendances.status = 'Leave'
                THEN attendances.id
            END) as leave_count,

            COUNT(DISTINCT CASE
                WHEN attendances.late IS NOT NULL
                    AND attendances.late != ''
                THEN attendances.id
            END) as late_count
        ")->first();


        // -----------------------------------------
        // 5. Paginated report data
        // -----------------------------------------

        $reportQuery = clone $query;

        $reportQuery->select([
            'attendances.id',
            'attendances.company_id',
            'attendances.user_id',
            'attendances.check_in_date',
            'attendances.check_in_time',
            'attendances.check_in_latitude',
            'attendances.check_in_longitude',
            'attendances.check_out_time',
            'attendances.check_out_latitude',
            'attendances.check_out_longitude',
            'attendances.work_hour',
            'attendances.late',
            'attendances.status',
            'attendances.remarks',

            'users.name as employee_name',
            'users.email as employee_email',

            'hierarchy_nodes.id as hierarchy_node_id',
            'hierarchy_nodes.name as hierarchy_node_name',

            'hierarchy_levels.id as hierarchy_level_id',
            'hierarchy_levels.name as hierarchy_level_name',
        ]);

        $report = $reportQuery
            ->orderBy('attendances.check_in_date', 'desc')
            ->orderBy('attendances.check_in_time', 'desc')
            ->paginate($validated['per_page'] ?? 15);
            


        // -----------------------------------------
        // 5. Export Excel / PDF
        // -----------------------------------------

        $export = $validated['export'] ?? null;

        if ($export) {

            // Export all filtered records without pagination
            $exportData = (clone $reportQuery)->get();

            $fileName = 'attendance_report_' .
                now()->format('Y_m_d_H_i_s');

            // Excel Export
            if ($export === 'excel') {

                return Excel::download(
                    new AttendanceReportExport($exportData),
                    $fileName . '.xlsx'
                );
            }

            // PDF Export
            if ($export === 'pdf') {

                $pdf = Pdf::loadView(
                    'reports.attendance-pdf',
                    [
                        'company' => $company,
                        'attendances' => $exportData,
                        'summary' => $summary,
                        'filters' => [
                            'from_date' => $fromDateString,
                            'to_date' => $toDateString,
                            'period' => $period,
                        ],
                    ]
                );

                $pdf->setPaper('a4', 'landscape');

                return $pdf->download($fileName . '.pdf');
            }
        }            

        // -----------------------------------------
        // 6. Response
        // -----------------------------------------

        return response()->json([
            'message' => 'Attendance report retrieved successfully.',

            'filters' => [
                'period' => $period,
                'from_date' => $fromDateString,
                'to_date' => $toDateString,
                'user_id' => $validated['user_id'] ?? null,
                'hierarchy_level_id' => $validated['hierarchy_level_id'] ?? null,
                'hierarchy_node_id' => $validated['hierarchy_node_id'] ?? null,
                'status' => $validated['status'] ?? null,
            ],

            'summary' => $summary,

            'data' => $report->items(),

            'pagination' => [
                'current_page' => $report->currentPage(),
                'per_page' => $report->perPage(),
                'total' => $report->total(),
                'last_page' => $report->lastPage(),
            ],
        ]);
    }
}
