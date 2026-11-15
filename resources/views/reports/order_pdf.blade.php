
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">

    <title>Order Report</title>

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
            width: 100%;
            margin-bottom: 15px;
            border-collapse: collapse;
        }

        .summary td {
            border: 1px solid #ccc;
            padding: 7px;
            font-weight: bold;
        }

        .section-title {
            font-size: 12px;
            font-weight: bold;
            margin-bottom: 8px;
        }

        table.orders {
            width: 100%;
            border-collapse: collapse;
        }

        table.orders th,
        table.orders td {
            border: 1px solid #777;
            padding: 6px;
            text-align: left;
        }

        table.orders th {
            background-color: #244062;
            color: white;
            text-align: center;
        }

        .order-number {
            font-weight: bold;
            text-align: center !important;
            vertical-align: middle;
            background-color: #edf2f7;
        }

        .number {
            text-align: right !important;
        }

        .footer {
            margin-top: 20px;
            text-align: right;
            font-size: 9px;
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
        ORDER REPORT
    </div>


    <!-- REPORT SUMMARY -->

    <div class="summary">

        <div class="section-title">
            Report Summary
        </div>

        <table>
            <tr>
                <td>
                    From Date:
                    {{ $filters['from_date'] ?? 'All' }}
                </td>

                <td>
                    To Date:
                    {{ $filters['to_date'] ?? 'All' }}
                </td>

                <td>
                    Total Orders:
                    {{ $totalOrders }}
                </td>
            </tr>

            <tr>
                <td>
                    Total Quantity:
                    {{ number_format($totalQuantity, 3) }}
                </td>

                <td colspan="2">
                    Total Amount:
                    {{ number_format($totalAmount, 2) }}
                </td>
            </tr>
        </table>

    </div>


    <!-- ORDER DETAILS -->

    <div class="section-title">
        Order Details
    </div>

    <table class="orders">

        <thead>
            <tr>
                <th>Order Number</th>
                <th>Product</th>
                <th>Category</th>
                <th>Sub Category</th>
                <th>Brand</th>
                <th>Quantity</th>
                <th>Unit Price</th>
                <th>Item Total</th>
            </tr>
        </thead>

        <tbody>

            @forelse($orders as $order)

                @php
                    $items = $order->items;
                    $rowspan = max($items->count(), 1);
                @endphp

                @if($items->isEmpty())

                    <tr>
                        <td class="order-number">
                            {{ $order->order_number }}
                        </td>

                        <td colspan="7">
                            No items found
                        </td>
                    </tr>

                @else

                    @foreach($items as $index => $item)

                        <tr>

                            @if($index === 0)

                                <td
                                    class="order-number"
                                    rowspan="{{ $rowspan }}"
                                >
                                    {{ $order->order_number }}
                                </td>

                            @endif

                            <td>
                                {{ $item->product_name }}
                            </td>

                            <td>
                                {{ $item->category_name ?? '-' }}
                            </td>

                            <td>
                                {{ $item->sub_category_name ?? '-' }}
                            </td>

                            <td>
                                {{ $item->brand_name ?? '-' }}
                            </td>

                            <td class="number">
                                {{ number_format($item->quantity, 3) }}
                            </td>

                            <td class="number">
                                {{ number_format($item->unit_price, 2) }}
                            </td>

                            <td class="number">
                                {{ number_format($item->line_total, 2) }}
                            </td>

                        </tr>

                    @endforeach

                @endif

            @empty

                <tr>
                    <td colspan="8" style="text-align:center;">
                        No order data found.
                    </td>
                </tr>

            @endforelse

        </tbody>

    </table>


    <!-- FOOTER -->

    <div class="footer">
        Generated at: {{ now()->format('d M Y h:i A') }}
    </div>

</body>
</html>
