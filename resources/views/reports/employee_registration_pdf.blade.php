
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Employee Registration Report</title>

    <style>
        @page {
            margin: 25px 25px;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 9px;
            color: #333;
            line-height: 1.5;
        }

        /* COMPANY HEADER */
    .company-header {
        text-align: center;
        padding-bottom: 12px;
        margin-bottom: 15px;
        border-bottom: 2px solid #333;
    }

    .company-name {
        font-size: 21px;
        font-weight: bold;
        margin-bottom: 8px;
        text-transform: uppercase;
    }

    .company-info {
        font-size: 9px;
        line-height: 1.8;
        color: #555;
        text-align: center;
    }

    .company-info p {
        margin: 2px 0;
    }

        /* REPORT TITLE */
        .report-title {
            text-align: center;
            font-size: 16px;
            font-weight: bold;
            margin: 15px 0;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .generated-date {
            text-align: right;
            font-size: 8px;
            color: #666;
            margin-bottom: 12px;
        }

        /* SECTION TITLE */
        .section-title {
            font-size: 11px;
            font-weight: bold;
            margin-bottom: 8px;
            padding-bottom: 5px;
            border-bottom: 1px solid #999;
        }

        /* SUMMARY */
        .summary {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 18px;
        }

        .summary td {
            width: 25%;
            border: 1px solid #ccc;
            padding: 8px;
            vertical-align: middle;
        }

        .summary-label {
            font-size: 8px;
            color: #666;
            font-weight: bold;
        }

        .summary-value {
            font-size: 14px;
            font-weight: bold;
            margin-top: 3px;
            color: #222;
        }

        /* FILTERS */
        .filter-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 18px;
        }

        .filter-table td {
            border: 1px solid #ddd;
            padding: 6px;
            font-size: 8px;
        }

        .filter-label {
            font-weight: bold;
            color: #555;
        }

        /* EMPLOYEE TABLE */
        .details {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
        }

        .details th {
            background-color: #e9e9e9;
            color: #222;
            font-weight: bold;
            text-align: center;
            border: 1px solid #777;
            padding: 7px 4px;
            font-size: 8px;
        }

        .details td {
            border: 1px solid #aaa;
            padding: 6px 4px;
            font-size: 8px;
            vertical-align: middle;
        }

        .details tbody tr:nth-child(even) {
            background-color: #f7f7f7;
        }

        .text-center {
            text-align: center;
        }

        .registered {
            color: #16803c;
            font-weight: bold;
        }

        .unregistered {
            color: #dc2626;
            font-weight: bold;
        }

        .active {
            color: #16803c;
            font-weight: bold;
        }

        .inactive {
            color: #d97706;
            font-weight: bold;
        }

        .terminated {
            color: #dc2626;
            font-weight: bold;
        }

        /* FOOTER */
        .footer {
            margin-top: 12px;
            padding-top: 7px;
            border-top: 1px solid #ccc;
            text-align: right;
            font-size: 8px;
            color: #555;
        }

        .no-record {
            text-align: center;
            padding: 15px;
            font-weight: bold;
            color: #777;
        }
    </style>
</head>

<body>

    {{-- COMPANY HEADER --}}

    <div class="company-header">

    <div class="company-name">
        {{ $company->company_name ?? 'Company Name' }}
    </div>

    <div class="company-info">
        <p>
            <strong>Address:</strong>
            {{ $company->address ?? 'N/A' }}
        </p>

        <p>
            <strong>Phone:</strong>
            {{ $company->phone ?? 'N/A' }}
        </p>

        <p>
            <strong>Email:</strong>
            {{ $company->email ?? 'N/A' }}
        </p>
    </div>

</div>


    {{-- REPORT TITLE --}}

    <div class="report-title">
        Employee Registration Report
    </div>

    <div class="generated-date">
        Generated Date:
        {{ now()->format('d M Y h:i A') }}
    </div>


    {{-- REPORT SUMMARY --}}

    <div class="section-title">
        Report Summary
    </div>

    <table class="summary">
        <tr>
            <td>
                <div class="summary-label">Total Employees</div>
                <div class="summary-value">
                    {{ $summary['total_employees'] ?? 0 }}
                </div>
            </td>

            <td>
                <div class="summary-label">Registered Employees</div>
                <div class="summary-value registered">
                    {{ $summary['registered_employees'] ?? 0 }}
                </div>
            </td>

            <td>
                <div class="summary-label">Unregistered Employees</div>
                <div class="summary-value unregistered">
                    {{ $summary['unregistered_employees'] ?? 0 }}
                </div>
            </td>

            <td>
                <div class="summary-label">Registration Rate</div>
                <div class="summary-value">
                    {{ $summary['registration_rate'] ?? 0 }}%
                </div>
            </td>
        </tr>
    </table>    


    {{-- EMPLOYEE DETAILS --}}

    <div class="section-title">
        Employee Registration Details
    </div>

    <table class="details">

        <thead>
            <tr>
                <th width="4%">SL</th>
                <th width="9%">Employee Code</th>
                <th width="13%">Employee Name</th>
                <th width="9%">Phone</th>
                <th width="15%">Email</th>
                <th width="9%">Employee Status</th>
                <th width="10%">Registration</th>
                <th width="6%">User ID</th>
                <th width="8%">User Status</th>
                <th width="17%">Registered At</th>
            </tr>
        </thead>

        <tbody>

            @forelse($data as $index => $employee)

                <tr>
                    <td class="text-center">
                        {{ $index + 1 }}
                    </td>

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

                    <td class="text-center">
                        <span class="{{ strtolower($employee->employee_status ?? '') }}">
                            {{ ucfirst($employee->employee_status ?? 'N/A') }}
                        </span>
                    </td>

                    <td class="text-center">
                        <span class="{{ $employee->user_id ? 'registered' : 'unregistered' }}">
                            {{ $employee->registration_status ?? 'N/A' }}
                        </span>
                    </td>

                    <td class="text-center">
                        {{ $employee->user_id ?? 'N/A' }}
                    </td>

                    <td class="text-center">
                        {{ ucfirst($employee->user_status ?? 'N/A') }}
                    </td>

                    <td class="text-center">
                        {{ $employee->registered_at
                            ? \Carbon\Carbon::parse($employee->registered_at)->format('d M Y h:i A')
                            : 'N/A'
                        }}
                    </td>
                </tr>

            @empty

                <tr>
                    <td colspan="10" class="no-record">
                        No Employee Registration Records Found
                    </td>
                </tr>

            @endforelse

        </tbody>

    </table>


    {{-- FOOTER --}}

    <div class="footer">
        <strong>Total Records:</strong>
        {{ $data->count() }}
    </div>

</body>
</html>
