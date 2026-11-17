
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">

    <title>Market Visit Report</title>

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
            font-size: 17px;
            font-weight: bold;
            margin: 15px 0;
        }

        .summary {
            margin-bottom: 18px;
        }

        .summary table {
            width: 100%;
            border-collapse: collapse;
        }

        .summary td {
            border: 1px solid #ccc;
            padding: 8px;
            width: 25%;
        }

        .details {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        .details th,
        .details td {
            border: 1px solid #333;
            padding: 6px;
            text-align: left;
            vertical-align: top;
        }

        .details th {
            background-color: #eeeeee;
            font-size: 8px;
        }

        .section-title {
            font-size: 12px;
            font-weight: bold;
            margin-bottom: 8px;
        }

        .visited {
            color: green;
            font-weight: bold;
        }

        .not-visited {
            color: red;
            font-weight: bold;
        }

        .footer {
            margin-top: 15px;
            text-align: right;
            font-weight: bold;
        }

        .text-center {
            text-align: center;
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
        MARKET VISIT REPORT
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
                    <strong>From Date:</strong>
                    {{ $filters['from_date'] ?? 'N/A' }}
                </td>

                <td>
                    <strong>To Date:</strong>
                    {{ $filters['to_date'] ?? 'N/A' }}
                </td>

                <td>
                    <strong>Total Visits:</strong>
                    {{ $summary->total_visits ?? 0 }}
                </td>
            </tr>

            <tr>
                <td>
                    <strong>Total Employees:</strong>
                    {{ $summary->total_employees ?? 0 }}
                </td>

                <td>
                    <strong>Total Markets:</strong>
                    {{ $summary->total_markets ?? 0 }}
                </td>

                <td>
                    <strong>Visited Markets:</strong>
                    {{ $summary->visited_markets ?? 0 }}
                </td>

                <td>
                    <strong>Not Visited Markets:</strong>
                    {{ $summary->none_visited_markets ?? 0 }}
                </td>
            </tr>

            <tr>
                <td>
                    <strong>Completed Visits:</strong>
                    {{ $summary->completed_visits ?? 0 }}
                </td>

                <td>
                    <strong>Active Visits:</strong>
                    {{ $summary->active_visits ?? 0 }}
                </td>

                <td>
                    <strong>Total Duration:</strong>
                    {{ $summary->total_duration ?? 0 }}
                </td>

                <td>
                    <strong>Visit Status:</strong>
                    {{ ucfirst($filters['visit_status'] ?? 'all') }}
                </td>
            </tr>

        </table>

    </div>


    <!-- MARKET VISIT DETAILS -->

    <div class="section-title">
        Market Visit Details
    </div>

    <table class="details">

        <thead>

            <tr>
                <th>SL</th>
                <th>Market Name</th>
                <th>Market Address</th>
                <th>Hierarchy Level</th>
                <th>Hierarchy Node</th>
                <th>Employee Name</th>
                <th>Visit Status</th>
                <th>Visit Start</th>
                <th>Visit End</th>
                <th>Duration</th>
                <th>Check-in Location</th>
                <th>Check-out Location</th>
            </tr>

        </thead>

        <tbody>

            @forelse($markets as $index => $market)

                <tr>

                    <td>
                        {{ $index + 1 }}
                    </td>

                    <td>
                        {{ $market->market_name ?? 'N/A' }}
                    </td>

                    <td>
                        {{ $market->market_address ?? 'N/A' }}
                    </td>

                    <td>
                        {{ $market->hierarchy_level ?? 'N/A' }}
                    </td>

                    <td>
                        {{ $market->hierarchy_node ?? 'N/A' }}
                    </td>

                    <td>
                        {{ $market->employee_name ?? 'N/A' }}
                    </td>

                    <td class="{{ $market->visit_id ? 'visited' : 'not-visited' }}">

                        @if($market->visit_id)

                            @if($market->visit_end_time)
                                Completed
                            @else
                                Active
                            @endif

                        @else
                            Not Visited
                        @endif

                    </td>

                    <td>
                        {{ $market->visit_start_time
                            ? \Carbon\Carbon::parse($market->visit_start_time)->format('d M Y h:i A')
                            : 'N/A'
                        }}
                    </td>

                    <td>
                        {{ $market->visit_end_time
                            ? \Carbon\Carbon::parse($market->visit_end_time)->format('d M Y h:i A')
                            : 'N/A'
                        }}
                    </td>

                    <td>
                        {{ $market->duration ?? 'N/A' }}
                    </td>

                    <td>
                        @if($market->check_in_latitude && $market->check_in_longitude)

                            Lat: {{ $market->check_in_latitude }}
                            <br>
                            Long: {{ $market->check_in_longitude }}

                        @else
                            N/A
                        @endif
                    </td>

                    <td>
                        @if($market->check_out_latitude && $market->check_out_longitude)

                            Lat: {{ $market->check_out_latitude }}
                            <br>
                            Long: {{ $market->check_out_longitude }}

                        @else
                            N/A
                        @endif
                    </td>

                </tr>

            @empty

                <tr>
                    <td colspan="12" class="text-center">
                        No Market Visit Records Found
                    </td>
                </tr>

            @endforelse

        </tbody>

    </table>


    <!-- TOTAL RECORDS -->

    <div class="footer">

        Total Records: {{ $markets->count() }}

    </div>

</body>
</html>
