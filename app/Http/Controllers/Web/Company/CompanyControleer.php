<?php

namespace App\Http\Controllers\Web\Company;

use App\Http\Controllers\Controller;
use App\Services\Company\CompanyService;
use Illuminate\Http\Request;

class CompanyControleer extends Controller
{
    protected CompanyService $companyService;

    public function __construct(CompanyService $companyService)
    {
        $this->companyService = $companyService;
    }

    public function create()
    {
        return view('company.create');
    }

    public function store(Request $request)
    {
        $company = $this->companyService->store($request);

        return redirect()
            ->route('company.create')
            ->with('success', 'Company created successfully.');
    }
}
