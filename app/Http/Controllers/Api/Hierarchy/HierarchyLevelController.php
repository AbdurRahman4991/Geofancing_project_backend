<?php

namespace App\Http\Controllers\Api\Hierarchy;

use App\Http\Controllers\Controller;
use App\Services\Hierarchy\HierarchyLevelService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class HierarchyLevelController extends Controller
{
    public function __construct(private readonly HierarchyLevelService $service)
    {
    }

    public function index(Request $request)
    {
        $this->authorizeAction('view');
        $filters = $request->only('is_active', 'search', 'per_page');

        return response()->json([
            'status' => 200,
            'data' => $this->service->paginate($this->companyId($request), $filters),
        ]);
    }

    public function store(Request $request)
    {
        $this->authorizeAction('create');
        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'code' => ['nullable', 'string', 'max:100'],
            'level_order' => ['required', 'integer', 'min:1'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        return response()->json([
            'status' => 201,
            'message' => 'Hierarchy level created successfully.',
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
            'name' => ['sometimes', 'required', 'string', 'max:150'],
            'code' => ['sometimes', 'nullable', 'string', 'max:100'],
            'level_order' => ['sometimes', 'required', 'integer', 'min:1'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        return response()->json([
            'status' => 200,
            'message' => 'Hierarchy level updated successfully.',
            'data' => $this->service->update($this->companyId($request), $id, $data),
        ]);
    }

    public function destroy(Request $request, int $id)
    {
        $this->authorizeAction('delete');
        $this->service->delete($this->companyId($request), $id);

        return response()->json([
            'status' => 200,
            'message' => 'Hierarchy level deleted successfully.',
        ]);
    }

    private function authorizeAction(string $action): void
    {
        abort_unless(auth()->user()?->can('hierarchy-level.' . $action), 403, 'You do not have permission to ' . $action . ' hierarchy levels.');
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