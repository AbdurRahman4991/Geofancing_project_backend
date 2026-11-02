<?php

namespace App\Http\Controllers\Api\Order;

use App\Http\Controllers\Controller;
use App\Services\Order\OrderService;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function __construct(private readonly OrderService $orders)
    {
    }

    public function index(Request $request)
    {
        $this->authorizeAction('view');

        return response()->json(['message' => 'Orders retrieved successfully', 'data' => $this->orders->index($request)]);
    }

    public function store(Request $request)
    {
        $this->authorizeAction('create');

        return response()->json(['message' => 'Order created successfully', 'data' => $this->orders->store($request)], 201);
    }

    public function show(Request $request, int $id)
    {
        $this->authorizeAction('view');

        return response()->json(['data' => $this->orders->show($request, $id)]);
    }

    public function update(Request $request, int $id)
    {
        $this->authorizeAction('edit');

        return response()->json(['message' => 'Order updated successfully', 'data' => $this->orders->update($request, $id)]);
    }

    public function destroy(Request $request, int $id)
    {
        $this->authorizeAction('delete');
        $this->orders->destroy($request, $id);

        return response()->json(['message' => 'Order deleted successfully']);
    }

    private function authorizeAction(string $action): void
    {
        abort_unless(auth()->user()?->can('order.' . $action), 403, 'You do not have permission to ' . $action . ' orders.');
    }
}
