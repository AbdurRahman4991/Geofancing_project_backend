<?php
namespace App\Services\Employee;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Traits\ScopesCompanyAccess;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

class EmployeeService
{
    use ScopesCompanyAccess;
       

public function index(Request $request)
{
//     $user = auth()->user();

// dd([
//     'user_id' => $user->id,
//     'roles' => $user->getRoleNames(),
//     'permissions' => $user->getAllPermissions()->pluck('name'),
//     'can_employee_view' => $user->can('employee.view'),
// ]);

   
    if (!auth()->user()->can('employee.view')) {
        abort(403, 'You do not have permission to view employees.');
    }

    $query = Employee::with([
        'company:id,company_name'
    ]);

    $this->scopeToCurrentCompany($query);

    // Login user's company অনুযায়ী filter   

    // 🔍 Search
    if ($request->filled('search')) {
        $search = $request->search;

        $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
                ->orWhere('employee_id', 'like', "%{$search}%")
                ->orWhere('phone', 'like', "%{$search}%");
        });
    }

    // 🏢 Department Filter
    if ($request->filled('department')) {
        $query->where(
            'department',
            'like',
            "%{$request->department}%"
        );
    }

    // ↕️ Sorting
    $allowedSortColumns = [
        'id',
        'name',
        'employee_id',
        'phone',
        'department',
        'created_at',
    ];

    $orderBy = $request->get('orderBy', 'id');

    if (!in_array($orderBy, $allowedSortColumns)) {
        $orderBy = 'id';
    }

    $order = strtolower(
        $request->get('order', 'desc')
    );

    if (!in_array($order, ['asc', 'desc'])) {
        $order = 'desc';
    }

    $query->orderBy($orderBy, $order);

    // 📄 Pagination
    $employees = $query->paginate(
        $request->get('per_page', 10)
    );

    return response()->json([
        'status'  => 200,
        'message' => 'Employee list retrieved successfully',
        'data'    => $employees,
    ]);
}

    public function store(Request $request)
    {
        if (!auth()->user()->can('employee.create')) {
            abort(403, 'You do not have permission to create employees.');
        }
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'employee_id' => 'required|string|unique:employees,employee_id',
            'company_id' => 'nullable|exists:companies,id',

            'phone' => 'required|string|max:20',
            'status' => 'required|in:active,inactive,terminated',
            'nature_of_employment' => 'required|string',

            'department' => 'nullable|string|max:255',
            'unit' => 'nullable|string|max:255',
            'division' => 'nullable|string|max:255',
            'designation' => 'nullable|string|max:255',
            'reporting_person' => 'nullable|string|max:255',

            'date_of_joining' => 'required|date',

            'email' => 'nullable|email',
            'dob' => 'nullable|date',
            'section_info' => 'nullable|string|max:255',
        ]);

        return Employee::create($data);
    }
  
    public function syncEmployees(Request $request)
    {
        return $this->importEmployeesFromFile($request);
        
    }

    private function importEmployeesFromFile(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt,xlsx,xls|max:10240',
        ]);

        try {
            $rows = IOFactory::load($request->file('file')->getRealPath())
                ->getActiveSheet()
                ->toArray(null, true, true, false);
        } catch (\Throwable $exception) {
            return response()->json([
                'success' => false,
                'message' => 'The uploaded file could not be read. Please use a valid CSV or Excel file.',
            ], 422);
        }

        if (count($rows) < 2) {
            return response()->json([
                'success' => false,
                'message' => 'The file must contain a header row and at least one employee row.',
            ], 422);
        }

        $aliases = [
            'employeeid' => 'employee_id',
            'name' => 'name',
            'companyid' => 'company_id',
            'buid' => 'company_id',
            'businessunitid' => 'company_id',
            'phone' => 'phone',
            'mobileno' => 'phone',
            'status' => 'status',
            'isactive' => 'status',
            'natureofemployment' => 'nature_of_employment',
            'department' => 'department',
            'unit' => 'unit',
            'businessunitname' => 'unit',
            'dateofjoining' => 'date_of_joining',
            'division' => 'division',
            'divisionname' => 'division',
            'designation' => 'designation',
            'reportingperson' => 'reporting_person',
            'email' => 'email',
            'dob' => 'dob',
            'sectioninfo' => 'section_info',
            'sectionname' => 'section_info',
        ];

        $headers = [];
        foreach ($rows[0] as $index => $header) {
            $key = strtolower(preg_replace('/[^a-z0-9]/', '', (string) $header));
            $headers[$index] = $aliases[$key] ?? null;
        }

        $employees = [];
        $errors = [];
        foreach (array_slice($rows, 1) as $rowIndex => $row) {
            $item = [];
            foreach ($headers as $index => $field) {
                if ($field !== null) {
                    $item[$field] = is_string($row[$index] ?? null)
                        ? trim($row[$index])
                        : ($row[$index] ?? null);
                }
            }

            if (empty(array_filter($item, fn ($value) => $value !== null && $value !== ''))) {
                continue;
            }

            foreach (['employee_id', 'phone'] as $stringField) {
                if (isset($item[$stringField])) {
                    $item[$stringField] = (string) $item[$stringField];
                }
            }

            if (isset($item['status']) && $item['status'] !== '') {
                $status = strtolower((string) $item['status']);
                $item['status'] = in_array($status, ['1', 'true', 'yes', 'active'], true)
                    ? 'active'
                    : (in_array($status, ['terminated'], true) ? 'terminated' : 'inactive');
            } else {
                $item['status'] = 'active';
            }

            foreach (['date_of_joining', 'dob'] as $dateField) {
                if (isset($item[$dateField]) && is_numeric($item[$dateField])) {
                    $item[$dateField] = ExcelDate::excelToDateTimeObject((float) $item[$dateField])->format('Y-m-d');
                }
            }

            $validator = Validator::make($item, [
                'employee_id' => 'required|string|max:255',
                'name' => 'required|string|max:255',
                'company_id' => 'required|integer|exists:companies,id',
                'phone' => 'nullable|string|max:255',
                'status' => 'required|in:active,inactive,terminated',
                'nature_of_employment' => 'nullable|string|max:255',
                'department' => 'nullable|string|max:255',
                'unit' => 'nullable|string|max:255',
                'date_of_joining' => 'nullable|date',
                'division' => 'nullable|string|max:255',
                'designation' => 'nullable|string|max:255',
                'reporting_person' => 'nullable|string|max:255',
                'email' => 'nullable|email|max:255',
                'dob' => 'nullable|date',
                'section_info' => 'nullable|string|max:255',
            ]);

            if ($validator->fails()) {
                $errors['row_' . ($rowIndex + 2)] = $validator->errors()->all();
                continue;
            }

            $employees[] = $validator->validated();
        }

        if ($errors) {
            return response()->json([
                'success' => false,
                'message' => 'Some rows contain invalid or missing data. No employees were imported.',
                'errors' => $errors,
            ], 422);
        }

        DB::transaction(function () use ($employees) {
            foreach ($employees as $item) {
                Employee::updateOrCreate(
                    ['employee_id' => $item['employee_id']],
                    $item
                );
            }
        });

        return response()->json([
            'success' => true,
            'message' => 'Employees imported successfully.',
            'total' => count($employees),
        ]);
    }
    public function show($id)
    {
        if (!auth()->user()->can('employee.edit')) {
        abort(403, 'You do not have permission to view employees.');
        }
        $query = Employee::with(['company:id,company_name']);
        $this->scopeToCurrentCompany($query);

        return $query->findOrFail($id);
    }

    public function update(Request $request, $id)
    {
        if (!auth()->user()->can('employee.update')) {
        abort(403, 'You do not have permission to update employees.');
        }

        $employee = Employee::findOrFail($id);

        $data = $request->validate([
            'employee_id' => 'required|string|unique:employees,employee_id,' . $employee->id,
             'name' => 'required|string|max:255',
            'company_id' => 'nullable|exists:companies,id',
            'phone' => 'required|string|max:20',
            'status' => 'required|in:active,inactive,terminated',
            'nature_of_employment' => 'required|string',
            'department' => 'nullable|string|max:255',
            'unit' => 'nullable|string|max:255',
            'division' => 'nullable|string|max:255',
            'designation' => 'nullable|string|max:255',
            'reporting_person' => 'nullable|string|max:255',
            'date_of_joining' => 'required|date',
            'email' => 'nullable|email',
            'dob' => 'nullable|date',
            'section_info' => 'nullable|string|max:255',
        ]);

        $employee->update($data);
        return $employee;
    }

    public function destroy($id)
    {
        if (!auth()->user()->can('employee.delete')) {
        abort(403, 'You do not have permission to delete employees.');
        }
        $employee = Employee::findOrFail($id);
        return $employee->delete();
    }
}
