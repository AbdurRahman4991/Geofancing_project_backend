<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleController extends Controller
{
    // Role List
    public function index()
    {
        $roles = Role::with('permissions')->get();

        return response()->json([
            'status' => true,
            'data' => $roles
        ]);
    }

    // Create Role
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:roles,name',
            'permissions' => 'array'
        ]);

        $role = Role::create([
            'name' => $request->name
        ]);

        $role->syncPermissions($request->permissions);

        return response()->json([
            'status' => true,
            'message' => 'Role created successfully'
        ]);
    }

    // Single Role
    public function show(Role $role)
    {
        $role->load('permissions');

        return response()->json([
            'status' => true,
            'data' => $role
        ]);
    }

    // Update Role
    public function update(Request $request, Role $role)
    {
        $request->validate([
            'name' => 'required|unique:roles,name,' . $role->id,
            'permissions' => 'array'
        ]);

        $role->update([
            'name' => $request->name
        ]);

        $role->syncPermissions($request->permissions);

        return response()->json([
            'status' => true,
            'message' => 'Role updated successfully'
        ]);
    }

    // Delete Role
    public function destroy(Role $role)
    {
        $role->delete();

        return response()->json([
            'status' => true,
            'message' => 'Role deleted successfully'
        ]);
    }

    // Assign Role to User
    public function assignRole(Request $request, User $user)
    {
        $request->validate([
            'roles' => 'required|array'
        ]);

        $user->syncRoles($request->roles);

        return response()->json([
            'status' => true,
            'message' => 'Role assigned successfully'
        ]);
    }
}