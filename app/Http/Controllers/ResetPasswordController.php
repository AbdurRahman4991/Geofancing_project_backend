<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;
use App\Mail\SendOtpMail;

class ResetPasswordController extends Controller
{
//     public function reset(Request $request)
//     {
//         $request->validate([
//             'token' => 'required',
//             'email' => 'required|email',
//             'password' => 'required|min:8|confirmed',
//         ]);

//         // $status = Password::reset(
//         //     $request->only('email', 'password', 'password_confirmation', 'token'),
//         //     function ($user, $password) {
//         //         $user->forceFill(['password' => bcrypt($password)])->save();
//         //     }
//         // );
//         // Reset closure
// $status = Password::reset(
//     $request->only('email', 'password', 'password_confirmation', 'token'),
//     function ($user, $password) {
//         $user->forceFill(['password' => $password])->save(); // plain — cast will hash
//     }
// );


//         return $status === Password::PASSWORD_RESET
//             ? response()->json(['message' => 'Password reset successfully.'])
//             : response()->json(['message' => 'Invalid token or email.'], 400);
//     }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'otp' => 'required|digits:6',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = User::where('email', $request->email)->first();

        if ($user->otp !== $request->otp) {
            return response()->json(['message' => 'Invalid OTP'], 400);
        }

        if (now()->gt($user->otp_expires_at)) {
            return response()->json(['message' => 'OTP expired'], 400);
        }

        $user->update([
            'password' => bcrypt($request->password),
            'otp' => null,
            'otp_expires_at' => null,
        ]);

        return response()->json(['message' => 'Password reset successfully']);
    }

}
