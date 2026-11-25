<?php

namespace App\Http\Controllers\Api\CountryHierarchy;

use App\Http\Controllers\Controller;
use App\Services\CountryHierarchy\CountryHiearchyNodeService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class CountryHiearchyNodeController extends Controller
{
    public function __construct(private readonly CountryHiearchyNodeService $service) {}

    public function index(Request $request)
    {
        $this->authorizeAction('view');
        return response()->json(['status' => 200, 'data' => $this->service->paginate($this->companyId($request), $request->only('hierarchy_level_id', 'parent_id', 'status', 'search', 'per_page'))]);
    }

    public function store(Request $request)
    {
        $this->authorizeAction('create');
        $data = $request->validate(['hierarchy_level_id' => ['required', 'integer', 'exists:country_hiearchy_levels,id'], 'parent_id' => ['nullable', 'integer', 'exists:country_hiearchy_nodes,id'], 'name' => ['required', 'string', 'max:150'], 'code' => ['nullable', 'string', 'max:100'], 'status' => ['sometimes', 'in:active,inactive'], 'latitude' => ['nullable', 'numeric', 'between:-90,90'], 'longitude' => ['nullable', 'numeric', 'between:-180,180']]);
        return response()->json(['status' => 201, 'message' => 'Country hierarchy node created successfully.', 'data' => $this->service->create($this->companyId($request), $data)], 201);
    }

    public function show(Request $request, int $id)
    {
        $this->authorizeAction('edit');
        return response()->json(['status' => 200, 'data' => $this->service->find($this->companyId($request), $id)]);
    }

    public function update(Request $request, int $id)
    {
        $this->authorizeAction('update');
        $data = $request->validate(['hierarchy_level_id' => ['sometimes', 'required', 'integer', 'exists:country_hiearchy_levels,id'], 'parent_id' => ['sometimes', 'nullable', 'integer', 'exists:country_hiearchy_nodes,id'], 'name' => ['sometimes', 'required', 'string', 'max:150'], 'code' => ['sometimes', 'nullable', 'string', 'max:100'], 'status' => ['sometimes', 'in:active,inactive'], 'latitude' => ['sometimes', 'nullable', 'numeric', 'between:-90,90'], 'longitude' => ['sometimes', 'nullable', 'numeric', 'between:-180,180']]);
        return response()->json(['status' => 200, 'message' => 'Country hierarchy node updated successfully.', 'data' => $this->service->update($this->companyId($request), $id, $data)]);
    }

    public function destroy(Request $request, int $id)
    {
        $this->authorizeAction('delete');
        $this->service->delete($this->companyId($request), $id);
        return response()->json(['status' => 200, 'message' => 'Country hierarchy node deleted successfully.']);
    }

    private function authorizeAction(string $action): void
    {
        abort_unless(auth()->user()?->can('country-hierarchy-node.' . $action), 403, 'You do not have permission to ' . $action . ' country hierarchy nodes.');
    }

    private function companyId(Request $request): int
    {
        $user = $request->user();
        $companyId = $user?->hasRole('Super-Admin') ? $request->input('company_id') : ($user?->company_id ?? $user?->employee?->company_id);
        if (!$companyId) throw ValidationException::withMessages(['company_id' => 'A company_id is required for this account.']);
        return (int) $companyId;
    }
}
