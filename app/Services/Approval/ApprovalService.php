<?php

namespace App\Services\Approval;

use App\Models\ApprovalRequest;
use App\Models\ApprovalRequestStep;
use App\Models\ApprovalWorkflow;
use App\Models\EmployeeHierarchyAssignment;
use App\Models\Employee;
use App\Models\User;
use App\Notifications\ApprovalNotification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ApprovalService
{
    public function createApprovalRequest(int $companyId, string $module, int $documentId, User $requester): ApprovalRequest
    {
        return DB::transaction(function () use ($companyId, $module, $documentId, $requester) {
            if (ApprovalRequest::query()->where('company_id', $companyId)->where('module', $module)->where('document_id', $documentId)->where('status', 'pending')->exists()) {
                throw ValidationException::withMessages(['approval' => 'This document already has an active approval request.']);
            }
            $workflow = ApprovalWorkflow::query()->where('company_id', $companyId)->where('module', $module)->where('is_active', true)->with('steps')->first();
            if (!$workflow || $workflow->steps->isEmpty()) {
                throw ValidationException::withMessages(['workflow' => 'No active approval workflow is configured for this module and company.']);
            }
            foreach ($workflow->steps->values() as $index => $step) {
                if ((int) $step->step_order !== $index + 1) {
                    throw ValidationException::withMessages(['workflow' => 'Workflow steps must be numbered sequentially starting at 1.']);
                }
            }

            $assignments = [];
            foreach ($workflow->steps as $step) {
                $approverIds = $this->resolveApprovers($step, $requester, $companyId);
                $approverIds = array_values(array_diff($approverIds, [(int) $requester->id]));
                if (!$approverIds) {
                    throw ValidationException::withMessages(['workflow' => "No eligible approver is configured for step {$step->step_order} ({$step->name})."]);
                }
                $assignments[] = [$step, $approverIds];
            }

            $approval = ApprovalRequest::create([
                'company_id' => $companyId, 'workflow_id' => $workflow->id, 'module' => $module,
                'document_id' => $documentId, 'requested_by' => $requester->id,
                'current_step' => 1, 'status' => 'pending', 'submitted_at' => now(),
            ]);
            foreach ($assignments as [$step, $approverIds]) {
                $approval->steps()->create([
                    'step_order' => $step->step_order, 'name' => $step->name,
                    'approver_type' => $step->approver_type, 'assigned_approver_ids' => $approverIds,
                    'status' => (int) $step->step_order === 1 ? 'pending' : 'waiting',
                ]);
            }
            $this->notifyUsers($assignments[0][1], $approval, 'pending');
            $approval->setRelation('steps', $approval->steps()->where('step_order', 1)->get());
            return $approval;
        });
    }

    public function submitForApproval(int $companyId, string $module, int $documentId, User $requester): ApprovalRequest
    {
        return $this->createApprovalRequest($companyId, $module, $documentId, $requester);
    }

    public function getPendingApprovals(User $user)
    {
        $page = $this->accessibleRequests($user)
            ->where('status', 'pending')
            ->whereHas('steps', fn (Builder $step) => $step->whereColumn('step_order', 'approval_requests.current_step')->where('status', 'pending')->whereJsonContains('assigned_approver_ids', (int) $user->id))
            ->with('requester:id,name,email')
            ->latest('submitted_at')->paginate(15);
        $page->getCollection()->transform(function (ApprovalRequest $approval) {
            $approval->setRelation('steps', $approval->steps()->where('step_order', '<=', $approval->current_step)->get());
            return $this->attachDocument($approval);
        });
        return $page;
    }

    public function getApproval(int $id, User $user): ApprovalRequest
    {
        $approval = $this->accessibleRequests($user)->with(['requester:id,name,email', 'steps.actor:id,name'])->findOrFail($id);
        abort_unless($this->canViewApproval($approval, $user), 404);
        $approval->setRelation('steps', $approval->steps->where('step_order', '<=', $approval->current_step)->values());
        return $this->attachDocument($approval);
    }

    public function approve(int $id, User $user, ?string $remarks = null): ApprovalRequest
    {
        return $this->act($id, $user, 'approved', $remarks);
    }

    public function reject(int $id, User $user, string $remarks): ApprovalRequest
    {
        return $this->act($id, $user, 'rejected', $remarks);
    }

    public function getApprovalHistory(int $id, User $user)
    {
        return $this->getApproval($id, $user)->steps;
    }

    public function canViewApproval(ApprovalRequest $approval, User $user): bool
    {
        if ((int) $approval->requested_by === (int) $user->id) return true;
        return $approval->steps()->where('step_order', '<=', $approval->current_step)
            ->whereJsonContains('assigned_approver_ids', (int) $user->id)->exists();
    }

    public function canApprove(ApprovalRequest $approval, User $user): bool
    {
        if ($approval->status !== 'pending' || (int) $approval->requested_by === (int) $user->id) return false;
        return $approval->steps()->where('step_order', $approval->current_step)->where('status', 'pending')
            ->whereJsonContains('assigned_approver_ids', (int) $user->id)->exists();
    }

    public function getCurrentApprovalStep(ApprovalRequest $approval): ?ApprovalRequestStep
    {
        return $approval->steps()->where('step_order', $approval->current_step)->first();
    }

    public function cancelForRequester(int $id, User $requester): ApprovalRequest
    {
        return DB::transaction(function () use ($id, $requester) {
            $approval = ApprovalRequest::query()->where('company_id', $this->companyId($requester))->lockForUpdate()->findOrFail($id);
            abort_unless((int) $approval->requested_by === (int) $requester->id && $approval->status === 'pending', 403, 'Only the requester can cancel a pending approval.');
            $step = $approval->steps()->where('step_order', $approval->current_step)->lockForUpdate()->firstOrFail();
            abort_unless($step->status === 'pending', 409, 'This approval step can no longer be cancelled.');
            $step->update(['status' => 'cancelled', 'action_by' => $requester->id, 'action_at' => now(), 'remarks' => 'Cancelled by requester.']);
            $approval->update(['status' => 'cancelled', 'completed_at' => now()]);
            $approval->refresh();
            $approval->setRelation('steps', $approval->steps()->where('step_order', '<=', $approval->current_step)->get());
            return $approval;
        });
    }

    private function act(int $id, User $user, string $action, ?string $remarks): ApprovalRequest
    {
        return DB::transaction(function () use ($id, $user, $action, $remarks) {
            $approval = ApprovalRequest::query()->where('company_id', $this->companyId($user))->lockForUpdate()->findOrFail($id);
            abort_unless($this->canApprove($approval, $user), 403, 'You are not the assigned approver for the active step.');
            $step = $approval->steps()->where('step_order', $approval->current_step)->lockForUpdate()->firstOrFail();
            abort_unless($step->status === 'pending' && in_array((int) $user->id, $step->assigned_approver_ids, true), 403, 'You are not assigned to this approval step.');

            $step->update(['status' => $action, 'action_by' => $user->id, 'action_at' => now(), 'remarks' => $remarks]);
            if ($action === 'rejected') {
                $approval->update(['status' => 'rejected', 'completed_at' => now()]);
                $this->notifyUsers([(int) $approval->requested_by], $approval, 'rejected');
            } else {
                $next = $approval->steps()->where('step_order', '>', $approval->current_step)->orderBy('step_order')->lockForUpdate()->first();
                if ($next) {
                    $next->update(['status' => 'pending']);
                    $approval->update(['current_step' => $next->step_order]);
                    $this->notifyUsers($next->assigned_approver_ids, $approval, 'pending');
                } else {
                    $approval->update(['status' => 'approved', 'completed_at' => now()]);
                    $this->notifyUsers([(int) $approval->requested_by], $approval, 'approved');
                }
            }
            $approval->refresh();
            $approval->setRelation('steps', $approval->steps()->where('step_order', '<=', $approval->current_step)->get());
            return $approval;
        });
    }

    private function resolveApprovers($step, User $requester, int $companyId): array
    {
        if ($step->approver_type === 'employee') {
            $employee = Employee::query()->where('company_id', $companyId)->find($step->approver_employee_id);
            $userId = $employee?->user()->where('company_id', $companyId)->value('id');
            return $userId ? [(int) $userId] : [];
        }
        if ($step->approver_type === 'role') {
            $users = User::query()->where('company_id', $companyId)
                ->whereHas('roles', fn ($q) => $q->where('name', $step->approver_role)->where('guard_name', 'sanctum'))
                ->pluck('id')->map(fn ($id) => (int) $id)->all();
            if ($step->department_id) $users = $this->filterByDepartment($users, $companyId, $step->department_id);
            return $users;
        }
        $managerIds = $this->hierarchyManagers($requester, $companyId);
        return $step->department_id ? $this->filterByDepartment($managerIds, $companyId, (int) $step->department_id) : $managerIds;
    }

    private function hierarchyManagers(User $requester, int $companyId): array
    {
        $assignments = EmployeeHierarchyAssignment::query()->where('company_id', $companyId)->where('user_id', $requester->id)
            ->where('is_current', true)->whereNotNull('hierarchy_node_id')->with('hierarchyNode')->get();
        foreach ($assignments as $assignment) {
            $node = $assignment->hierarchyNode;
            while ($node?->parent_id) {
                $managerIds = EmployeeHierarchyAssignment::query()->where('company_id', $companyId)->where('hierarchy_node_id', $node->parent_id)
                    ->where('is_current', true)->where('user_id', '!=', $requester->id)
                    ->whereHas('user', fn ($q) => $q->where('company_id', $companyId))
                    ->pluck('user_id')->map(fn ($id) => (int) $id)->all();
                if ($managerIds) return $managerIds;
                $node = $node->parent;
            }
        }
        return [];
    }

    private function filterByDepartment(array $userIds, int $companyId, int $departmentId): array
    {
        $departmentName = \App\Models\Department::query()->where('company_id', $companyId)->whereKey($departmentId)->value('name');
        if (!$departmentName) return [];
        return User::query()->whereIn('id', $userIds)->whereHas('employee', fn ($q) => $q->where('department_id', $departmentName))
            ->pluck('id')->map(fn ($id) => (int) $id)->all();
    }

    private function accessibleRequests(User $user): Builder
    {
        return ApprovalRequest::query()->where('company_id', $this->companyId($user));
    }

    private function companyId(User $user): int
    {
        $id = $user->company_id ?? $user->employee?->company_id;
        abort_unless($id || $user->hasAnyRole(['Super-Admin', 'super-admin']), 403, 'Your account is not associated with a company.');
        return (int) ($id ?? request()->input('company_id'));
    }

    private function notifyUsers(array $userIds, ApprovalRequest $approval, string $action): void
    {
        foreach (array_unique($userIds) as $userId) {
            User::query()->where('company_id', $approval->company_id)->find($userId)?->notify(new ApprovalNotification($approval, $action));
        }
    }

    private function attachDocument(ApprovalRequest $approval): ApprovalRequest
    {
        if ($approval->module === 'tour_plan') {
            $document = \App\Models\TourPlan::query()->where('company_id', $approval->company_id)
                ->with(['employee:id,name,email', 'creator:id,name', 'stops.geofence:id,firm_name,address,latitude,longitude'])
                ->find($approval->document_id);
            $approval->setAttribute('document', $document);
        }
        return $approval;
    }
}
