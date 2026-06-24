<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\OtpCode;
use App\Notifications\ResetPasswordWithOtp;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PasswordResetLinkController extends Controller
{
    /**
     * Handle an incoming password reset OTP request.
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        $user = User::where('email', $request->email)->first();

        // Standard security response even if user does not exist
        if (!$user) {
            return response()->json([
                'message' => 'If the email matches an active account, a verification code has been sent.'
            ], 200);
        }

        // Clean up older reset entries in both tables
        OtpCode::where('user_id', $user->id)
                ->where('type', 'reset_password')
                ->delete();

        DB::table('password_reset_tokens')->where('email', $user->email)->delete();

        $otp = rand(100000, 999999);

        // Save fresh OTP token
        OtpCode::create([
            'type' => 'reset_password',
            'user_id' => $user->id,
            'otp_code' => $otp,
            'expires_at' => now()->addMinutes(15),
        ]);

        // Send customized OTP template
        $user->notify(new ResetPasswordWithOtp($otp));

        return response()->json([
            'message' => 'If the email matches an active account, a verification code has been sent.'
        ], 200);
    }
}