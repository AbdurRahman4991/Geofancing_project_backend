<?php

namespace App\Http\Controllers\Api\Hierarchy;

use App\Http\Controllers\Controller;
use App\Services\Hierarchy\HierarchyNodeService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class HierarchyNodeController extends Controller
{
    public function __construct(private readonly HierarchyNodeService $service)
    {
    }

    public function index(Request $request)
    {
        $this->authorizeAction('view');
        $filters = $request->only('hierarchy_level_id', 'parent_id', 'status', 'search', 'per_page');

        return response()->json([
            'status' => 200,
            'data' => $this->service->paginate($this->companyId($request), $filters),
        ]);
    }

    public function store(Request $request)
    {
        $this->authorizeAction('create');
        $data = $request->validate([
            'hierarchy_level_id' => ['required', 'integer', 'exists:hierarchy_levels,id'],
            'parent_id' => ['nullable', 'integer', 'exists:hierarchy_nodes,id'],
            'name' => ['required', 'string', 'max:150'],
            'code' => ['nullable', 'string', 'max:100'],
            'status' => ['sometimes', 'in:active,inactive'],
        ]);

        return response()->json([
            'status' => 201,
            'message' => 'Hierarchy node created successfully.',
            'data' => $this->service->create($this->companyId($request), $data),
        ], 201);
    }

    public function show(Request $request, int $id)
    {
        $this->authorizeAction('view');

        return response()->json([
            'status' => 200,
            'data' => $this->service->find($this->companyId($request), $id),
        ]);
    }

    public function update(Request $request, int $id)
    {
        $this->authorizeAction('edit');
        $data = $request->validate([
            'hierarchy_level_id' => ['sometimes', 'required', 'integer', 'exists:hierarchy_levels,id'],
            'parent_id' => ['sometimes', 'nullable', 'integer', 'exists:hierarchy_nodes,id'],
            'name' => ['sometimes', 'required', 'string', 'max:150'],
            'code' => ['sometimes', 'nullable', 'string', 'max:100'],
            'status' => ['sometimes', 'in:active,inactive'],
        ]);

        return response()->json([
            'status' => 200,
            'message' => 'Hierarchy node updated successfully.',
            'data' => $this->service->update($this->companyId($request), $id, $data),
        ]);
    }

    public function destroy(Request $request, int $id)
    {
        $this->authorizeAction('delete');
        $this->service->delete($this->companyId($request), $id);

        return response()->json([
            'status' => 200,
            'message' => 'Hierarchy node deleted successfully.',
        ]);
    }

    private function authorizeAction(string $action): void
    {
        abort_unless(auth()->user()?->can('hierarchy-node.' . $action), 403, 'You do not have permission to ' . $action . ' hierarchy nodes.');
    }

    private function companyId(Request $request): int
    {
        $user = $request->user();
        $companyId = $user?->hasRole('Super-Admin')
            ? $request->input('company_id')
            : ($user?->company_id ?? $user?->employee?->company_id);

        if (!$companyId) {
            throw ValidationException::withMessages([
                'company_id' => 'A company_id is required for this account.',
            ]);
        }

        return (int) $companyId;
    }
}
