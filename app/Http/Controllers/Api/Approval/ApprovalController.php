<?php

namespace App\Http\Controllers\Api\Approval;

use App\Http\Controllers\Controller;
use App\Services\Approval\ApprovalService;
use Illuminate\Http\Request;

class ApprovalController extends Controller
{
    public function __construct(private readonly ApprovalService $service) {}

    public function pending(Request $request)
    {
        $this->authorizeAction($request, 'view');
        return response()->json(['data' => $this->service->getPendingApprovals($request->user())]);
    }

    public function show(Request $request, int $id)
    {
        $this->authorizeAction($request, 'view');
        return response()->json(['data' => $this->service->getApproval($id, $request->user())]);
    }

    public function approve(Request $request, int $id)
    {
        $this->authorizeAction($request, 'approve');
        $data = $request->validate(['remarks' => ['nullable', 'string', 'max:2000']]);
        return response()->json(['message' => 'Approval step approved.', 'data' => $this->service->approve($id, $request->user(), $data['remarks'] ?? null)]);
    }

    public function reject(Request $request, int $id)
    {
        $this->authorizeAction($request, 'reject');
        $data = $request->validate(['remarks' => ['required', 'string', 'max:2000']]);
        return response()->json(['message' => 'Approval request rejected.', 'data' => $this->service->reject($id, $request->user(), $data['remarks'])]);
    }

    public function history(Request $request, int $id)
    {
        $this->authorizeAction($request, 'view');
        return response()->json(['data' => $this->service->getApprovalHistory($id, $request->user())]);
    }

    private function authorizeAction(Request $request, string $action): void
    {
        abort_unless($request->user()?->can('approval.' . $action), 403, 'You do not have permission to access approvals.');
    }
}
