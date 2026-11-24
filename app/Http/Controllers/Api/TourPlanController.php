<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\EmployeeHierarchyAssignment;
use App\Models\Geofence;
use App\Models\MarketVisit;
use App\Models\TourPlan;
use App\Models\User;
use App\Services\Hierarchy\HierarchyAccessService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TourPlanController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $request->validate([
            'company_id' => [$this->isSuperAdmin($user) ? 'nullable' : 'prohibited', 'integer', 'exists:companies,id'],
            'user_id' => ['nullable', 'integer'],
            'status' => ['nullable', 'in:draft,pending,approved,rejected,cancelled'],
            'from_date' => ['nullable', 'date'],
            'to_date' => ['nullable', 'date', 'after_or_equal:from_date'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);
        $companyId = $this->companyId($user, $request, false);
        $query = TourPlan::query()->with(['employee:id,name,email', 'creator:id,name', 'reviewer:id,name', 'stops.geofence:id,firm_name,address,latitude,longitude']);

        if ($companyId) {
            $query->where('company_id', $companyId);
        } elseif (!$this->isSuperAdmin($user)) {
            abort(403, 'Company not found for this user.');
        }

        if (!$this->isCompanyAdmin($user) && !$this->isSuperAdmin($user)) {
            $visibleUserIds = $this->visibleUserIds($user, $companyId);
            $query->whereIn('user_id', $visibleUserIds);
        }

        foreach (['user_id', 'status'] as $filter) {
            if ($request->filled($filter)) {
                $query->where($filter, $request->input($filter));
            }
        }
        if ($request->filled('from_date')) {
            $query->whereDate('plan_date', '>=', $request->input('from_date'));
        }
        if ($request->filled('to_date')) {
            $query->whereDate('plan_date', '<=', $request->input('to_date'));
        }

        $plans = $query->orderByDesc('plan_date')->paginate($request->integer('per_page', 15));
        $plans->getCollection()->transform(fn (TourPlan $plan) => $this->loadPlan($plan, true));
        return response()->json($plans);
    }

    public function store(Request $request)
    {
        $user = $request->user();
        $data = $request->validate([
            'company_id' => [$this->isSuperAdmin($user) ? 'required' : 'prohibited', 'integer', 'exists:companies,id'],
            'user_id' => ['nullable', 'integer', 'exists:users,id'],
            'plan_date' => ['required', 'date'],
            'notes' => ['nullable', 'string', 'max:5000'],
            'stops' => ['required', 'array', 'min:1', 'max:100'],
            'stops.*.geofence_id' => ['required', 'integer', 'distinct', 'exists:geofences,id'],
            'stops.*.planned_arrival_time' => ['nullable', 'date_format:H:i'],
            'stops.*.notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $companyId = $this->companyId($user, $request, true);
        $employee = User::query()->where('company_id', $companyId)->findOrFail($data['user_id'] ?? $user->id);
        $this->ensureCanManageEmployee($user, $employee, $companyId);
        $geofences = $this->validateStops($data['stops'], $companyId, $user);

        $plan = DB::transaction(function () use ($data, $user, $employee, $companyId, $geofences) {
            $plan = TourPlan::create([
                'company_id' => $companyId,
                'user_id' => $employee->id,
                'created_by' => $user->id,
                'plan_date' => $data['plan_date'],
                'status' => 'draft',
                'notes' => $data['notes'] ?? null,
            ]);
            foreach ($data['stops'] as $index => $stop) {
                $plan->stops()->create([
                    'geofence_id' => $stop['geofence_id'],
                    'sequence' => $index + 1,
                    'planned_arrival_time' => $stop['planned_arrival_time'] ?? null,
                    'notes' => $stop['notes'] ?? null,
                ]);
            }
            return $plan;
        });

        return response()->json(['message' => 'Tour plan created.', 'data' => $this->loadPlan($plan, true)], 201);
    }

    public function show(Request $request, TourPlan $tourPlan)
    {
        $this->ensureCanView($request->user(), $tourPlan);
        return response()->json(['data' => $this->loadPlan($tourPlan, true)]);
    }

    public function update(Request $request, TourPlan $tourPlan)
    {
        $user = $request->user();
        $this->ensureCanView($user, $tourPlan);
        abort_unless(in_array($tourPlan->status, ['draft', 'rejected'], true), 409, 'Only draft or rejected plans can be edited.');

        $data = $request->validate([
            'plan_date' => ['required', 'date'],
            'notes' => ['nullable', 'string', 'max:5000'],
            'stops' => ['required', 'array', 'min:1', 'max:100'],
            'stops.*.geofence_id' => ['required', 'integer', 'distinct', 'exists:geofences,id'],
            'stops.*.planned_arrival_time' => ['nullable', 'date_format:H:i'],
            'stops.*.notes' => ['nullable', 'string', 'max:2000'],
        ]);
        $this->ensureCanManageEmployee($user, $tourPlan->employee, (int) $tourPlan->company_id);
        $this->validateStops($data['stops'], (int) $tourPlan->company_id, $user);

        DB::transaction(function () use ($tourPlan, $data) {
            $tourPlan->update(['plan_date' => $data['plan_date'], 'notes' => $data['notes'] ?? null, 'status' => 'draft', 'reviewed_by' => null, 'reviewed_at' => null, 'review_note' => null]);
            $tourPlan->stops()->delete();
            foreach ($data['stops'] as $index => $stop) {
                $tourPlan->stops()->create([
                    'geofence_id' => $stop['geofence_id'], 'sequence' => $index + 1,
                    'planned_arrival_time' => $stop['planned_arrival_time'] ?? null, 'notes' => $stop['notes'] ?? null,
                ]);
            }
        });
        return response()->json(['message' => 'Tour plan updated.', 'data' => $this->loadPlan($tourPlan->fresh(), true)]);
    }

    public function submit(Request $request, TourPlan $tourPlan)
    {
        $this->ensureCanView($request->user(), $tourPlan);
        abort_unless(in_array($tourPlan->status, ['draft', 'rejected'], true), 409, 'This tour plan cannot be submitted in its current status.');
        $tourPlan->update(['status' => 'pending', 'reviewed_by' => null, 'reviewed_at' => null, 'review_note' => null]);
        return response()->json(['message' => 'Tour plan submitted for approval.', 'data' => $this->loadPlan($tourPlan->fresh(), false)]);
    }

    public function approve(Request $request, TourPlan $tourPlan)
    {
        $data = $request->validate(['review_note' => ['nullable', 'string', 'max:2000']]);
        $this->ensureCanReview($request->user(), $tourPlan);
        abort_unless($tourPlan->status === 'pending', 409, 'Only pending plans can be approved.');
        $tourPlan->update(['status' => 'approved', 'reviewed_by' => $request->user()->id, 'reviewed_at' => now(), 'review_note' => $data['review_note'] ?? null]);
        return response()->json(['message' => 'Tour plan approved.', 'data' => $this->loadPlan($tourPlan->fresh(), false)]);
    }

    public function reject(Request $request, TourPlan $tourPlan)
    {
        $data = $request->validate(['review_note' => ['required', 'string', 'max:2000']]);
        $this->ensureCanReview($request->user(), $tourPlan);
        abort_unless($tourPlan->status === 'pending', 409, 'Only pending plans can be rejected.');
        $tourPlan->update(['status' => 'rejected', 'reviewed_by' => $request->user()->id, 'reviewed_at' => now(), 'review_note' => $data['review_note']]);
        return response()->json(['message' => 'Tour plan rejected.', 'data' => $this->loadPlan($tourPlan->fresh(), false)]);
    }

    public function cancel(Request $request, TourPlan $tourPlan)
    {
        $this->ensureCanView($request->user(), $tourPlan);
        abort_unless(in_array($tourPlan->status, ['draft', 'pending', 'rejected'], true), 409, 'This tour plan cannot be cancelled in its current status.');
        $tourPlan->update(['status' => 'cancelled']);
        return response()->json(['message' => 'Tour plan cancelled.', 'data' => $this->loadPlan($tourPlan->fresh(), false)]);
    }

    private function companyId(User $user, Request $request, bool $required): ?int
    {
        if ($this->isSuperAdmin($user)) {
            $id = $request->input('company_id');
            if ($required && !$id) {
                throw ValidationException::withMessages(['company_id' => 'Super-admin must select a company.']);
            }
            return $id ? (int) $id : null;
        }
        return $user->company_id ? (int) $user->company_id : null;
    }

    private function visibleUserIds(User $actor, ?int $companyId): array
    {
        if ($this->isSuperAdmin($actor) || $this->isCompanyAdmin($actor)) {
            return User::where('company_id', $companyId)->pluck('id')->all();
        }
        $ids = [$actor->id];
        if (!$companyId) {
            return $ids;
        }
        $access = app(HierarchyAccessService::class);
        $nodes = EmployeeHierarchyAssignment::where('user_id', $actor->id)->where('company_id', $companyId)->where('is_current', true)->whereNotNull('hierarchy_node_id')->pluck('hierarchy_node_id');
        $nodeIds = $nodes->flatMap(fn ($id) => $access->branchIds($companyId, (int) $id))->unique();
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
        if ($this->isSuperAdmin($actor) || $this->isCompanyAdmin($actor)) {
            return;
        }

        $employeeNodeIds = EmployeeHierarchyAssignment::where('user_id', $plan->user_id)->where('company_id', $plan->company_id)->where('is_current', true)->pluck('hierarchy_node_id')->filter();
        $managerNodeIds = EmployeeHierarchyAssignment::where('user_id', $actor->id)->where('company_id', $plan->company_id)->where('is_current', true)->pluck('hierarchy_node_id')->filter();
        $allowed = false;
        foreach ($employeeNodeIds as $employeeNodeId) {
            $node = DB::table('hierarchy_nodes')->where('company_id', $plan->company_id)->where('id', $employeeNodeId)->first();
            while ($node && $node->parent_id) {
                if ($managerNodeIds->contains((int) $node->parent_id)) {
                    $allowed = true;
                    break 2;
                }
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
            $access = app(HierarchyAccessService::class);
            $nodeIds = EmployeeHierarchyAssignment::where('user_id', $actor->id)->where('company_id', $companyId)->where('is_current', true)->whereNotNull('hierarchy_node_id')->pluck('hierarchy_node_id')
                ->flatMap(fn ($id) => $access->branchIds($companyId, (int) $id))->unique();
            $query->whereIn('hierarchy_node_id', $nodeIds);
        }
        if ($query->count() !== $ids->count()) {
            throw ValidationException::withMessages(['stops' => 'Every market must belong to your company and accessible hierarchy.']);
        }
    }

    private function loadPlan(TourPlan $plan, bool $includeVisits): TourPlan
    {
        $plan->load(['employee:id,name,email', 'creator:id,name', 'reviewer:id,name', 'stops.geofence:id,firm_name,address,latitude,longitude']);
        if ($includeVisits) {
            $visits = MarketVisit::where('company_id', $plan->company_id)->where('user_id', $plan->user_id)
                ->whereDate('visit_start_time', $plan->plan_date)->get()->keyBy('geofence_id');
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
