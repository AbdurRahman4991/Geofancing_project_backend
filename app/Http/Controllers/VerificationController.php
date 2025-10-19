<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Carbon\Carbon;

class VerificationController extends Controller
{


    // public function verify(Request $request, $id, $hash)
    // {
    //     $user = User::findOrFail($id);

    //     if (!hash_equals((string) $hash, sha1($user->getEmailForVerification()))) {
    //         return redirect(config('app.frontend_url') . '/email-verification-failed');
    //     }

    //     if ($user->hasVerifiedEmail()) {
    //         return redirect(config('app.frontend_url') . '/email-already-verified');
    //     }

    //     $user->markEmailAsVerified();

    //     // ✅ সফল হলে ফ্রন্টএন্ডে রিডাইরেক্ট করো
    //     return redirect(config('app.frontend_url') . '/email-verified-success');
    // }

    // public function resend(Request $request)
    // {
    //     $user = $request->user();

    //     if ($user->hasVerifiedEmail()) {
    //         return response()->json(['message' => 'Email already verified.']);
    //     }

    //     $user->sendEmailVerificationNotification();

    //     return response()->json(['message' => 'Verification link sent again.']);
    // }

    public function verifyOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'otp' => 'required|digits:6',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        if ($user->otp !== $request->otp) {
            return response()->json(['message' => 'Invalid OTP'], 400);
        }

        if (now()->gt($user->otp_expires_at)) {
            return response()->json(['message' => 'OTP expired'], 400);
        }

        $user->update([
            'email_verified_at' => now(),
            'otp' => null,
            'otp_expires_at' => null,
        ]);

        return response()->json(['message' => 'Email verified successfully']);
    }

    public function resendOtp(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $otp = rand(100000, 999999);
        $user->update([
            'otp' => $otp,
            'otp_expires_at' => now()->addMinutes(10),
        ]);

        Mail::to($user->email)->send(new SendOtpMail($otp));

        return response()->json(['message' => 'New OTP sent']);
    }


}
