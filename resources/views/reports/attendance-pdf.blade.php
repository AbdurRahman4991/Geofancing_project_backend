
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">

    <title>Attendance Report</title>

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

        .text-center {
            text-align: center !important;
        }

        .footer {
            margin-top: 15px;
            padding-top: 8px;
            border-top: 1px solid #ccc;
            text-align: right;
            font-size: 8px;
        }
    </style>
</head>

<body>

    <!-- ===================================== -->
    <!-- COMPANY INFORMATION -->
    <!-- ===================================== -->

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


    <!-- ===================================== -->
    <!-- REPORT TITLE -->
    <!-- ===================================== -->

    <div class="report-title">
        ATTENDANCE REPORT
    </div>


    <!-- ===================================== -->
    <!-- SUMMARY -->
    <!-- ===================================== -->

    <div class="summary">

        <div class="section-title">
            Report Summary
        </div>

        <table>

            <tr>
                <td>
                    <strong>From Date:</strong>
                    {{ $filters['from_date'] }}
                </td>

                <td>
                    <strong>To Date:</strong>
                    {{ $filters['to_date'] }}
                </td>

                <td>
                    <strong>Total Attendance:</strong>
                    {{ $summary->total_attendance ?? 0 }}
                </td>

                <td>
                    <strong>Checked In:</strong>
                    {{ $summary->checked_in ?? 0 }}
                </td>
            </tr>

            <tr>
                <td>
                    <strong>Checked Out:</strong>
                    {{ $summary->checked_out ?? 0 }}
                </td>

                <td>
                    <strong>Absent:</strong>
                    {{ $summary->absent ?? 0 }}
                </td>

                <td>
                    <strong>Leave:</strong>
                    {{ $summary->leave_count ?? 0 }}
                </td>

                <td>
                    <strong>Late:</strong>
                    {{ $summary->late_count ?? 0 }}
                </td>
            </tr>

        </table>

    </div>


    <!-- ===================================== -->
    <!-- ATTENDANCE DETAILS -->
    <!-- ===================================== -->

    <div class="section-title">
        Employee Attendance Details
    </div>

    <table class="details">

        <thead>
            <tr>
                <th>SL</th>
                <th>Employee</th>
                <th>Date</th>
                <th>Check In</th>
                <th>Check Out</th>
                <th>Work Hour</th>
                <th>Late</th>
                <th>Status</th>
                <th>Hierarchy Level</th>
                <th>Hierarchy Node</th>
            </tr>
        </thead>

        <tbody>

            @forelse($attendances as $index => $attendance)

                <tr>

                    <td class="text-center">
                        {{ $index + 1 }}
                    </td>

                    <td>
                        {{ $attendance->employee_name ?? 'N/A' }}
                        <br>
                        {{ $attendance->employee_email ?? '' }}
                    </td>

                    <td>
                        {{ $attendance->attendance_date ?? 'N/A' }}
                    </td>

                    <td>
                        {{ $attendance->check_in_time ?? 'N/A' }}

                        @if($attendance->check_in_latitude !== null &&
                            $attendance->check_in_longitude !== null)

                            <br>
                            <small>
                                ({{ $attendance->check_in_latitude }},
                                {{ $attendance->check_in_longitude }})
                            </small>

                        @endif
                    </td>

                    <td>
                        {{ $attendance->check_out_time ?? 'N/A' }}

                        @if($attendance->check_out_latitude !== null &&
                            $attendance->check_out_longitude !== null)

                            <br>
                            <small>
                                ({{ $attendance->check_out_latitude }},
                                {{ $attendance->check_out_longitude }})
                            </small>

                        @endif
                    </td>

                    <td>
                        {{ $attendance->work_hour ?? 'N/A' }}
                    </td>

                    <td>
                        {{ $attendance->late ?? 'N/A' }}
                    </td>

                    <td>
                        {{ $attendance->status ?? 'N/A' }}
                    </td>

                    <td>
                        {{ $attendance->hierarchy_level_name ?? 'N/A' }}
                    </td>

                    <td>
                        {{ $attendance->hierarchy_node_name ?? 'N/A' }}
                    </td>

                </tr>

            @empty

                <tr>
                    <td colspan="10" class="text-center">
                        No Attendance Records Found
                    </td>
                </tr>

            @endforelse

        </tbody>

    </table>


    <!-- ===================================== -->
    <!-- FOOTER -->
    <!-- ===================================== -->

    <div class="footer">

        Generated Date:
        {{ now()->format('d-m-Y h:i A') }}

        <br>

        Total Records:
        {{ count($attendances) }}

    </div>

</body>
</html>
