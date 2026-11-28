<?php

namespace App\Services\Approval;

use App\Models\ApprovalWorkflow;
use App\Models\Company;
use App\Traits\ScopesCompanyAccess;
use App\Traits\ScopesCompanyDirectoryAccess;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ApprovalWorkflowService
{
    use ScopesCompanyAccess;
    use ScopesCompanyDirectoryAccess;

    public function index(Request $request)
    {
        $this->authorize($request, 'view');
        $query = ApprovalWorkflow::query()->with('steps.department:id,name')->withCount('requests');
        $this->scopeToCurrentCompany($query);
        if ($request->filled('module')) $query->where('module', $request->string('module')->toString());
        return $query->orderBy('name')->paginate(min(max((int) $request->input('per_page', 15), 1), 100));
    }

    public function store(Request $request): ApprovalWorkflow
    {
        $this->authorize($request, 'create');
        $data = $this->validated($request);
        $companyId = $this->companyId($request, $data['company_id'] ?? null);
        return DB::transaction(function () use ($data, $companyId) {
            $workflow = ApprovalWorkflow::create(['company_id' => $companyId, 'name' => $data['name'], 'module' => $data['module'], 'is_active' => $data['is_active'] ?? true]);
            $this->saveSteps($workflow, $data['steps']);
            return $workflow->load('steps.department:id,name');
        });
    }

    public function show(Request $request, int $id): ApprovalWorkflow
    {
        $this->authorize($request, 'view');
        return $this->query()->with('steps.department:id,name')->findOrFail($id);
    }

    public function update(Request $request, int $id): ApprovalWorkflow
    {
        $this->authorize($request, 'update');
        $workflow = $this->query()->findOrFail($id);
        $data = $this->validated($request, $workflow);
        return DB::transaction(function () use ($workflow, $data) {
            $workflow->update(collect($data)->only(['name', 'module', 'is_active'])->all());
            if (array_key_exists('steps', $data)) {
                $workflow->steps()->delete();
                $this->saveSteps($workflow, $data['steps']);
            }
            return $workflow->refresh()->load('steps.department:id,name');
        });
    }

    public function destroy(Request $request, int $id): void
    {
        $this->authorize($request, 'delete');
        $workflow = $this->query()->findOrFail($id);
        abort_if($workflow->requests()->exists(), 409, 'A workflow used by approval requests cannot be deleted.');
        $workflow->delete();
    }

    private function validated(Request $request, ?ApprovalWorkflow $workflow = null): array
    {
        $user = $request->user();
        $companyId = $user->hasAnyRole(['Super-Admin', 'super-admin'])
            ? $request->input('company_id', $workflow?->company_id)
            : ($user->company_id ?? $user->employee?->company_id);
        $rules = [
            'company_id' => [$workflow ? 'prohibited' : (!$user->hasAnyRole(['Super-Admin', 'super-admin']) ? 'prohibited' : 'required'), 'integer', 'exists:companies,id'],
            'name' => [$workflow ? 'sometimes' : 'required', 'string', 'max:150', Rule::unique('approval_workflows', 'name')->where('company_id', $companyId)->where('module', $request->input('module', $workflow?->module))->ignore($workflow?->id)],
            'module' => [$workflow ? 'sometimes' : 'required', 'string', 'max:100'],
            'is_active' => ['sometimes', 'boolean'],
            'steps' => [$workflow ? 'sometimes' : 'required', 'array', 'min:1'],
            'steps.*.step_order' => ['required', 'integer', 'min:1', 'distinct'],
            'steps.*.name' => ['required', 'string', 'max:150'],
            'steps.*.approver_type' => ['required', Rule::in(['hierarchy_manager', 'employee', 'role'])],
            'steps.*.department_id' => ['nullable', 'integer', Rule::exists('departments', 'id')->where('company_id', $companyId)],
            'steps.*.approver_employee_id' => ['required_if:steps.*.approver_type,employee', 'nullable', 'integer', Rule::exists('employees', 'id')->where('company_id', $companyId)],
            'steps.*.approver_role' => ['required_if:steps.*.approver_type,role', 'nullable', 'string', 'max:150'],
        ];
        $data = $request->validate($rules);
        $data['steps'] ??= null;
        return $data;
    }

    private function saveSteps(ApprovalWorkflow $workflow, array $steps): void
    {
        foreach ($steps as $step) {
            $workflow->steps()->create($step);
        }
    }

    private function companyId(Request $request, mixed $requested): int
    {
        $user = $request->user();
        $id = $user->hasAnyRole(['Super-Admin', 'super-admin']) ? (int) $requested : (int) ($user->company_id ?? $user->employee?->company_id);
        abort_unless($id, 403, 'Your account is not associated with a company.');
        $query = Company::query();
        $this->scopeToAccessibleCompanies($query);
        return (int) $query->findOrFail($id)->id;
    }

    private function query()
    {
        $query = ApprovalWorkflow::query();
        return $this->scopeToCurrentCompany($query);
    }

    private function authorize(Request $request, string $action): void
    {
        abort_unless($request->user()?->can("approval-workflow.{$action}"), 403, 'You do not have permission to manage approval workflows.');
    }
}
