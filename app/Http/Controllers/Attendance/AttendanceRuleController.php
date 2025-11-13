<?php

namespace App\Http\Controllers\Attendance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Attendance\AttendanceRuleService;

class AttendanceRuleController extends Controller
{
      protected $service;

    public function __construct(AttendanceRuleService $service)
    {
        $this->service = $service;
    }

    public function index()
    {
        return response()->json($this->service->index());
    }

    public function store(Request $request)
    {
        $request->validate([
            'company_id' => 'required|exists:companies,id',
            'office_in_time' => 'required',
            'office_out_time' => 'required',
        ]);

        return response()->json($this->service->store($request), 201);
    }

    public function show($id)
    {
        return response()->json($this->service->show($id));
    }

    public function update(Request $request, $id)
    {
        return response()->json($this->service->update($request, $id));
    }

    public function destroy($id)
    {
        $this->service->destroy($id);
        return response()->json(['message' => 'Deleted successfully']);
    }
}
