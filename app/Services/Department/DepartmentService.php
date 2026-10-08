<?php

namespace App\Services\Department;

use App\Models\Department;
use App\Traits\ScopesCompanyAccess;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DepartmentService
{
    use ScopesCompanyAccess;

    public function index(Request $request)
    {
        $this->authorize('view');

        $query = Department::query()->with('company:id,company_name');
        $this->scopeToCurrentCompany($query);

        if ($request->filled('search')) {
            $search = $request->string('search')->trim()->toString();
            $query->where(function ($builder) use ($search) {
                $builder->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $sort = $request->input('orderBy', 'name');
        $sort = in_array($sort, ['id', 'name', 'status', 'created_at'], true) ? $sort : 'name';
        $direction = strtolower($request->input('order', 'asc'));
        $direction = in_array($direction, ['asc', 'desc'], true) ? $direction : 'asc';

        return $query->orderBy($sort, $direction)
            ->paginate(min(max((int) $request->input('per_page', 10), 1), 100));
    }

    public function store(Request $request): Department
    {
        $this->authorize('create');
        $data = $this->validatedData($request);
        $data['company_id'] = $this->companyIdFor($request, $data['company_id'] ?? null);

        return Department::create($data);
    }

    public function show(int $id): Department
    {
        $this->authorize('view');
        return $this->accessibleQuery()->with('company:id,company_name')->findOrFail($id);
    }

    public function update(Request $request, int $id): Department
    {
        $this->authorize('edit');
        $department = $this->accessibleQuery()->findOrFail($id);
        $data = $this->validatedData($request, $department);
        unset($data['company_id']);
        $department->update($data);

        return $department->refresh()->load('company:id,company_name');
    }

    public function destroy(int $id): void
    {
        $this->authorize('delete');
        $this->accessibleQuery()->findOrFail($id)->delete();
    }

    private function validatedData(Request $request, ?Department $department = null): array
    {
        $companyId = $request->user()->hasRole('Super-Admin')
            ? $request->input('company_id', $department?->company_id)
            : ($request->user()->company_id ?? $request->user()->employee?->company_id);

        return $request->validate([
            'company_id' => [$department || !$request->user()->hasRole('Super-Admin') ? 'sometimes' : 'required', 'integer', 'exists:companies,id'],
            'name' => [ $department ? 'sometimes' : 'required', 'string', 'max:255', Rule::unique('departments', 'name')->where('company_id', $companyId)->ignore($department?->id)],
            'description' => ['sometimes', 'nullable', 'string', 'max:5000'],
            'status' => ['sometimes', 'in:active,inactive'],
        ]);
    }

    private function companyIdFor(Request $request, mixed $requestedCompanyId): int
    {
        if ($request->user()->hasRole('Super-Admin')) {
            return (int) $requestedCompanyId;
        }

        $companyId = $request->user()->company_id ?? $request->user()->employee?->company_id;
        abort_unless($companyId, 403, 'Your account is not associated with a company.');

        return (int) $companyId;
    }

    private function accessibleQuery()
    {
        $query = Department::query();
        return $this->scopeToCurrentCompany($query);
    }

    private function authorize(string $action): void
    {
        abort_unless(auth()->user()?->can("department.{$action}"), 403, 'You do not have permission to manage departments.');
    }
}
