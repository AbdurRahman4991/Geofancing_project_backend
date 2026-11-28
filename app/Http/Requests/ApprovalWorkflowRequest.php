<?php

namespace App\Http\Requests;

use App\Models\ApprovalWorkflow;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class ApprovalWorkflowRequest extends FormRequest
{
    public function authorize(): bool
    {
        $action = $this->isMethod('post') ? 'create' : 'update';
        return (bool) $this->user()?->can('approval-workflow.' . $action);
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            if (!$this->has('steps')) return;
            $orders = collect($this->input('steps', []))->pluck('step_order')->map(fn ($value) => (int) $value)->sort()->values()->all();
            if ($orders !== range(1, count($orders))) {
                $validator->errors()->add('steps', 'Step order must be sequential starting at 1.');
            }
        });
    }

    public function rules(): array
    {
        $workflowId = $this->route('approval_workflow');
        $workflow = $workflowId ? ApprovalWorkflow::find($workflowId) : null;
        $companyId = $this->user()?->hasAnyRole(['Super-Admin', 'super-admin'])
            ? $this->input('company_id', $workflow?->company_id)
            : ($this->user()?->company_id ?? $this->user()?->employee?->company_id);
        $required = $workflow ? 'sometimes' : 'required';
        return [
            'company_id' => [$workflow ? 'prohibited' : (!$this->user()?->hasAnyRole(['Super-Admin', 'super-admin']) ? 'prohibited' : 'required'), 'integer', 'exists:companies,id'],
            'name' => [$required, 'string', 'max:150', Rule::unique('approval_workflows', 'name')->where('company_id', $companyId)->where('module', $this->input('module', $workflow?->module))->ignore($workflow?->id)],
            'module' => [$required, 'string', 'max:100'],
            'is_active' => ['sometimes', 'boolean'],
            'steps' => [$required, 'array', 'min:1'],
            'steps.*.step_order' => ['required', 'integer', 'min:1', 'distinct'],
            'steps.*.name' => ['required', 'string', 'max:150'],
            'steps.*.approver_type' => ['required', Rule::in(['hierarchy_manager', 'employee', 'role'])],
            'steps.*.department_id' => ['nullable', 'integer', Rule::exists('departments', 'id')->where('company_id', $companyId)],
            'steps.*.approver_employee_id' => ['required_if:steps.*.approver_type,employee', 'nullable', 'integer', Rule::exists('employees', 'id')->where('company_id', $companyId)],
            'steps.*.approver_role' => ['required_if:steps.*.approver_type,role', 'nullable', 'string', 'max:150'],
        ];
    }
}
