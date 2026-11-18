<?php

namespace App\Http\Controllers\Api\Report;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;


class EmployeeRegistrationReportController extends Controller
{
     public function index(Request $request)
    {
        $this->authorizeAction('view');
        // =====================================
        // 1. VALIDATION
        // =====================================

        $request->validate([
            'company_id' => 'nullable|integer',
            'registration_status' => 'nullable|in:registered,unregistered,both',
            'employee_status' => 'nullable|in:active,inactive,terminated',
            'search' => 'nullable|string|max:100',
            'per_page' => 'nullable|integer|min:1|max:200',
        ]);

        $authUser = auth()->user();

        if (!$authUser) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated.',
            ], 401);
        }


        // =====================================
        // 2. LOGIN USER ACCESS
        // =====================================

        $isSuperAdmin = $authUser->hasRole('Super-Admin')
            || $authUser->hasRole('super-admin');

        $isCompanyAdmin = $authUser->hasRole('Company Admin')
            || $authUser->hasRole('company-admin');


        // =====================================
        // 3. COMPANY SCOPE
        // =====================================

        if ($isSuperAdmin) {

            // Super Admin can see all companies
            // or filter by a specific company.

            $companyId = $request->company_id;

        } else {

            // Other users can see only their own company.

            $companyId = $authUser->company_id
                ?: DB::table('employees')
                    ->where('id', $authUser->employee_id)
                    ->value('company_id');

            if (!$companyId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Company not found.',
                ], 403);
            }
        }


        // =====================================
        // 4. REGISTERED USER MAP
        // =====================================

        // employees is the master table.
        // users.employee_id = employees.id
        //
        // If multiple users exist for one employee,
        // select the user with the highest ID.

        $userMap = DB::table('users')
            ->select(
                'employee_id',
                DB::raw('MAX(id) as user_id')
            )
            ->whereNotNull('employee_id')
            ->groupBy('employee_id');


        // =====================================
        // 5. MAIN EMPLOYEE QUERY
        // =====================================

        $query = DB::table('employees as e')

            ->leftJoinSub(
                $userMap,
                'um',
                function ($join) {
                    $join->on(
                        'um.employee_id',
                        '=',
                        'e.id'
                    );
                }
            )

            ->leftJoin(
                'users as u',
                'u.id',
                '=',
                'um.user_id'
            )

            ->leftJoin(
                'companies as c',
                'c.id',
                '=',
                'e.company_id'
            )

            ->select(
                'e.id',
                'e.employee_id',
                'e.name as employee_name',
                'e.phone',
                'e.email',
                'e.company_id',
                'e.status as employee_status',

                'c.company_name',

                'u.id as user_id',
                'u.device_id',
                'u.status as user_status',
                'u.created_at as registered_at',

                DB::raw("
                    CASE
                        WHEN u.id IS NOT NULL
                        THEN 'Registered'
                        ELSE 'Unregistered'
                    END as registration_status
                ")
            );


        // =====================================
        // 6. APPLY COMPANY FILTER
        // =====================================

        if ($companyId) {

            $query->where('e.company_id', $companyId);

        }


        // =====================================
        // 7. EMPLOYEE STATUS FILTER
        // =====================================

        if ($request->filled('employee_status')) {

            $query->where(
                'e.status',
                $request->employee_status
            );

        }


        // =====================================
        // 8. SUMMARY
        // =====================================

        // Summary is calculated before
        // registration status and search filters.

        $summary = (clone $query)
            ->reorder()
            ->select(DB::raw("
                COUNT(DISTINCT e.id) as total_employees,

                COUNT(DISTINCT CASE
                    WHEN u.id IS NOT NULL THEN e.id
                END) as registered_employees,

                COUNT(DISTINCT CASE
                    WHEN u.id IS NULL THEN e.id
                END) as unregistered_employees
            "))
            ->first();


        $totalEmployees = (int) (
            $summary->total_employees ?? 0
        );

        $registeredEmployees = (int) (
            $summary->registered_employees ?? 0
        );

        $unregisteredEmployees = (int) (
            $summary->unregistered_employees ?? 0
        );

        $registrationRate = $totalEmployees > 0
            ? round(
                ($registeredEmployees / $totalEmployees) * 100,
                2
            )
            : 0;


        // =====================================
        // 9. REGISTRATION FILTER
        // =====================================

        $registrationStatus = $request->registration_status ?? 'both';

        if ($registrationStatus === 'registered') {

            $query->whereNotNull('u.id');

        } elseif ($registrationStatus === 'unregistered') {

            $query->whereNull('u.id');

        }


        // =====================================
        // 10. SEARCH FILTER
        // =====================================

        if ($request->filled('search')) {

            $search = $request->search;

            $query->where(function ($q) use ($search) {

                $q->where('e.name', 'ILIKE', "%{$search}%")
                    ->orWhere('e.employee_id', 'ILIKE', "%{$search}%")
                    ->orWhere('e.phone', 'ILIKE', "%{$search}%")
                    ->orWhere('e.email', 'ILIKE', "%{$search}%");

            });

        }
        
        // =====================================
        // 11. EXPORT EXCEL
        // =====================================

        if ($request->export === 'excel') {

            $exportData = (clone $query)
                ->reorder()
                ->orderBy('e.id', 'desc')
                ->get();

            return Excel::download(
                new EmployeeRegistrationExport($exportData),
                'employee_registration_report.xlsx'
            );
        }


        // =====================================
        // 12. EXPORT PDF
        // =====================================

        if ($request->export === 'pdf') {

            // Get all filtered employees, without pagination.

            $exportData = (clone $query)
                ->reorder()
                ->orderBy('e.id', 'desc')
                ->get();

            // Get company information.

            $company = $companyId
                ? DB::table('companies')
                    ->where('id', $companyId)
                    ->first()
                : null;

            $pdf = Pdf::loadView(
                'reports.employee_registration_pdf',
                [
                    'data' => $exportData,
                    'summary' => [
                        'total_employees' => $totalEmployees,
                        'registered_employees' => $registeredEmployees,
                        'unregistered_employees' => $unregisteredEmployees,
                        'registration_rate' => $registrationRate,
                    ],
                    'company' => $company,
                    'filters' => [
                        'company_id' => $companyId,
                        'registration_status' => $registrationStatus,
                        'employee_status' => $request->employee_status,
                        'search' => $request->search,
                    ],
                ]
            )->setPaper('a4', 'landscape');

            return $pdf->download(
                'employee_registration_report.pdf'
            );
        }


        // =====================================
        // 13. PAGINATION
        // =====================================

        $perPage = $request->per_page ?? 20;

        $data = $query
            ->orderBy('e.id', 'desc')
            ->paginate($perPage)
            ->withQueryString();
       

        // =====================================
        // 12. RESPONSE
        // =====================================

        return response()->json([

            'success' => true,

            'message' => 'Employee Registration Report',

            'filters' => [
                'company_id' => $companyId,
                'registration_status' => $registrationStatus,
                'employee_status' => $request->employee_status,
                'search' => $request->search,
                'per_page' => $perPage,
            ],

            'summary' => [
                'total_employees' => $totalEmployees,
                'registered_employees' => $registeredEmployees,
                'unregistered_employees' => $unregisteredEmployees,
                'registration_rate' => $registrationRate,
            ],

            'data' => $data,

        ]);
    }
    private function authorizeAction(string $action): void
    {
        abort_unless(auth()->user()?->can('employee-register-report.' . $action), 403, 'You do not have permission to ' . $action . ' orders.');
    }    
}
