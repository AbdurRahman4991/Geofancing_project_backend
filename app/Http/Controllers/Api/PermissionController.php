<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionController extends Controller
{
    /**
     * Permission List
     */
    public function index()
    {
        $permissions = Permission::select('id', 'name', 'guard_name')
            ->orderBy('name')
            ->get();

        return response()->json([
            'status' => true,
            'data' => $permissions
        ]);
    }

    /**
     * Create Permission
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => [
                'required',
                'string',
                Rule::unique('permissions', 'name'),
            ],
        ]);

        $permission = Permission::create([
            'name' => $request->name,
            'guard_name' => 'sanctum', // অথবা web
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Permission created successfully.',
            'data' => $permission
        ], 201);
    }

    /**
     * Single Permission
     */
    public function show(Permission $permission)
    {
        return response()->json([
            'status' => true,
            'data' => $permission
        ]);
    }

    /**
     * Update Permission
     */
    public function update(Request $request, Permission $permission)
    {
        $request->validate([
            'name' => [
                'required',
                'string',
                Rule::unique('permissions', 'name')->ignore($permission->id),
            ],
        ]);

        $permission->update([
            'name' => $request->name,
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Permission updated successfully.',
            'data' => $permission
        ]);
    }

    /**
     * Delete Permission
     */
    public function destroy(Permission $permission)
    {
        $permission->delete();

        return response()->json([
            'status' => true,
            'message' => 'Permission deleted successfully.'
        ]);
    }

    /**
     * Grouped Permission (Dynamic UI)
     */
    public function grouped()
    {
        $permissions = Permission::select('id', 'name')
            ->orderBy('name')
            ->get()
            ->groupBy(function ($permission) {
                return explode('.', $permission->name)[0];
            });

        return response()->json([
            'status' => true,
            'data' => $permissions
        ]);
    }

    public function assignPermission(Request $request, Role $role)
    {
        $request->validate([
            'permissions' => 'required|array'
        ]);

        $role->syncPermissions($request->permissions);

        return response()->json([
            'status' => true,
            'message' => 'Permissions assigned successfully'
        ]);
    }
}