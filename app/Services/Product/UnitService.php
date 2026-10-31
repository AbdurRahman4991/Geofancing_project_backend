<?php

namespace App\Services\Product;

use App\Models\Unit;
use App\Traits\ScopesCompanyAccess;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UnitService
{
    use ScopesCompanyAccess;

    public function index(Request $request)
    {
        $query = $this->companyQuery($this->companyId($request));

        if ($request->filled('search')) {
            $search = $request->string('search')->trim()->toString();
            $query->where(function (Builder $builder) use ($search) {
                $builder->where('unit_name', 'like', "%{$search}%")
                    ->orWhere('short_name', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        return $query->orderBy('unit_name')
            ->paginate(min(max($request->integer('per_page', 10), 1), 100))
            ->withQueryString();
    }

    public function store(Request $request): Unit
    {
        $companyId = $this->companyId($request);

        return Unit::create($this->validatedData($request, $companyId) + ['company_id' => $companyId]);
    }

    public function show(Request $request, int $id): Unit
    {
        return $this->companyQuery($this->companyId($request))->findOrFail($id);
    }

    public function update(Request $request, int $id): Unit
    {
        $companyId = $this->companyId($request);
        $unit = $this->companyQuery($companyId)->findOrFail($id);
        $unit->update($this->validatedData($request, $companyId, $unit));

        return $unit->refresh();
    }

    public function destroy(Request $request, int $id): void
    {
        $this->companyQuery($this->companyId($request))->findOrFail($id)->delete();
    }

    private function validatedData(Request $request, int $companyId, ?Unit $unit = null): array
    {
        return $request->validate([
            'unit_name' => [
                $unit ? 'sometimes' : 'required',
                'string',
                'max:255',
                Rule::unique('units', 'unit_name')->where('company_id', $companyId)->ignore($unit?->id),
            ],
            'short_name' => [
                $unit ? 'sometimes' : 'required',
                'string',
                'max:50',
                Rule::unique('units', 'short_name')->where('company_id', $companyId)->ignore($unit?->id),
            ],
            'status' => ['sometimes', 'in:active,inactive'],
        ]);
    }

    private function companyQuery(int $companyId): Builder
    {
        $query = Unit::query();
        $this->scopeToCurrentCompany($query);

        return $query->where('units.company_id', $companyId);
    }

    private function companyId(Request $request): int
    {
        $user = $request->user();
        $companyId = $user?->hasRole('Super-Admin')
            ? ($request->input('company_id') ?? $request->query('company_id'))
            : ($user?->company_id ?? $user?->employee?->company_id);

        abort_unless($companyId, 422, 'A company_id is required for this account.');

        return (int) $companyId;
    }
}
