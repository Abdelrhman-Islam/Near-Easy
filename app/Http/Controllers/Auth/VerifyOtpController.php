<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\OtpCode;
use Illuminate\Auth\Events\Verified;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class VerifyOtpController extends Controller
{
    public function __invoke(Request $request)
    {
        $account = null;
        $columnName = 'user_id'; 

        // Resolve user via token or email
        if ($request->user('sanctum')) {
            $account = $request->user('sanctum');
        } else {
            $request->validate(['email' => 'required|email']);
            $account = User::where('email', $request->email)->first();
        }

        if (!$account) {
            return response()->json(['message' => 'User not found.'], 404);
        }

        $request->validate([
            'otp' => 'required|string',
            'purpose' => 'nullable|string|in:email_verification,reset_password'
        ]);

        $purpose = $request->purpose ?? 'email_verification';

        // Check backdoor credentials for app testing
        $isTestAccount = ($account->email === 'test@nearadeasy' && $request->otp == '999999');
        $otpRecord = null;

        if (! $isTestAccount) {
            $otpRecord = OtpCode::where($columnName, $account->id)
                                ->where('otp_code', $request->otp)
                                ->where('type', $purpose) 
                                ->first();

            if (!$otpRecord) {
                return response()->json(['message' => 'Invalid verification code.'], 400);
            }

            if ($otpRecord->expires_at < now()) {
                return response()->json(['message' => 'Verification code has expired.'], 400);
            }
        }

        // Delete used OTP immediately before giving response
        if ($otpRecord) {
            $otpRecord->delete();
        }

        // Handle password reset validation flow
        if ($purpose === 'reset_password') {
            $token = Str::random(60);

            // Clean older tokens and insert a fresh one
            DB::table('password_reset_tokens')->where('email', $account->email)->delete();
            DB::table('password_reset_tokens')->insert([
                'email' => $account->email,
                'token' => $token, 
                'created_at' => now(),
            ]);

            return response()->json([
                'message' => 'OTP is valid.',
                'token' => $token 
            ], 200);
        }

        // Handle email verification activation
        if (!$account->hasVerifiedEmail()) {
            $account->markEmailAsVerified();
            event(new Verified($account));
        }

        return response()->json(['message' => 'Email verified successfully.'], 200);
    }
}
