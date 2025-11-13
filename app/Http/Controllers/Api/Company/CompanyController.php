<?php

namespace App\Http\Controllers\Api\Company;
use App\Services\Company\CompanyService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CompanyController extends Controller
{
      protected $companyService;

    public function __construct(CompanyService $companyService)
    {
        $this->companyService = $companyService;
    }

    public function index(Request $request)
    {
        try {
            $companies = $this->companyService->all($request);

            return response()->json([
                'status' => 200,
                'message' => 'Company list retrieved successfully',
                'data' => $companies,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to fetch company list',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function store(Request $request)
    {
        $request->validate([
            'company_name' => 'required|string|max:255',
            'email' => 'nullable|email',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
            'avatar' => 'nullable|image|max:2048',
            
        ]);

        try {
            $company = $this->companyService->store($request);

            return response()->json([
                'status' => 200,
                'message' => 'Company created successfully',
                'data' => $company,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to create company',
                'error' => $e->getMessage(),
            ], 500);
        }
       
    }

    public function show($id)
    {       
         try {
            $company = $this->companyService->show($id);

            return response()->json([
                'status' => 200,
                'message' => 'Company retrieved successfully',
                'data' => $company,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Company not found',
                'error' => $e->getMessage(),
            ], 404);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $company = $this->companyService->update($request, $id);

            return response()->json([
                'status' => 200,
                'message' => 'Company updated successfully',
                'data' => $company,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to update company',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function destroy($id)
    {
       try {
            $this->companyService->destroy($id);

            return response()->json([
                'status' => 200,
                'message' => 'Company deleted successfully',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to delete company',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
