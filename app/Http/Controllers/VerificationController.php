<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

class VerificationController extends Controller
{
    // public function verify(Request $request, $id, $hash)
    // {
    //     $user = User::findOrFail($id);

    //     if (!hash_equals((string) $hash, sha1($user->getEmailForVerification()))) {
    //         return response()->json(['message' => 'Invalid verification link.'], 403);
    //     }

    //     if ($user->hasVerifiedEmail()) {
    //         return response()->json(['message' => 'Email already verified.']);
    //     }

    //     $user->markEmailAsVerified();

    //     return response()->json(['message' => 'Email verified successfully.']);
    // }

    public function verify(Request $request, $id, $hash)
    {
        $user = User::findOrFail($id);

        if (!hash_equals((string) $hash, sha1($user->getEmailForVerification()))) {
            return redirect(config('app.frontend_url') . '/email-verification-failed');
        }

        if ($user->hasVerifiedEmail()) {
            return redirect(config('app.frontend_url') . '/email-already-verified');
        }

        $user->markEmailAsVerified();

        // ✅ সফল হলে ফ্রন্টএন্ডে রিডাইরেক্ট করো
        return redirect(config('app.frontend_url') . '/email-verified-success');
    }

    public function resend(Request $request)
    {
        $user = $request->user();

        if ($user->hasVerifiedEmail()) {
            return response()->json(['message' => 'Email already verified.']);
        }

        $user->sendEmailVerificationNotification();

        return response()->json(['message' => 'Verification link sent again.']);
    }
}
