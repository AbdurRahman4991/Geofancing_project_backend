<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Employee;
use App\Models\Geofence;
use Illuminate\Support\Facades\Auth;
use Illuminate\Auth\Events\Registered;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;
use App\Mail\SendOtpMail;

class AuthController extends Controller
{
    // public function register(Request $request)
    // {
    //     $data = $request->validate([
    //         'name' => 'required|string|max:255',
    //         'email' => 'required|email|unique:users,email',
    //         'password' => 'required|string|min:8|confirmed',
    //     ]);

    //     $user = User::create($data);

    //     // ইমেইল ভেরিফিকেশন মেইল পাঠাবে
    //     event(new Registered($user));

    //     return response()->json([
    //         'message' => 'Registration successful. Please check your email for verification link.'
    //     ], 201);
    // }

    public function register(Request $request)
    {
        $data = $request->validate([
            //'name' => 'required|string|max:255',
            //'email' => 'required|email',            
              'employee_id' => ['required', 'string', 'unique:users,employee_id'],
              'phone' => ['required', 'string', 'min:11'],
            //'password' => 'required|string|min:8|confirmed',
        ]);

        $exists = Employee::where('employee_id', $data['employee_id'])
                    ->where('phone', $data['phone'])
                    ->exists();

        if (!$exists) {
            throw ValidationException::withMessages([
                'employee_id' => ['Invalid employee ID or phone number combination.'],
            ]);
        }

       // $data['password'] = bcrypt($data['password']);

        $user = User::create($data);

        // OTP তৈরি
        // $otp = rand(100000, 999999);

        // $user->update([
        //     'otp' => $otp,
        //     'otp_expires_at' => Carbon::now()->addMinutes(10),
        // ]);

        // ইমেইল পাঠানো
       
       // Mail::to($user->email)->queue(new SendOtpMail($otp));


        return response()->json([
            'status' => 200,
            'message' => 'Registration successful.',
            'email' => $user->email
        ], 200);
    }

    public function login(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|string',
            'phone'       => 'required|string',
            'device_id'   => 'required|string',
            'latitude'    => 'required|string',
            'longitude'   => 'required|string',
        ]);

        // $user = User::with('employee')->where('employee_id', $request->employee_id)
        //             ->where('phone', $request->phone)
        //             ->first();
        $user = User::with('employee')
            ->where('employee_id', $request->employee_id)
            ->where('phone', $request->phone)
            ->first();


        if (!$user) {
            throw ValidationException::withMessages([
                'employee_id' => ['The provided credentials are incorrect.']
            ]);
        }

        $userId = $user->id;
        $userGeoFancing = Geofence::where('user_id', $userId)->select('latitude','longitude','radius','firm_name')->get();


        
        Auth::login($user);
        
        $user->tokens()->delete();

        $token = $user->createToken('api-token')->plainTextToken;
    
        $user->update([
            'device_id' => $request->device_id,
            'latitude'  => $request->latitude,
            'longitude' => $request->longitude,
            // 'otp' => $otp,
            // 'otp_expires_at' => Carbon::now()->addMinutes(5),
        ]);

        return response()->json([
            'status'       => 200,
            'access_token' => $token,
            'token_type'   => 'Bearer',            
            'user'         => $user,
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
        ]
    ]);
}

    // public function adminLogin(Request $request)
    // {
    //     $request->validate([
    //         'email'    => 'required|email',
    //         'password' => 'required|string',
            
    //     ]);

    //     // ইউজার আছে কিনা চেক করো
    //     $user = User::where('email', $request->email)->first();

    //     if (!$user || !Auth::attempt($request->only('email', 'password'))) {
    //         throw ValidationException::withMessages([
    //             'email' => ['The provided credentials are incorrect.'],
    //         ]);
    //     }

    //     if (!$user->hasVerifiedEmail()) {
    //         return response()->json(['message' => 'Email not verified.'], 403);
    //     }

    //     // 🔹 Random 6-digit OTP তৈরি করো
    //     $otp = rand(100000, 999999);

    //     // 🔹 OTP সংরক্ষণ করো (এখানে otp_expires_at সময় দেওয়া হলো 5 মিনিট)
    //     $user->update([
    //         'otp' => $otp,
    //         'otp_expires_at' => Carbon::now()->addMinutes(5),
    //     ]);

    //     Mail::to($user->email)->queue(new SendOtpMail($otp));


    //     return response()->json([
    //         'status' => 200,
    //         'message' => 'OTP sent successfully to your email.',
    //         'email' => $user->email,
    //     ]);
    // }

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

        // ✅ OTP valid হলে, null করে দাও এবং টোকেন তৈরি করো
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
