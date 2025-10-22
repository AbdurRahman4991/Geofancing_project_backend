<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\URL;
use Illuminate\Auth\Notifications\ResetPassword;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;
use App\Mail\SendOtpMail;

class ForgotPasswordController extends Controller
{
    // public function sendResetLinkEmail(Request $request)
    // {
    //     $request->validate(['email' => 'required|email']);

    //     // Custom reset URL
    //     // ResetPassword::createUrlUsing(function ($user, string $token) {
    //     //     return env('FRONTEND_URL') . '/#/reset-password?token=' . $token . '&email=' . urlencode($user->email);
    //     // });
    //            ResetPassword::createUrlUsing(function ($user, string $token) {
    //                 return env('FRONTEND_URL') . '#/reset-password/' . $token . '/' . urlencode($user->email);
    //             });

    //     $status = Password::sendResetLink($request->only('email'));

    //     return $status === Password::RESET_LINK_SENT
    //         ? response()->json(['message' => 'Reset link sent to your email.'])
    //         : response()->json(['message' => 'Unable to send reset link.'], 400);
    // }

    public function sendForgetOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);

        $user = User::where('email', $request->email)->first();

        // OTP তৈরি
        $otp = rand(100000, 999999);

        $user->update([
            'otp' => $otp,
            'otp_expires_at' => now()->addMinutes(10),
        ]);

        Mail::to($user->email)->queue(new SendOtpMail($otp));

        return response()->json([
            'status' => 200,
            'message' => 'OTP sent to your email address.',
            'email' => $user->email            
        ]);
    }

}
