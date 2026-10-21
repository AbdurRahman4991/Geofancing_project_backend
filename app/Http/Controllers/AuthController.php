<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Employee;
use App\Models\Geofence;
use App\Models\EmployeeHierarchyAssignment;
use Illuminate\Support\Facades\Auth;
use Illuminate\Auth\Events\Registered;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;
use App\Mail\SendOtpMail;
use Illuminate\Support\Facades\Hash;
use App\Services\User\UserService;

class AuthController extends Controller
{
    public function users(Request $request, UserService $userService)
    {
        $users = $userService->index($request);

        return response()->json([
            'status' => true,
            'message' => 'User list retrieved successfully.',
            'data' => $users,
        ]);
    }

    public function showUser(int $id, UserService $userService)
    {
        return response()->json([
            'status' => true,
            'data' => $userService->show($id),
        ]);
    }

    public function updateUser(Request $request, int $id, UserService $userService)
    {
        return response()->json([
            'status' => true,
            'message' => 'User updated successfully.',
            'data' => $userService->update($request, $id),
        ]);
    }

    public function register(Request $request)
    {
        $data = $request->validate([
            'employee_id' => 'required|exists:employees,employee_id',
            'email'       => 'required|email|unique:users,email',
            'password'    => 'required|string|min:8|confirmed',
        ]);

        $employee = Employee::where(
            'employee_id',
            $data['employee_id']
        )->first();

        if (!$employee) {
            return response()->json([
                'message' => 'Employee not found.',
                'employee_id' => $data['employee_id'],
            ], 404);
        }

        if (User::where('employee_id', $employee->id)->exists()) {
            return response()->json([
                'message' => 'This employee already has a user account.',
            ], 422);
        }

        $user = User::create([
            'employee_id' => $employee->id,
            'name'        => $employee->name,
            'phone'       => $employee->phone,
            'company_id'  => $employee->company_id,
            'email'       => $data['email'],
            'password'    => Hash::make($data['password']),
        ]);

        event(new Registered($user));

        return response()->json([
            'message' => 'Registration successful.',
            'user' => $user,
        ], 201);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email'     => 'required|email',
            'password'  => 'required|string',
            'device_id' => 'required|string',
            'latitude'  => 'required|string',
            'longitude' => 'required|string',
        ]);
        $user = User::select([
            'id',
            'email',
            'employee_id',
            'status',
            'password',
        ])
        ->with([
            'employee:id,name,employee_id,company_id,designation',
            'hierarchyAssignment:id,user_id,hierarchy_node_id'
        ])
        ->where('email', $request->email)
        ->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.']
            ]);
        }

        if ($user->status !== 'active') {
            return response()->json([
                'status' => 403,
                'message' => 'Your account is inactive.',
            ], 403);
        }

        $hierarchyNodeId = $user->hierarchyAssignment?->hierarchy_node_id;
        $userGeoFancing = collect();
        if ($hierarchyNodeId) {
            $userGeoFancing = Geofence::where('hierarchy_node_id', $hierarchyNodeId)
                ->select(
                    'latitude',
                    'longitude',
                    'radius',
                    'firm_name'
                )
                ->get();
        }

        // Login
        Auth::login($user);

        // Delete old tokens
        $user->tokens()->delete();

        // Create new token
        $token = $user->createToken('api-token')
            ->plainTextToken;

        // Update device information
        $user->update([
            'device_id' => $request->device_id,
            'latitude'  => $request->latitude,
            'longitude' => $request->longitude,
        ]);

        return response()->json([
            'status'        => 200,
            'access_token'  => $token,
            'token_type'    => 'Bearer',
            'user' => $user,
            'geofancing'   => $userGeoFancing,
        ]);
    }

    public function adminLogin(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        if (!Auth::attempt($request->only('email', 'password'))) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        // Login User
        $user = Auth::user();
        $companyId = $user->company_id ?? $user->employee?->company_id;
        $roleNames = $user->getRoleNames()->values();

        // আগের token delete করতে চাইলে
        $user->tokens()->delete();

        // নতুন token তৈরি
        $token = $user->createToken('admin-token')->plainTextToken;

        return response()->json([
            'status' => true,
            'message' => 'Login successful.',
            'token' => $token,
            'token_type' => 'Bearer',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'company_id' => $companyId,
                'roles' => $roleNames,
            ]
        ]);
    }
    

    // Step 2: Verify OTP and complete login
    public function verifyOtpLogin(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'otp'   => 'required|digits:6',
        ]);

        $user = User::where('email', $request->email)
            ->where('otp', $request->otp)
            ->first();

        if (!$user) {
            return response()->json(['message' => 'Invalid OTP.'], 400);
        }

        if (Carbon::now()->greaterThan($user->otp_expires_at)) {
            return response()->json(['message' => 'OTP expired.'], 400);
        }

        
        $user->update([
            'otp' => null,
            'otp_expires_at' => null,
        ]);

        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'message' => 'Login successful.',
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $user,
        ]);
    }

    public function logout(Request $request)
    {
        $user = $request->user();

        $user->currentAccessToken()->delete();

        $user->update([
            'device_id' => null,
            'latitude'  => null,
            'longitude' => null,
        ]);

        return response()->json([
            'status'  => 200,
            'message' => 'Logout successful',
        ]);
    }

    public function user(Request $request)
    {       
        return response()->json($request->user());
    }

}
