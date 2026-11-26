<?php

namespace App\Http\Controllers\Api\Market;

use App\Http\Controllers\Controller;
use App\Services\Market\MarketGeographyNodeAssignService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class MarketGeographyNodeAssignController extends Controller
{
    public function __construct(private readonly MarketGeographyNodeAssignService $service) {}

    public function index(Request $request)
    {
        $this->authorizeAction('view');
        return response()->json(['status' => 200, 'data' => $this->service->paginate($this->companyId($request), $request->only('market_node_id', 'country_node_id', 'is_active', 'per_page'))]);
    }

    public function store(Request $request)
    {
        $this->authorizeAction('create');
        $data = $request->validate([
            'market_node_id' => ['required', 'integer', 'exists:hierarchy_nodes,id'],
            'country_node_id' => ['required', 'integer', 'different:market_node_id', 'exists:hierarchy_nodes,id'],
            'is_active' => ['sometimes', 'boolean'],
        ]);
        return response()->json(['status' => 201, 'message' => 'Market geography node assignment created successfully.', 'data' => $this->service->create($this->companyId($request), $data)], 201);
    }

    public function show(Request $request, int $id)
    {
        $this->authorizeAction('view');
        return response()->json(['status' => 200, 'data' => $this->service->find($this->companyId($request), $id)]);
    }

    public function update(Request $request, int $id)
    {
        $this->authorizeAction('update');
        $data = $request->validate([
            'market_node_id' => ['sometimes', 'required', 'integer', 'exists:hierarchy_nodes,id'],
            'country_node_id' => ['sometimes', 'required', 'integer', 'different:market_node_id', 'exists:hierarchy_nodes,id'],
            'is_active' => ['sometimes', 'boolean'],
        ]);
        return response()->json(['status' => 200, 'message' => 'Market geography node assignment updated successfully.', 'data' => $this->service->update($this->companyId($request), $id, $data)]);
    }

    public function destroy(Request $request, int $id)
    {
        $this->authorizeAction('delete');
        $this->service->delete($this->companyId($request), $id);
        return response()->json(['status' => 200, 'message' => 'Market geography node assignment deleted successfully.']);
    }

    private function authorizeAction(string $action): void
    {
        abort_unless(auth()->user()?->can('market-geography-node-assign.' . $action), 403, 'You do not have permission to ' . $action . ' market geography node assignments.');
    }

    private function companyId(Request $request): int
    {
        $user = $request->user();
        $companyId = $user?->hasRole('Super-Admin') ? $request->input('company_id') : ($user?->company_id ?? $user?->employee?->company_id);
        if (!$companyId) {
            throw ValidationException::withMessages(['company_id' => 'A company_id is required for this account.']);
        }
        return (int) $companyId;
    }
}
