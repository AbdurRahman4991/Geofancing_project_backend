<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use App\Traits\ScopesCompanyAccess;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class RoleController extends Controller
{
    use ScopesCompanyAccess;

    // Role List
    public function index(Request $request)
    {
        abort_unless($request->user()->can('role.view'), 403, 'You do not have permission to role view.');
        $query = $this->scopeToCurrentCompany(Role::query());

        if ($request->filled('company_id')) {
            $request->validate([
                'company_id' => ['integer', 'exists:companies,id'],
            ]);
            $query->where('roles.company_id', $request->input('company_id'));
        }

        $roles = $query
            ->select('id', 'name', 'guard_name', 'company_id')
            ->get();

        return response()->json([
            'status' => true,
            'data' => $roles,
        ]);
    }

    // Create Role
    public function store(Request $request)
    {
        abort_unless($request->user()->can('role.create'), 403, 'You do not have permission to role create.');
        $companyId = $this->companyIdFor($request);
        $request->validate([
            'name' => [
                'required', 'string', 'max:255',
                Rule::unique('roles', 'name')->where('company_id', $companyId)->where('guard_name', 'sanctum'),
                function ($attribute, $value, $fail) use ($request) {
                    if (!$request->user()->hasRole('Super-Admin') && $this->isReservedRole($value)) {
                        $fail('The admin and Super-Admin role names are reserved.');
                    }
                },
            ],
            'permissions' => ['sometimes', 'array'],
            'company_id' => ['sometimes', 'nullable', 'integer', 'exists:companies,id'],
        ]);

        $role = Role::query()->create([
            'name' => $request->name,
            'guard_name' => 'sanctum',
            'company_id' => $companyId,
        ]);

        $role->syncPermissions($request->input('permissions', []));

        return response()->json([
            'status' => true,
            'message' => 'Role created successfully',
            'data' => $role->load('permissions:id,name'),
        ]);
    }

    // Single Role
    public function show(int $id)
    {
        abort_unless(auth()->user()->can('role.edit'), 403, 'You do not have permission to role edit.');
        $role = $this->scopeToCurrentCompany(Role::query())->findOrFail($id);
        $role->load('permissions');

        return response()->json([
            'status' => true,
            'data' => $role
        ]);
    }

    // Update Role
    public function update(Request $request, int $id)
    {
        abort_unless($request->user()->can('role.update'), 403, 'You do not have permission to role update.');
        $role = $this->scopeToCurrentCompany(Role::query())->findOrFail($id);
        $request->validate([
            'name' => [
                'required', 'string', 'max:255',
                Rule::unique('roles', 'name')->where('company_id', $role->company_id)->where('guard_name', $role->guard_name)->ignore($role->id),
                function ($attribute, $value, $fail) use ($request) {
                    if (!$request->user()->hasRole('Super-Admin') && $this->isReservedRole($value)) {
                        $fail('The admin and Super-Admin role names are reserved.');
                    }
                },
            ],
            'permissions' => ['sometimes', 'array'],
        ]);

        abort_if(!$request->user()->hasRole('Super-Admin') && $this->isReservedRole($role->name), 403, 'This role is protected.');
        $role->update([
            'name' => $request->name
        ]);

        if ($request->has('permissions')) {
            $role->syncPermissions($request->input('permissions'));
        }

        return response()->json([
            'status' => true,
            'message' => 'Role updated successfully'
        ]);
    }

    // Delete Role
    public function destroy(int $id)
    {
        abort_unless(auth()->user()->can('role.delete'), 403, 'You do not have permission to role delete.');
        $role = $this->scopeToCurrentCompany(Role::query())->findOrFail($id);
        abort_if(!auth()->user()->hasRole('Super-Admin') && $this->isReservedRole($role->name), 403, 'This role is protected.');
        $role->delete();

        return response()->json([
            'status' => true,
            'message' => 'Role deleted successfully'
        ]);
    }

    // Assign Role to User
    public function assignRole(Request $request, User $user)
    {
        abort_unless($request->user()->can('role.view'), 403, 'You do not have permission to assign roles.');
        $request->validate([
            'roles' => ['required', 'array'],
            'roles.*' => ['required'],
        ]);

        $companyId = $this->companyIdFor($request);
        if (!$request->user()->hasRole('Super-Admin')) {
            abort_unless((int) $this->companyIdForUser($user) === (int) $companyId, 403, 'You can only assign roles to users in your company.');
        }

        $roles = collect($request->input('roles'))->map(function ($identifier) use ($companyId, $request) {
            $query = Role::query();
            if (!$request->user()->hasRole('Super-Admin')) {
                $query->where('company_id', $companyId);
            }

            $role = is_numeric($identifier)
                ? $query->findOrFail((int) $identifier)
                : $query->where('name', (string) $identifier)->firstOrFail();

            abort_if(!$request->user()->hasRole('Super-Admin') && $this->isReservedRole($role->name), 403, 'This role cannot be assigned.');
            return $role;
        });

        $user->syncRoles($roles);

        return response()->json([
            'status' => true,
            'message' => 'Role assigned successfully'
        ]);
    }

    public function getRolePermissions(int $roleId)
    {
        abort_unless(auth()->user()->can('role.view'), 403, 'You do not have permission to role view.');
        $role = $this->scopeToCurrentCompany(Role::query())->findOrFail($roleId);

        $permissions = $role->permissions()->get([
            'id',
            'name',
        ]);

        return response()->json([
            'status' => 200,
            'data' => $permissions,
        ]);
    }

    private function companyIdFor(Request $request): ?int
    {
        $user = $request->user();
        if ($user->hasRole('Super-Admin')) {
            $id = $request->input('company_id');
            return $id === null ? null : (int) $id;
        }

        $id = $this->companyIdForUser($user);
        abort_unless($id, 403, 'Your account is not associated with a company.');
        return $id;
    }

    private function companyIdForUser(User $user): ?int
    {
        $id = $user->company_id ?? $user->employee?->company_id;
        return $id === null ? null : (int) $id;
    }

    private function isReservedRole(string $name): bool
    {
        return in_array(strtolower(trim($name)), ['super-admin'], true);
    }
}
