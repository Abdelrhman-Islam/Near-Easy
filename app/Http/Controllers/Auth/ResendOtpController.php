<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\OtpCode;
use App\Notifications\VerifyEmailWithOtp;
use App\Notifications\ResetPasswordWithOtp;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ResendOtpController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
            'purpose' => ['nullable', 'string', 'in:email_verification,reset_password'],
        ]);

        $purpose = $request->purpose ?? 'email_verification';

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json(['message' => 'User not found.'], 404);
        }

        // Check if email is already verified when requesting verification code
        if ($purpose === 'email_verification' && $user->hasVerifiedEmail()) {
            return response()->json(['message' => 'Email is already verified.'], 422);
        }

        // Delete any existing unused OTP for this user and type
        OtpCode::where('user_id', $user->id)
                ->where('type', $purpose)
                ->delete();

        $otp = rand(100000, 999999);

        // Save new OTP record
        OtpCode::create([
            'type' => $purpose,
            'user_id' => $user->id,
            'otp_code' => $otp,
            'expires_at' => now()->addMinutes(15),
        ]);

        // Trigger the correct notification layout
        if ($purpose === 'reset_password') {
            $user->notify(new ResetPasswordWithOtp($otp));
        } else {
            $user->notify(new VerifyEmailWithOtp($otp));
        }

        return response()->json([
            'message' => 'A new verification code has been sent to your email.'
        ], 200);
    }
}