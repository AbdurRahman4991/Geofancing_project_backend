
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Monthly Employee Performance Report</title>

    <style>
        @page {
            margin: 20px 10px;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 7px;
            color: #333;
        }

        .company-header {
            text-align: center;
            margin-bottom: 15px;
            padding-bottom: 10px;
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

        .period {
            text-align: center;
            font-size: 10px;
            margin-bottom: 15px;
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
            font-size: 8px;
        }

        .section-title {
            font-size: 12px;
            font-weight: bold;
            margin-bottom: 8px;
        }

        .details {
            width: 100%;
            border-collapse: collapse;
        }

        .details th,
        .details td {
            border: 1px solid #333;
            padding: 4px 2px;
            text-align: center;
        }

        .details th {
            background-color: #eeeeee;
            font-weight: bold;
        }

        .employee {
            text-align: left !important;
        }

        .footer {
            margin-top: 15px;
            text-align: right;
            font-size: 8px;
        }

        tr {
            page-break-inside: avoid;
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
        MONTHLY PERFORMANCE REPORT
    </div>

    <div class="period">
        <strong>Report Period:</strong>
        {{ $filters['from_date'] ?? 'N/A' }}
        to
        {{ $filters['to_date'] ?? 'N/A' }}
    </div>


    <!-- REPORT SUMMARY -->

    <div class="summary">

        <div class="section-title">
            Report Summary
        </div>

        <table>

            @if(empty($filters['user_id']))

                <!-- ALL EMPLOYEE SUMMARY -->

                <tr>
                    <td>
                        <strong>Total Employees:</strong>
                        {{ $summary['total_employees'] ?? 0 }}
                    </td>

                    <td>
                        <strong>Total Present Days:</strong>
                        {{ $summary['total_present_days'] ?? 0 }}
                    </td>

                    <td>
                        <strong>Total Absent Days:</strong>
                        {{ $summary['total_absent_days'] ?? 0 }}
                    </td>

                    <td>
                        <strong>Total Late Days:</strong>
                        {{ $summary['total_late_days'] ?? 0 }}
                    </td>
                </tr>

                <tr>
                    <td>
                        <strong>Total Market Visits:</strong>
                        {{ $summary['total_market_visits'] ?? 0 }}
                    </td>

                    <td>
                        <strong>Total Orders:</strong>
                        {{ $summary['total_orders'] ?? 0 }}
                    </td>

                    <td>
                        <strong>Total Quantity:</strong>
                        {{ $summary['total_quantity'] ?? 0 }}
                    </td>

                    <td>
                        <strong>Total Sales:</strong>
                        {{ number_format($summary['total_sales'] ?? 0, 2) }}
                    </td>
                </tr>

            @else

                <!-- INDIVIDUAL EMPLOYEE SUMMARY -->

                <tr>
                    <td>
                        <strong>Present Days:</strong>
                        {{ $summary['total_present_days'] ?? 0 }}
                    </td>

                    <td>
                        <strong>Absent Days:</strong>
                        {{ $summary['total_absent_days'] ?? 0 }}
                    </td>

                    <td>
                        <strong>Late Days:</strong>
                        {{ $summary['total_late_days'] ?? 0 }}
                    </td>

                    <td>
                        <strong>Attendance Rate:</strong>
                        {{ $summary['attendance_rate'] ?? 0 }}%
                    </td>
                </tr>

                <tr>
                    <td>
                        <strong>Market Visits:</strong>
                        {{ $summary['total_market_visits'] ?? 0 }}
                    </td>

                    <td>
                        <strong>Total Orders:</strong>
                        {{ $summary['total_orders'] ?? 0 }}
                    </td>

                    <td>
                        <strong>Total Quantity:</strong>
                        {{ $summary['total_quantity'] ?? 0 }}
                    </td>

                    <td>
                        <strong>Total Sales:</strong>
                        {{ number_format($summary['total_sales'] ?? 0, 2) }}
                    </td>
                </tr>

            @endif

        </table>

    </div>


    <!-- EMPLOYEE PERFORMANCE DETAILS -->

    <div class="section-title">

        @if(empty($filters['user_id']))
            Employee Performance Details
        @else
            Individual Employee Performance Details
        @endif

    </div>

    <table class="details">

        <thead>

            <tr>
                <th>SL</th>
                <th>Employee ID</th>
                <th>Employee</th>

                <th>Target</th>
                <th>Achievement</th>
                <th>Achievement %</th>

                <th>Present</th>
                <th>Absent</th>
                <th>Late</th>
                <th>Attendance %</th>

                <th>Visits</th>
                <th>Completed</th>
                <th>Incomplete</th>
                <th>Customers</th>

                <th>Work Hours</th>
                <th>Avg Hours</th>

                <th>Orders</th>
                <th>Quantity</th>
                <th>Sales</th>
                <th>Avg Order</th>
            </tr>

        </thead>

        <tbody>

            @forelse($data as $index => $row)

                @php

                    $target = $row['target'] ?? 0;

                    $achievement = $row['sales']['total_sales'] ?? 0;

                    $achievementRate = $target > 0
                        ? ($achievement / $target) * 100
                        : 0;

                @endphp

                <tr>

                    <td>{{ $index + 1 }}</td>

                    <td>
                        {{ $row['employee_id'] ?? 'N/A' }}
                    </td>

                    <td class="employee">
                        {{ $row['employee_name'] ?? 'N/A' }}
                    </td>


                    <!-- TARGET -->

                    <td>
                        {{ number_format($target, 2) }}
                    </td>

                    <!-- ACHIEVEMENT -->

                    <td>
                        {{ number_format($achievement, 2) }}
                    </td>

                    <!-- ACHIEVEMENT PERCENTAGE -->

                    <td>
                        {{ number_format($achievementRate, 2) }}%
                    </td>


                    <!-- ATTENDANCE -->

                    <td>
                        {{ $row['attendance']['present_days'] ?? 0 }}
                    </td>

                    <td>
                        {{ $row['attendance']['absent_days'] ?? 0 }}
                    </td>

                    <td>
                        {{ $row['attendance']['late_days'] ?? 0 }}
                    </td>

                    <td>
                        {{ $row['attendance']['attendance_rate'] ?? 0 }}%
                    </td>


                    <!-- MARKET VISITS -->

                    <td>
                        {{ $row['field_visits']['total_visits'] ?? 0 }}
                    </td>

                    <td>
                        {{ $row['field_visits']['completed_visits'] ?? 0 }}
                    </td>

                    <td>
                        {{ $row['field_visits']['incomplete_visits'] ?? 0 }}
                    </td>

                    <td>
                        {{ $row['field_visits']['unique_customers'] ?? 0 }}
                    </td>


                    <!-- WORKING HOURS -->

                    <td>
                        {{ $row['working_hours']['total_hours'] ?? 0 }}
                    </td>

                    <td>
                        {{ $row['working_hours']['average_hours'] ?? 0 }}
                    </td>


                    <!-- SALES -->

                    <td>
                        {{ $row['sales']['total_orders'] ?? 0 }}
                    </td>

                    <td>
                        {{ $row['sales']['total_quantity'] ?? 0 }}
                    </td>

                    <td>
                        {{ number_format($row['sales']['total_sales'] ?? 0, 2) }}
                    </td>

                    <td>
                        {{ number_format($row['sales']['average_order_value'] ?? 0, 2) }}
                    </td>

                </tr>

            @empty

                <tr>
                    <td colspan="20" style="text-align:center;">
                        No Employee Performance Records Found
                    </td>
                </tr>

            @endforelse

        </tbody>

    </table>


    <!-- FOOTER -->

    <div class="footer">
        Generated on: {{ now()->format('d-m-Y h:i A') }}
    </div>

</body>
</html>
