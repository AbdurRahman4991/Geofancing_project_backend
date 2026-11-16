
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">

    <title>Employee Registration Report</title>

    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 9px;
            color: #333;
        }

        .company-header {
            text-align: center;
            margin-bottom: 20px;
            padding-bottom: 12px;
            border-bottom: 2px solid #333;
        }

        .company-name {
            font-size: 21px;
            font-weight: bold;
            margin-bottom: 8px;
        }

        .company-info {
            font-size: 10px;
            line-height: 1.7;
        }

        .report-title {
            text-align: center;
            font-size: 16px;
            font-weight: bold;
            margin: 15px 0;
        }

        .summary {
            margin-bottom: 15px;
        }

        .summary table {
            width: 100%;
            border-collapse: collapse;
        }

        .summary td {
            border: 1px solid #ccc;
            padding: 7px;
        }

        .details {
            width: 100%;
            border-collapse: collapse;
        }

        .details th,
        .details td {
            border: 1px solid #333;
            padding: 6px;
            text-align: left;
        }

        .details th {
            background-color: #eeeeee;
        }

        .section-title {
            font-size: 12px;
            font-weight: bold;
            margin-bottom: 8px;
        }

        .registered {
            color: green;
            font-weight: bold;
        }

        .unregistered {
            color: red;
            font-weight: bold;
        }
    </style>
</head>

<body>

    <!-- COMPANY INFORMATION -->

    <div class="company-header">

        <div class="company-name">
            {{ $company->company_name ?? 'Company Name' }}
        </div>

        <div class="company-info">

            Address:
            {{ $company->address ?? 'N/A' }}

            <br>

            Phone:
            {{ $company->phone ?? 'N/A' }}

            <br>

            Email:
            {{ $company->email ?? 'N/A' }}

        </div>

    </div>


    <!-- REPORT TITLE -->

    <div class="report-title">
        EMPLOYEE REGISTRATION REPORT
    </div>


    <!-- SUMMARY -->

    <div class="summary">

        <div class="section-title">
            Report Summary
        </div>

        <table>
            <tr>
                <td>
                    <strong>Report Date:</strong>
                    {{ now()->format('d M Y') }}
                </td>

                <td>
                    <strong>Total Employees:</strong>
                    {{ $summary['total_employees'] }}
                </td>

                <td>
                    <strong>Registered Employees:</strong>
                    {{ $summary['registered_employees'] }}
                </td>

                <td>
                    <strong>Unregistered Employees:</strong>
                    {{ $summary['unregistered_employees'] }}
                </td>
            </tr>

            <tr>
                <td>
                    <strong>Registration Rate:</strong>
                    {{ $summary['registration_rate'] }}%
                </td>

                <td>
                    <strong>Registration Filter:</strong>
                    {{ ucfirst($filters['registration_status'] ?? 'both') }}
                </td>

                <td>
                    <strong>Employee Status:</strong>
                    {{ ucfirst($filters['employee_status'] ?? 'All') }}
                </td>

                <td>
                    <strong>Search:</strong>
                    {{ $filters['search'] ?? 'All Employees' }}
                </td>
            </tr>
        </table>

    </div>


    <!-- EMPLOYEE REGISTRATION DETAILS -->

    <div class="section-title">
        Employee Registration Details
    </div>

    <table class="details">

        <thead>
            <tr>
                <th>SL</th>
                <th>Employee Code</th>
                <th>Employee Name</th>
                <th>Phone</th>
                <th>Email</th>
                <th>Employee Status</th>
                <th>Registration Status</th>
                <th>User ID</th>
                <th>User Status</th>
                <th>Registered At</th>
            </tr>
        </thead>

        <tbody>

            @forelse($data as $index => $employee)

                <tr>
                    <td>{{ $index + 1 }}</td>

                    <td>
                        {{ $employee->employee_id ?? 'N/A' }}
                    </td>

                    <td>
                        {{ $employee->employee_name ?? 'N/A' }}
                    </td>

                    <td>
                        {{ $employee->phone ?? 'N/A' }}
                    </td>

                    <td>
                        {{ $employee->email ?? 'N/A' }}
                    </td>

                    <td>
                        {{ ucfirst($employee->employee_status ?? 'N/A') }}
                    </td>

                    <td class="{{ $employee->user_id ? 'registered' : 'unregistered' }}">
                        {{ $employee->registration_status }}
                    </td>

                    <td>
                        {{ $employee->user_id ?? 'N/A' }}
                    </td>

                    <td>
                        {{ ucfirst($employee->user_status ?? 'N/A') }}
                    </td>

                    <td>
                        {{ $employee->registered_at
                            ? \Carbon\Carbon::parse($employee->registered_at)->format('d M Y h:i A')
                            : 'N/A'
                        }}
                    </td>
                </tr>

            @empty

                <tr>
                    <td colspan="10" style="text-align:center;">
                        No Employee Registration Records Found
                    </td>
                </tr>

            @endforelse

        </tbody>

    </table>


    <div style="margin-top: 10px; text-align: right;">
        <strong>Total Records:</strong> {{ $data->count() }}
    </div>

</body>
</html>
