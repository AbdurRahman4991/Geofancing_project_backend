<?php

namespace App\Services\TourPlan;

use App\Models\EmployeeHierarchyAssignment;
use App\Models\Geofence;
use App\Models\MarketVisit;
use App\Models\TourPlan;
use App\Models\User;
use App\Services\Hierarchy\HierarchyAccessService;
use App\Services\Approval\ApprovalService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TourPlanService
{
    public function __construct(
        private readonly HierarchyAccessService $hierarchyAccessService,
        private readonly ApprovalService $approvalService,
    ) {}

    public function index(User $user, array $filters)
    {
        $companyId = $this->companyId($user, $filters['company_id'] ?? null, false);
        $query = TourPlan::query()->with(['employee:id,name,email', 'creator:id,name', 'reviewer:id,name', 'stops.geofence:id,firm_name,address,latitude,longitude']);
        if ($companyId) {
            $query->where('company_id', $companyId);
        } elseif (!$this->isSuperAdmin($user)) {
            abort(403, 'Company not found for this user.');
        }
        if (!$this->isCompanyAdmin($user) && !$this->isSuperAdmin($user)) {
            $query->whereIn('user_id', $this->visibleUserIds($user, $companyId));
        }
        foreach (['user_id', 'status'] as $filter) {
            if (!empty($filters[$filter])) $query->where($filter, $filters[$filter]);
        }
        if (!empty($filters['from_date'])) $query->whereDate('plan_date', '>=', $filters['from_date']);
        if (!empty($filters['to_date'])) $query->whereDate('plan_date', '<=', $filters['to_date']);
        $plans = $query->orderByDesc('plan_date')->paginate($filters['per_page'] ?? 15);
        $plans->getCollection()->transform(fn (TourPlan $plan) => $this->loadPlan($plan, true));
        return $plans;
    }

    public function store(User $actor, array $data): TourPlan
    {
        $companyId = $this->companyId($actor, $data['company_id'] ?? null, true);
        $employee = User::query()->where('company_id', $companyId)->findOrFail($data['user_id'] ?? $actor->id);
        $this->ensureCanManageEmployee($actor, $employee, $companyId);
        $this->validateStops($data['stops'], $companyId, $actor);
        $plan = DB::transaction(function () use ($data, $actor, $employee, $companyId) {
            $plan = TourPlan::create([
                'company_id' => $companyId, 'user_id' => $employee->id, 'created_by' => $actor->id,
                'plan_date' => $data['plan_date'], 'status' => 'draft', 'notes' => $data['notes'] ?? null,
            ]);
            $this->replaceStops($plan, $data['stops']);
            return $plan;
        });
        return $this->loadPlan($plan, true);
    }

    public function show(User $actor, TourPlan $plan): TourPlan
    {
        $this->ensureCanView($actor, $plan);
        return $this->loadPlan($plan, true);
    }

    public function update(User $actor, TourPlan $plan, array $data): TourPlan
    {
        $this->ensureCanView($actor, $plan);
        abort_unless(in_array($plan->status, ['draft', 'rejected'], true), 409, 'Only draft or rejected plans can be edited.');
        $this->ensureCanManageEmployee($actor, $plan->employee, (int) $plan->company_id);
        $this->validateStops($data['stops'], (int) $plan->company_id, $actor);
        DB::transaction(function () use ($plan, $data) {
            $plan->update(['plan_date' => $data['plan_date'], 'notes' => $data['notes'] ?? null, 'status' => 'draft', 'reviewed_by' => null, 'reviewed_at' => null, 'review_note' => null]);
            $plan->stops()->delete();
            $this->replaceStops($plan, $data['stops']);
        });
        return $this->loadPlan($plan->fresh(), true);
    }

    public function submit(User $actor, TourPlan $plan): TourPlan
    {
        $this->ensureCanView($actor, $plan);
        abort_unless(in_array($plan->status, ['draft', 'rejected'], true), 409, 'This tour plan cannot be submitted in its current status.');
        DB::transaction(function () use ($actor, $plan) {
            $requester = User::query()->where('company_id', $plan->company_id)->findOrFail($plan->user_id);
            $this->approvalService->submitForApproval((int) $plan->company_id, 'tour_plan', (int) $plan->id, $requester);
            $plan->update(['status' => 'pending', 'reviewed_by' => null, 'reviewed_at' => null, 'review_note' => null]);
        });
        return $this->loadPlan($plan->fresh(), false);
    }

    public function approve(User $actor, TourPlan $plan, array $data): TourPlan
    {
        abort_unless($plan->status === 'pending', 409, 'Only pending plans can be approved.');
        $approval = $this->pendingApprovalFor($actor, $plan);
        $result = $this->approvalService->approve($approval->id, $actor, $data['review_note'] ?? null);
        if ($result->status === 'approved') {
            $plan->update(['status' => 'approved', 'reviewed_by' => $actor->id, 'reviewed_at' => now(), 'review_note' => $data['review_note'] ?? null]);
        }
        return $this->loadPlan($plan->fresh(), false);
    }

    public function reject(User $actor, TourPlan $plan, array $data): TourPlan
    {
        abort_unless($plan->status === 'pending', 409, 'Only pending plans can be rejected.');
        $approval = $this->pendingApprovalFor($actor, $plan);
        $this->approvalService->reject($approval->id, $actor, $data['review_note']);
        $plan->update(['status' => 'rejected', 'reviewed_by' => $actor->id, 'reviewed_at' => now(), 'review_note' => $data['review_note']]);
        return $this->loadPlan($plan->fresh(), false);
    }

    private function pendingApprovalFor(User $actor, TourPlan $plan)
    {
        $query = \App\Models\ApprovalRequest::query()->where('company_id', $plan->company_id)
            ->where('module', 'tour_plan')->where('document_id', $plan->id)->where('status', 'pending')->latest('id');
        $approval = $query->firstOrFail();
        $actorCompanyId = $actor->company_id ?? $actor->employee?->company_id;
        abort_unless((int) $actorCompanyId === (int) $plan->company_id || $this->isSuperAdmin($actor), 404);
        abort_unless($this->approvalService->canApprove($approval, $actor), 403, 'You are not the assigned approver for the active step.');
        return $approval;
    }

    public function cancel(User $actor, TourPlan $plan): TourPlan
    {
        $this->ensureCanView($actor, $plan);
        abort_unless(in_array($plan->status, ['draft', 'pending', 'rejected'], true), 409, 'This tour plan cannot be cancelled in its current status.');
        if ($plan->status === 'pending') {
            $approval = \App\Models\ApprovalRequest::query()->where('company_id', $plan->company_id)
                ->where('module', 'tour_plan')->where('document_id', $plan->id)->where('status', 'pending')->latest('id')->firstOrFail();
            $this->approvalService->cancelForRequester($approval->id, $actor);
        }
        $plan->update(['status' => 'cancelled']);
        return $this->loadPlan($plan->fresh(), false);
    }

    private function replaceStops(TourPlan $plan, array $stops): void
    {
        foreach ($stops as $index => $stop) {
            $plan->stops()->create([
                'geofence_id' => $stop['geofence_id'], 'sequence' => $index + 1,
                'planned_arrival_time' => $stop['planned_arrival_time'] ?? null, 'notes' => $stop['notes'] ?? null,
            ]);
        }
    }

    private function companyId(User $user, mixed $requestedId, bool $required): ?int
    {
        if ($this->isSuperAdmin($user)) {
            if ($required && !$requestedId) throw ValidationException::withMessages(['company_id' => 'Super-admin must select a company.']);
            return $requestedId ? (int) $requestedId : null;
        }
        return $user->company_id ? (int) $user->company_id : null;
    }

    private function visibleUserIds(User $actor, ?int $companyId): array
    {
        if ($this->isSuperAdmin($actor) || $this->isCompanyAdmin($actor)) return User::where('company_id', $companyId)->pluck('id')->all();
        $ids = [$actor->id];
        if (!$companyId) return $ids;
        $nodes = EmployeeHierarchyAssignment::where('user_id', $actor->id)->where('company_id', $companyId)->where('is_current', true)->whereNotNull('hierarchy_node_id')->pluck('hierarchy_node_id');
        $nodeIds = $nodes->flatMap(fn ($id) => $this->hierarchyAccessService->branchIds($companyId, (int) $id))->unique();
        if ($nodeIds->isNotEmpty()) {
            $ids = array_merge($ids, EmployeeHierarchyAssignment::where('company_id', $companyId)->where('is_current', true)->whereIn('hierarchy_node_id', $nodeIds)->pluck('user_id')->all());
        }
        return array_values(array_unique($ids));
    }

    private function ensureCanManageEmployee(User $actor, User $employee, int $companyId): void
    {
        abort_unless((int) $employee->company_id === $companyId, 403, 'Employee is outside this company.');
        abort_unless(in_array($employee->id, $this->visibleUserIds($actor, $companyId), true), 403, 'Employee is outside your hierarchy access.');
    }

    private function ensureCanView(User $actor, TourPlan $plan): void
    {
        abort_unless(in_array($plan->user_id, $this->visibleUserIds($actor, (int) $plan->company_id), true), 403, 'You cannot access this tour plan.');
    }

    private function ensureCanReview(User $actor, TourPlan $plan): void
    {
        $this->ensureCanView($actor, $plan);
        abort_unless($actor->id !== $plan->user_id, 403, 'You cannot approve your own tour plan.');
        if ($this->isSuperAdmin($actor) || $this->isCompanyAdmin($actor)) return;
        $employeeNodeIds = EmployeeHierarchyAssignment::where('user_id', $plan->user_id)->where('company_id', $plan->company_id)->where('is_current', true)->pluck('hierarchy_node_id')->filter();
        $managerNodeIds = EmployeeHierarchyAssignment::where('user_id', $actor->id)->where('company_id', $plan->company_id)->where('is_current', true)->pluck('hierarchy_node_id')->filter();
        $allowed = false;
        foreach ($employeeNodeIds as $employeeNodeId) {
            $node = DB::table('hierarchy_nodes')->where('company_id', $plan->company_id)->where('id', $employeeNodeId)->first();
            while ($node && $node->parent_id) {
                if ($managerNodeIds->contains((int) $node->parent_id)) { $allowed = true; break 2; }
                $node = DB::table('hierarchy_nodes')->where('company_id', $plan->company_id)->where('id', $node->parent_id)->first();
            }
        }
        abort_unless($allowed, 403, 'Only an ancestor manager can review this plan.');
    }

    private function validateStops(array $stops, int $companyId, User $actor): void
    {
        $ids = collect($stops)->pluck('geofence_id')->unique();
        $query = Geofence::query()->where('company_id', $companyId)->whereIn('id', $ids);
        if (!$this->isSuperAdmin($actor) && !$this->isCompanyAdmin($actor)) {
            $nodeIds = EmployeeHierarchyAssignment::where('user_id', $actor->id)->where('company_id', $companyId)->where('is_current', true)->whereNotNull('hierarchy_node_id')->pluck('hierarchy_node_id')
                ->flatMap(fn ($id) => $this->hierarchyAccessService->branchIds($companyId, (int) $id))->unique();
            $query->whereIn('hierarchy_node_id', $nodeIds);
        }
        if ($query->count() !== $ids->count()) {
            throw ValidationException::withMessages(['stops' => 'Every market must belong to your company and accessible hierarchy.']);
        }
    }

    private function loadPlan(TourPlan $plan, bool $includeVisits): TourPlan
    {
        $plan->load(['employee:id,name,email', 'creator:id,name', 'reviewer:id,name', 'stops.geofence:id,firm_name,address,latitude,longitude']);
        $plan->setAttribute('approval_request_id', $plan->approvalRequests()->value('id'));
        if ($includeVisits) {
            $visits = MarketVisit::where('company_id', $plan->company_id)->where('user_id', $plan->user_id)->whereDate('visit_start_time', $plan->plan_date)->get()->keyBy('geofence_id');
            $plan->stops->each(function ($stop) use ($visits) {
                $visit = $visits->get($stop->geofence_id);
                $stop->setAttribute('visited', (bool) $visit);
                $stop->setAttribute('visit_start_time', $visit?->visit_start_time);
                $stop->setAttribute('visit_end_time', $visit?->visit_end_time);
            });
        }
        return $plan;
    }

    private function isSuperAdmin(User $user): bool
    {
        return $user->hasAnyRole(['Super-Admin', 'super-admin']);
    }

    private function isCompanyAdmin(User $user): bool
    {
        return $user->hasAnyRole(['Company Admin', 'company-admin', 'admin']);
    }
}
