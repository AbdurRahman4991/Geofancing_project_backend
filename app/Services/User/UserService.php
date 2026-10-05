<?php

namespace App\Services\User;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UserService
{
    public function index(Request $request)
    {
        $request->validate([
            'company_id' => 'sometimes|integer|exists:companies,id',
            'search' => 'sometimes|string|max:255',
            'per_page' => 'sometimes|integer|min:1|max:100',
        ]);

        $query = User::query()
            ->with('employee:id,name,employee_id,company_id,designation')
            ->select('id', 'name', 'email', 'phone', 'employee_id', 'company_id', 'status', 'created_at');

        if ($request->filled('company_id')) {
            $companyId = $request->integer('company_id');
            $query->where(function ($companyQuery) use ($companyId) {
                $companyQuery->where('users.company_id', $companyId)
                    ->orWhereHas('employee', function ($employeeQuery) use ($companyId) {
                        $employeeQuery->where('company_id', $companyId);
                    });
            });
        }

        if ($request->filled('search')) {
            $search = $request->string('search')->toString();
            $query->where(function ($userQuery) use ($search) {
                $userQuery->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhereHas('employee', function ($employeeQuery) use ($search) {
                        $employeeQuery->where('name', 'like', "%{$search}%")
                            ->orWhere('employee_id', 'like', "%{$search}%");
                    });
            });
        }

        return $query->orderByDesc('id')->paginate($request->integer('per_page', 10));
    }

    public function show(int $id): User
    {
        return User::with('employee:id,name,employee_id,company_id,designation')->findOrFail($id);
    }

    public function update(Request $request, int $id): User
    {
        $user = User::findOrFail($id);

        $data = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'email' => ['sometimes', 'required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'phone' => 'sometimes|nullable|string|max:30',
            'status' => 'sometimes|required|in:active,inactive',
            'company_id' => 'sometimes|nullable|integer|exists:companies,id',
        ]);

        $user->update($data);

        return $user->load('employee:id,name,employee_id,company_id,designation');
    }
}
