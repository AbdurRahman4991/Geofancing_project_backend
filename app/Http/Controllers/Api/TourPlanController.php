<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TourPlan;
use App\Services\TourPlan\TourPlanService;
use Illuminate\Http\Request;

class TourPlanController extends Controller
{
    public function __construct(private readonly TourPlanService $service) {}

    public function index(Request $request)
    {
        $this->authorizeAction($request, 'view');
        $data = $request->validate([
            'company_id' => [$this->isSuperAdmin($request) ? 'nullable' : 'prohibited', 'integer', 'exists:companies,id'],
            'user_id' => ['nullable', 'integer'], 'status' => ['nullable', 'in:draft,pending,approved,rejected,cancelled'],
            'from_date' => ['nullable', 'date'], 'to_date' => ['nullable', 'date', 'after_or_equal:from_date'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);
        return response()->json($this->service->index($request->user(), $data));
    }

    public function store(Request $request)
    {
        $this->authorizeAction($request, 'create');
        $data = $request->validate([
            'company_id' => [$this->isSuperAdmin($request) ? 'required' : 'prohibited', 'integer', 'exists:companies,id'],
            'user_id' => ['nullable', 'integer', 'exists:users,id'], 'plan_date' => ['required', 'date'],
            'notes' => ['nullable', 'string', 'max:5000'], 'stops' => ['required', 'array', 'min:1', 'max:100'],
            'stops.*.geofence_id' => ['required', 'integer', 'distinct', 'exists:geofences,id'],
            'stops.*.planned_arrival_time' => ['nullable', 'date_format:H:i'], 'stops.*.notes' => ['nullable', 'string', 'max:2000'],
        ]);
        return response()->json(['message' => 'Tour plan created.', 'data' => $this->service->store($request->user(), $data)], 201);
    }

    public function show(Request $request, TourPlan $tourPlan)
    {
        $this->authorizeAction($request, 'view');
        return response()->json(['data' => $this->service->show($request->user(), $tourPlan)]);
    }

    public function update(Request $request, TourPlan $tourPlan)
    {
        $this->authorizeAction($request, 'update');
        $data = $request->validate([
            'plan_date' => ['required', 'date'], 'notes' => ['nullable', 'string', 'max:5000'],
            'stops' => ['required', 'array', 'min:1', 'max:100'],
            'stops.*.geofence_id' => ['required', 'integer', 'distinct', 'exists:geofences,id'],
            'stops.*.planned_arrival_time' => ['nullable', 'date_format:H:i'], 'stops.*.notes' => ['nullable', 'string', 'max:2000'],
        ]);
        return response()->json(['message' => 'Tour plan updated.', 'data' => $this->service->update($request->user(), $tourPlan, $data)]);
    }

    public function submit(Request $request, TourPlan $tourPlan)
    {
        $this->authorizeAction($request, 'submit');
        return response()->json(['message' => 'Tour plan submitted for approval.', 'data' => $this->service->submit($request->user(), $tourPlan)]);
    }

    public function approve(Request $request, TourPlan $tourPlan)
    {
        $this->authorizeAction($request, 'approve');
        $data = $request->validate(['review_note' => ['nullable', 'string', 'max:2000']]);
        return response()->json(['message' => 'Tour plan approved.', 'data' => $this->service->approve($request->user(), $tourPlan, $data)]);
    }

    public function reject(Request $request, TourPlan $tourPlan)
    {
        $this->authorizeAction($request, 'reject');
        $data = $request->validate(['review_note' => ['required', 'string', 'max:2000']]);
        return response()->json(['message' => 'Tour plan rejected.', 'data' => $this->service->reject($request->user(), $tourPlan, $data)]);
    }

    public function cancel(Request $request, TourPlan $tourPlan)
    {
        $this->authorizeAction($request, 'cancel');
        return response()->json(['message' => 'Tour plan cancelled.', 'data' => $this->service->cancel($request->user(), $tourPlan)]);
    }

    private function authorizeAction(Request $request, string $action): void
    {
        abort_unless($request->user()?->can('tour-plan.' . $action), 403, 'You do not have permission to ' . $action . ' tour plans.');
    }

    private function isSuperAdmin(Request $request): bool
    {
        return $request->user()->hasAnyRole(['Super-Admin', 'super-admin']);
    }
}
