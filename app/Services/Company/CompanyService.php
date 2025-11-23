<?php

namespace App\Services\Company;

use App\Models\Company;
use Illuminate\Http\Request;

class CompanyService
{

    public function all(Request $request)
    {

        $query = Company::query();

        // Add search filter
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('company_name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")
                ->orWhere('phone', 'like', "%{$search}%")
                ->orWhere('address', 'like', "%{$search}%");
            });
        }

        // Add order by
        $orderBy = $request->get('orderBy', 'id'); // default column
        $orderDir = $request->get('order', 'desc'); // default desc
        $query->orderBy($orderBy, $orderDir);

        $perPage = $request->get('limit', 10);
        $companies = $query->paginate($perPage);

        // Add avatar URLs
        $companies->getCollection()->transform(function ($company) {
            $company->avatar = $company->getFirstMediaUrl('avatar');
            return $company;
        });

        return $companies;

    }

    public function store(Request $request)
    {
        $company = Company::create($request->only(['company_name', 'email', 'phone', 'address']));

        if ($request->hasFile('avatar')) {
            $company->addMediaFromRequest('avatar')->toMediaCollection('avatar');
        }

        // ✅ Return with avatar URL
        $company->avatar_url = $company->getFirstMediaUrl('avatar');

        return $company;
    }


    // public function show($id)
    // {
    //     return Company::findOrFail($id);
    // }
    public function show($id)
{
    $company = Company::findOrFail($id);

    // Add avatar URL (just like in all() method)
    $company->avatar = $company->getFirstMediaUrl('avatar');

    return $company;
}


    public function update(Request $request, $id)
    {
       
        $company = Company::findOrFail($id);

        $company->update($request->only(['company_name', 'email', 'phone', 'address']));

    //     // ✅ যদি নতুন avatar আপলোড হয়, পুরনোটা ডিলিট করে নতুন যোগ করো
        if ($request->hasFile('avatar')) {
            $company->clearMediaCollection('avatar');
            $company->addMediaFromRequest('avatar')->toMediaCollection('avatar');
        }

         return $company;
     }

    public function destroy($id)
    {
        $company = Company::findOrFail($id);
        $company->clearMediaCollection('avatar'); // ✅ Optional: ছবিও মুছে ফেলবে
        $company->delete();

        return true;
    }
}
