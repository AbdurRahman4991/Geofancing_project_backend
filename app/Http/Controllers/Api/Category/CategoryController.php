<?php

namespace App\Http\Controllers\Api\Category;

use App\Http\Controllers\Controller;
use App\Services\Category\CategoryService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class CategoryController extends Controller
{
    public function __construct(private readonly CategoryService $service)
    {
    }

    public function index(Request $request)
    {
        $this->authorizeAction('view');

        return response()->json([
            'status' => 200,
            'data' => $this->service->paginate($this->companyId($request), $request->only('status', 'search', 'per_page')),
        ]);
    }

    public function store(Request $request)
    {
        $this->authorizeAction('create');
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['sometimes', 'nullable', 'string', 'max:5000'],
            'status' => ['sometimes', 'in:active,inactive'],
        ]);

        return response()->json([
            'status' => 201,
            'message' => 'Category created successfully.',
            'data' => $this->service->create($this->companyId($request), $data),
        ], 201);
    }

    public function show(Request $request, int $id)
    {
        $this->authorizeAction('edit');

        return response()->json(['status' => 200, 'data' => $this->service->find($this->companyId($request), $id)]);
    }

    public function update(Request $request, int $id)
    {
        $this->authorizeAction('update');
        $data = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['sometimes', 'nullable', 'string', 'max:5000'],
            'status' => ['sometimes', 'in:active,inactive'],
        ]);

        return response()->json([
            'status' => 200,
            'message' => 'Category updated successfully.',
            'data' => $this->service->update($this->companyId($request), $id, $data),
        ]);
    }

    public function destroy(Request $request, int $id)
    {
        $this->authorizeAction('delete');
        $this->service->delete($this->companyId($request), $id);

        return response()->json(['status' => 200, 'message' => 'Category deleted successfully.']);
    }

    private function authorizeAction(string $action): void
    {
        abort_unless(auth()->user()?->can('category.' . $action), 403, 'You do not have permission to ' . $action . ' categories.');
    }

    private function companyId(Request $request): int
    {
        $user = $request->user();
        $companyId = $user?->hasRole('Super-Admin')
            ? ($request->input('company_id') ?? $request->query('company_id'))
            : ($user?->company_id ?? $user?->employee?->company_id);

        if (!$companyId) {
            throw ValidationException::withMessages([
                'company_id' => 'A company_id is required for this account.',
            ]);
        }

        return (int) $companyId;
    }
}
