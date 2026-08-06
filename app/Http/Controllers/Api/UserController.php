<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;

class UserController extends Controller
{
    public function assignRoleUsers(Request $request)
    {
        $query = User::with([
            'employee:id,employee_id,name'
        ])
        ->select('id', 'employee_id', 'name');

        // Search by employee name or employee id
        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->where('employee_id', 'like', "%{$search}%")
                  ->orWhereHas('employee', function ($employee) use ($search) {
                      $employee->where('name', 'like', "%{$search}%")
                               ->orWhere('employee_id', 'like', "%{$search}%");
                  });
            });
        }

        // Sorting
        $orderBy = $request->get('orderBy', 'id');
        $order = $request->get('order', 'desc');

        $query->orderBy($orderBy, $order);

        // Pagination
        $perPage = $request->get('per_page', 10);

        $users = $query->paginate($perPage);

        return response()->json([
            'status' => true,
            'message' => 'User list retrieved successfully',
            'data' => $users,
        ]);
    }
}