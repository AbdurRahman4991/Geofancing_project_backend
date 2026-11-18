<?php

namespace App\Http\Controllers\Api\Report;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

class OrderReportController extends Controller
{

    public function index(Request $request)
    {
        $this->authorizeAction('view');
        $companyId = auth()->user()->company_id;
                

        if (!$companyId || !is_numeric($companyId)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid company information.',
            ], 403);
        }

        $companyId = (int) $companyId;

        /*
        |--------------------------------------------------------------------------
        | Validation
        |--------------------------------------------------------------------------
        */

        $validated = $request->validate([
            'from_date' => [
                'nullable',
                'date',
            ],

            'to_date' => [
                'nullable',
                'date',
                'after_or_equal:from_date',
            ],

            'category_id' => [
                'nullable',
                'integer',
                Rule::exists('categories', 'id')
                    ->where(fn ($q) => $q->where('company_id', $companyId)),
            ],

            'sub_category_id' => [
                'nullable',
                'integer',
                Rule::exists('sub_categories', 'id')
                    ->where(fn ($q) => $q->where('company_id', $companyId)),
            ],

            'brand_id' => [
                'nullable',
                'integer',
                Rule::exists('brands', 'id')
                    ->where(fn ($q) => $q->where('company_id', $companyId)),
            ],

            'product_id' => [
                'nullable',
                'integer',
                Rule::exists('products', 'id')
                    ->where(fn ($q) => $q->where('company_id', $companyId)),
            ],

            'hierarchy_level_id' => [
                'nullable',
                'integer',
                Rule::exists('hierarchy_levels', 'id')
                    ->where(fn ($q) => $q->where('company_id', $companyId)),
            ],

            'hierarchy_node_id' => [
                'nullable',
                'integer',
                Rule::exists('hierarchy_nodes', 'id')
                    ->where(fn ($q) => $q->where('company_id', $companyId)),
            ],

            'user_id' => [
                'nullable',
                'integer',
                Rule::exists('users', 'id')
                    ->where(fn ($q) => $q->where('company_id', (string) $companyId)),
            ],

            'geofence_id' => [
                'nullable',
                'integer',
                Rule::exists('geofences', 'id')
                    ->where(fn ($q) => $q->where('company_id', $companyId)),
            ],

            'status' => [
                'nullable',
                'string',
                'max:50',
            ],

            'per_page' => [
                'nullable',
                'integer',
                'min:1',
                'max:100',
            ],
        ]);

        /*
        |--------------------------------------------------------------------------
        | Base Order Query
        |--------------------------------------------------------------------------
        */

        $query = DB::table('orders')
            ->leftJoin('users', 'users.id', '=', 'orders.created_by')
            ->leftJoin('geofences', function ($join) {
                $join->on('geofences.id', '=', 'orders.geofence_id')
                    ->on('geofences.company_id', '=', 'orders.company_id');
            })
            ->where('orders.company_id', $companyId);

        /*
        |--------------------------------------------------------------------------
        | Apply Filters
        |--------------------------------------------------------------------------
        */

        $this->applyFilters($query, $validated, $companyId);

        /*
        |--------------------------------------------------------------------------
        | Order Summary
        |--------------------------------------------------------------------------
        */

        $summary = (clone $query)
            ->selectRaw('
                COUNT(DISTINCT orders.id) as total_orders,
                COALESCE(SUM(orders.total), 0) as total_amount,
                COUNT(DISTINCT orders.created_by) as total_employees
            ')
            ->first();

        /*
         * Total quantity of all items belonging to the matching orders.
         */
        $quantitySummary = (clone $query)
            ->join('order_items as summary_items', 'summary_items.order_id', '=', 'orders.id')
            ->selectRaw('COALESCE(SUM(summary_items.quantity), 0) as total_quantity')
            ->value('total_quantity');


        // =====================================
        // EXPORT PDF / EXCEL
        // =====================================

        if ($request->export === 'excel') {

            $orders = (clone $query)
                ->select([
                    'orders.id',
                    'orders.order_number',
                    'orders.created_at',
                    'orders.status',
                    'orders.total',
                    'orders.created_by',
                    'orders.geofence_id',
                    'users.name as employee_name',
                    'geofences.firm_name',
                ])
                ->orderBy('orders.created_at')
                ->get();

            $orders = $this->loadOrderItems($orders, $companyId);

            return Excel::download(
                new OrderReportExport($orders),
                'order_report.xlsx'
            );
        }


        if ($request->export === 'pdf') {

    $orders = (clone $query)
        ->select([
            'orders.id',
            'orders.order_number',
            'orders.created_at',
            'orders.status',
            'orders.total',
            'orders.created_by',
            'orders.geofence_id',
            'users.name as employee_name',
            'geofences.firm_name',
        ])
        ->orderBy('orders.created_at')
        ->get();

    // Load order items
    $orders = $this->loadOrderItems($orders, $companyId);

    // Summary calculation
    $totalOrders = $orders->count();

    $totalQuantity = $orders->sum(function ($order) {
        return $order->items->sum('quantity');
    });

    $totalAmount = $orders->sum(function ($order) {
        return $order->items->sum('line_total');
    });

    $company = DB::table('companies')
    ->where('id', $companyId)
    ->first();

    $pdf = Pdf::loadView('reports.order_pdf', [
        'company'       => $company,
        'orders' => $orders,
        'filters' => $request->all(),
        'totalOrders' => $totalOrders,
        'totalQuantity' => $totalQuantity,
        'totalAmount' => $totalAmount,
    ]);

    $pdf->setPaper('a4', 'landscape');

    return $pdf->download('order_report.pdf');
}          

        /*
        |--------------------------------------------------------------------------
        | Order Pagination
        |--------------------------------------------------------------------------
        */

        $orderQuery = clone $query;

        $orders = $orderQuery
            ->select([
                'orders.id',
                'orders.company_id',
                'orders.order_number',
                'orders.created_at',
                'orders.status',
                'orders.total',
                'orders.notes',
                'orders.created_by',
                'orders.geofence_id',

                'users.name as employee_name',
                'users.email as employee_email',

                'geofences.firm_name',
                'geofences.address as geofence_address',
            ])
            ->orderByDesc('orders.created_at')
            ->orderByDesc('orders.id')
            ->paginate($validated['per_page'] ?? 15);

        /*
        |--------------------------------------------------------------------------
        | Load Items For Current Page Orders
        |--------------------------------------------------------------------------
        */

        $orderIds = $orders->getCollection()->pluck('id');

        $items = collect();

        if ($orderIds->isNotEmpty()) {
            $items = DB::table('order_items')
                ->join('products', function ($join) use ($companyId) {
                    $join->on('products.id', '=', 'order_items.product_id')
                        ->where('products.company_id', '=', $companyId);
                })
                ->leftJoin('categories', function ($join) use ($companyId) {
                    $join->on('categories.id', '=', 'products.category_id')
                        ->where('categories.company_id', '=', $companyId);
                })
                ->leftJoin('sub_categories', function ($join) use ($companyId) {
                    $join->on('sub_categories.id', '=', 'products.sub_category_id')
                        ->where('sub_categories.company_id', '=', $companyId);
                })
                ->leftJoin('brands', function ($join) use ($companyId) {
                    $join->on('brands.id', '=', 'products.brand_id')
                        ->where('brands.company_id', '=', $companyId);
                })
                ->whereIn('order_items.order_id', $orderIds)
                ->select([
                    'order_items.id',
                    'order_items.order_id',
                    'order_items.quantity',
                    'order_items.unit_price',
                    'order_items.line_total',

                    'products.id as product_id',
                    'products.product_name',

                    'categories.id as category_id',
                    'categories.name as category_name',

                    'sub_categories.id as sub_category_id',
                    'sub_categories.name as sub_category_name',

                    'brands.id as brand_id',
                    'brands.name as brand_name',
                ])
                ->orderBy('order_items.id')
                ->get()
                ->groupBy('order_id');
        }

        /*
        |--------------------------------------------------------------------------
        | Nested Order -> Items -> Product
        |--------------------------------------------------------------------------
        */

        $data = $orders->getCollection()
            ->map(function ($order) use ($items) {

                $orderItems = $items->get($order->id, collect());

                return [
                    'id' => $order->id,
                    'order_number' => $order->order_number,
                    'order_date' => $order->created_at,
                    'status' => $order->status,
                    'total' => (float) $order->total,
                    'notes' => $order->notes,

                    'employee' => [
                        'id' => $order->created_by,
                        'name' => $order->employee_name,
                        'email' => $order->employee_email,
                    ],

                    'geofence' => [
                        'id' => $order->geofence_id,
                        'firm_name' => $order->firm_name,
                        'address' => $order->geofence_address,
                    ],

                    'items' => $orderItems
                        ->map(function ($item) {

                            return [
                                'id' => $item->id,
                                'quantity' => (float) $item->quantity,
                                'unit_price' => (float) $item->unit_price,
                                'line_total' => (float) $item->line_total,

                                'product' => [
                                    'id' => $item->product_id,
                                    'product_name' => $item->product_name,

                                    'category' => [
                                        'id' => $item->category_id,
                                        'name' => $item->category_name,
                                    ],

                                    'sub_category' => $item->sub_category_id
                                        ? [
                                            'id' => $item->sub_category_id,
                                            'name' => $item->sub_category_name,
                                        ]
                                        : null,

                                    'brand' => [
                                        'id' => $item->brand_id,
                                        'name' => $item->brand_name,
                                    ],
                                ],
                            ];
                        })
                    ->values(),
                ];
            })
            ->values();

        /*
        |--------------------------------------------------------------------------
        | Final Response
        |--------------------------------------------------------------------------
        */

        return response()->json([
            'success' => true,
            'message' => 'Order report retrieved successfully.',

            'filters' => [
                'from_date' => $validated['from_date'] ?? null,
                'to_date' => $validated['to_date'] ?? null,
                'category_id' => $validated['category_id'] ?? null,
                'sub_category_id' => $validated['sub_category_id'] ?? null,
                'brand_id' => $validated['brand_id'] ?? null,
                'product_id' => $validated['product_id'] ?? null,
                'hierarchy_level_id' => $validated['hierarchy_level_id'] ?? null,
                'hierarchy_node_id' => $validated['hierarchy_node_id'] ?? null,
                'user_id' => $validated['user_id'] ?? null,
                'geofence_id' => $validated['geofence_id'] ?? null,
                'status' => $validated['status'] ?? null,
            ],

            'summary' => [
                'total_orders' => (int) $summary->total_orders,
                'total_quantity' => (float) $quantitySummary,
                'total_amount' => (float) $summary->total_amount,
                'total_employees' => (int) $summary->total_employees,
            ],

            'data' => $data,

            'pagination' => [
                'current_page' => $orders->currentPage(),
                'per_page' => $orders->perPage(),
                'total' => $orders->total(),
                'last_page' => $orders->lastPage(),
                'from' => $orders->firstItem(),
                'to' => $orders->lastItem(),
                'next_page_url' => $orders->nextPageUrl(),
                'prev_page_url' => $orders->previousPageUrl(),
            ],
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Common Filters
    |--------------------------------------------------------------------------
    */

    private function applyFilters($query, array $filters, int $companyId): void
    {
        /*
         * Date Range
         */
        if (!empty($filters['from_date'])) {
            $query->whereDate(
                'orders.created_at',
                '>=',
                $filters['from_date']
            );
        }

        if (!empty($filters['to_date'])) {
            $query->whereDate(
                'orders.created_at',
                '<=',
                $filters['to_date']
            );
        }

        /*
         * Employee
         */
        if (!empty($filters['user_id'])) {
            $query->where(
                'orders.created_by',
                $filters['user_id']
            );
        }

        /*
         * Geofence
         */
        if (!empty($filters['geofence_id'])) {
            $query->where(
                'orders.geofence_id',
                $filters['geofence_id']
            );
        }

        /*
         * Order Status
         */
        if (!empty($filters['status'])) {
            $query->where(
                'orders.status',
                $filters['status']
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Product Filters
        |--------------------------------------------------------------------------
        |
        | At least one item in the order must match the selected product filters.
        |
        */

        $hasProductFilter =
            !empty($filters['category_id']) ||
            !empty($filters['sub_category_id']) ||
            !empty($filters['brand_id']) ||
            !empty($filters['product_id']);

        if ($hasProductFilter) {

            $query->whereExists(function ($subQuery) use ($filters, $companyId) {

                $subQuery->selectRaw('1')
                    ->from('order_items')
                    ->join('products', 'products.id', '=', 'order_items.product_id')
                    ->whereColumn('order_items.order_id', 'orders.id')
                    ->where('products.company_id', $companyId);

                if (!empty($filters['category_id'])) {
                    $subQuery->where(
                        'products.category_id',
                        $filters['category_id']
                    );
                }

                if (!empty($filters['sub_category_id'])) {
                    $subQuery->where(
                        'products.sub_category_id',
                        $filters['sub_category_id']
                    );
                }

                if (!empty($filters['brand_id'])) {
                    $subQuery->where(
                        'products.brand_id',
                        $filters['brand_id']
                    );
                }

                if (!empty($filters['product_id'])) {
                    $subQuery->where(
                        'products.id',
                        $filters['product_id']
                    );
                }
            });
        }

        /*
        |--------------------------------------------------------------------------
        | Hierarchy Filters
        |--------------------------------------------------------------------------
        |
        | Uses employee's CURRENT hierarchy assignment.
        |
        */

        $hasHierarchyFilter =
            !empty($filters['hierarchy_level_id']) ||
            !empty($filters['hierarchy_node_id']);

        if ($hasHierarchyFilter) {

            $query->whereExists(function ($subQuery) use ($filters, $companyId) {

                $subQuery->selectRaw('1')
                    ->from('employee_hierarchy_assignments as eha')
                    ->join(
                        'hierarchy_nodes as hn',
                        'hn.id',
                        '=',
                        'eha.hierarchy_node_id'
                    )
                    ->whereColumn('eha.user_id', 'orders.created_by')
                    ->where('eha.company_id', $companyId)
                    ->where('eha.is_current', true)
                    ->where('hn.company_id', $companyId)
                    ->where('hn.status', 'active');

                if (!empty($filters['hierarchy_level_id'])) {
                    $subQuery->where(
                        'hn.hierarchy_level_id',
                        $filters['hierarchy_level_id']
                    );
                }

                if (!empty($filters['hierarchy_node_id'])) {
                    $subQuery->where(
                        'hn.id',
                        $filters['hierarchy_node_id']
                    );
                }
            });
        }
    }

    private function loadOrderItems($orders, $companyId)
    {
        if ($orders->isEmpty()) {
            return $orders;
        }

        $orderIds = $orders->pluck('id');

        $items = DB::table('order_items')
            ->join('products', 'products.id', '=', 'order_items.product_id')
            ->leftJoin('categories', 'categories.id', '=', 'products.category_id')
            ->leftJoin('sub_categories', 'sub_categories.id', '=', 'products.sub_category_id')
            ->leftJoin('brands', 'brands.id', '=', 'products.brand_id')
            ->where('products.company_id', $companyId)
            ->whereIn('order_items.order_id', $orderIds)
            ->select([
                'order_items.*',
                'products.product_name',
                'categories.name as category_name',
                'sub_categories.name as sub_category_name',
                'brands.name as brand_name',
            ])
            ->orderBy('order_items.id')
            ->get()
            ->groupBy('order_id');

        return $orders->map(function ($order) use ($items) {
            $order->items = $items->get($order->id, collect());
            return $order;
        });
    }

    private function authorizeAction(string $action): void
    {
        abort_unless(auth()->user()?->can('order-report.' . $action), 403, 'You do not have permission to ' . $action . ' orders.');
    }    
}
