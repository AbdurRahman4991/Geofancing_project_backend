
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
                    <strong>From Date:</strong>
                    {{ $filters['from_date'] }}
                </td>

                <td>
                    <strong>To Date:</strong>
                    {{ $filters['to_date'] }}
                </td>

                <td>
                    <strong>Total Visits:</strong>
                    {{ $summary->total_visits }}
                </td>

                <td>
                    <strong>Total Employees:</strong>
                    {{ $summary->total_employees }}
                </td>
            </tr>

            <tr>
                <td>
                    <strong>Total Markets:</strong>
                    {{ $summary->total_markets }}
                </td>

                <td>
                    <strong>Visited Markets:</strong>
                    {{ $summary->visited_markets }}
                </td>

                <td>
                    <strong>Not Visited:</strong>
                    {{ $summary->none_visited_markets }}
                </td>

                <td>
                    <strong>Completed Visits:</strong>
                    {{ $summary->completed_visits }}
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
                <th>Market</th>
                <th>Address</th>
                <th>Hierarchy</th>
                <th>Employee</th>
                <th>Visit Start</th>
                <th>Visit End</th>
                <th>Duration</th>
            </tr>
        </thead>

        <tbody>

            @forelse($markets as $index => $market)

                <tr>
                    <td>{{ $index + 1 }}</td>

                    <td>
                        {{ $market->market_name ?? 'N/A' }}
                    </td>

                    <td>
                        {{ $market->market_address ?? 'N/A' }}
                    </td>

                    <td>
                        {{ $market->hierarchy_level ?? 'N/A' }}
                        /
                        {{ $market->hierarchy_node ?? 'N/A' }}
                    </td>

                    <td>
                        {{ $market->employee_name ?? 'N/A' }}
                    </td>

                    <td>
                        {{ $market->visit_start_time ?? 'N/A' }}
                    </td>

                    <td>
                        {{ $market->visit_end_time ?? 'N/A' }}
                    </td>

                    <td>
                        {{ $market->duration ?? 'N/A' }}
                    </td>
                </tr>

            @empty

                <tr>
                    <td colspan="8" style="text-align:center;">
                        No Market Visit Records Found
                    </td>
                </tr>

            @endforelse

        </tbody>

    </table>

</body>
</html>
