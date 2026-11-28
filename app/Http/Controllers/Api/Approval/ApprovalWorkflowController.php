<?php

namespace App\Http\Controllers\Api\Approval;

use App\Http\Controllers\Controller;
use App\Http\Requests\ApprovalWorkflowRequest;
use App\Services\Approval\ApprovalWorkflowService;
use Illuminate\Http\Request;

class ApprovalWorkflowController extends Controller
{
    public function __construct(private readonly ApprovalWorkflowService $service) {}

    public function index(Request $request) { $this->authorizeAction($request, 'view'); return response()->json(['data' => $this->service->index($request)]); }
    public function store(ApprovalWorkflowRequest $request) { $this->authorizeAction($request, 'create'); return response()->json(['message' => 'Approval workflow created.', 'data' => $this->service->store($request)], 201); }
    public function show(Request $request, int $id) { $this->authorizeAction($request, 'view'); return response()->json(['data' => $this->service->show($request, $id)]); }
    public function update(ApprovalWorkflowRequest $request, int $id) { $this->authorizeAction($request, 'update'); return response()->json(['message' => 'Approval workflow updated.', 'data' => $this->service->update($request, $id)]); }
    public function destroy(Request $request, int $id) { $this->authorizeAction($request, 'delete'); $this->service->destroy($request, $id); return response()->json(['message' => 'Approval workflow deleted.']); }

    private function authorizeAction(Request $request, string $action): void
    {
        abort_unless($request->user()?->can('approval-workflow.' . $action), 403, 'You do not have permission to manage approval workflows.');
    }
}
