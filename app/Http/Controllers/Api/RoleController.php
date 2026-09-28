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
    public function index(Request $request)
    {
        if (!auth()->user()->can('role.view')) {
        abort(403, 'You do not have permission to role view.');
        }         
        $roles = Role::select('id', 'name', 'guard_name')->get();

        return response()->json([
            'status' => true,
            'data' => $roles,
        ]);
    }

    // Create Role
    public function store(Request $request)
    {
        if (!auth()->user()->can('role.create')) {
        abort(403, 'You do not have permission to role create.');
        }        
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
        if (!auth()->user()->can('role.edit')) {
        abort(403, 'You do not have permission to role edit.');
        }        
        $role->load('permissions');

        return response()->json([
            'status' => true,
            'data' => $role
        ]);
    }

    // Update Role
    public function update(Request $request, Role $role)
    {
        if (!auth()->user()->can('role.edit')) {
        abort(403, 'You do not have permission to role edit.');
        }        
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
        if (!auth()->user()->can('role.delete')) {
        abort(403, 'You do not have permission to role delete.');
        }        
        $role->delete();

        return response()->json([
            'status' => true,
            'message' => 'Role deleted successfully'
        ]);
    }

    // Assign Role to User
    public function assignRole(Request $request, User $user)
    {
        if (!auth()->user()->can('role.view')) {
        abort(403, 'You do not have permission to role create.');
        }  
        $request->validate([
            'roles' => 'required|array'
        ]);

        $user->syncRoles($request->roles);

        return response()->json([
            'status' => true,
            'message' => 'Role assigned successfully'
        ]);
    }

    public function getRolePermissions($roleId)
    {
        if (!auth()->user()->can('role.view')) {
        abort(403, 'You do not have permission to role view.');
        }          
        $role = Role::findOrFail($roleId);

        $permissions = $role->permissions()->get([
            'id',
            'name',
        ]);

        return response()->json([
            'status' => 200,
            'data' => $permissions,
        ]);
    }
}