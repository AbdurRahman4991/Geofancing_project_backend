<?php

namespace App\Http\Controllers\Api\CountryHierarchy;

use App\Http\Controllers\Controller;
use App\Services\CountryHierarchy\CountryHiearchyLevelService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class CountryHiearchyLevelController extends Controller
{
    public function __construct(private readonly CountryHiearchyLevelService $service) {}

    public function index(Request $request)
    {
        $this->authorizeAction('view');
        return response()->json(['status' => 200, 'data' => $this->service->paginate($this->companyId($request), $request->only('is_active', 'search', 'per_page'))]);
    }

    public function store(Request $request)
    {
        $this->authorizeAction('create');
        $data = $request->validate(['name' => ['required', 'string', 'max:150'], 'code' => ['nullable', 'string', 'max:100'], 'level_order' => ['required', 'integer', 'min:1'], 'is_active' => ['sometimes', 'boolean']]);
        return response()->json(['status' => 201, 'message' => 'Country hierarchy level created successfully.', 'data' => $this->service->create($this->companyId($request), $data)], 201);
    }

    public function show(Request $request, int $id)
    {
        $this->authorizeAction('edit');
        return response()->json(['status' => 200, 'data' => $this->service->find($this->companyId($request), $id)]);
    }

    public function update(Request $request, int $id)
    {
        $this->authorizeAction('update');
        $data = $request->validate(['name' => ['sometimes', 'required', 'string', 'max:150'], 'code' => ['sometimes', 'nullable', 'string', 'max:100'], 'level_order' => ['sometimes', 'required', 'integer', 'min:1'], 'is_active' => ['sometimes', 'boolean']]);
        return response()->json(['status' => 200, 'message' => 'Country hierarchy level updated successfully.', 'data' => $this->service->update($this->companyId($request), $id, $data)]);
    }

    public function destroy(Request $request, int $id)
    {
        $this->authorizeAction('delete');
        $this->service->delete($this->companyId($request), $id);
        return response()->json(['status' => 200, 'message' => 'Country hierarchy level deleted successfully.']);
    }

    private function authorizeAction(string $action): void
    {
        abort_unless(auth()->user()?->can('country-hierarchy-level.' . $action), 403, 'You do not have permission to ' . $action . ' country hierarchy levels.');
    }

    private function companyId(Request $request): int
    {
        $user = $request->user();
        $companyId = $user?->hasRole('Super-Admin') ? $request->input('company_id') : ($user?->company_id ?? $user?->employee?->company_id);
        if (!$companyId) throw ValidationException::withMessages(['company_id' => 'A company_id is required for this account.']);
        return (int) $companyId;
    }
}
