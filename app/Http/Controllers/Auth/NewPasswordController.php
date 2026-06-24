<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;

class NewPasswordController extends Controller
{
    /**
     * Handle an incoming new password request.
     */
    public function store(Request $request): JsonResponse
    {
        // Expect token inside the request body
        $request->validate([
            'token' => ['required', 'string'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        // Locate token record to fetch the associated email
        $tokenData = DB::table('password_reset_tokens')
            ->where('token', $request->token)
            ->first();

        if (!$tokenData) {
            return response()->json(['message' => 'Invalid or expired reset token.'], 422);
        }

        // Check if token has expired (60 minutes timeout)
        if (now()->subMinutes(60)->gt($tokenData->created_at)) {
            DB::table('password_reset_tokens')->where('token', $request->token)->delete();
            return response()->json(['message' => 'Reset token has expired.'], 422);
        }

        // Find user by email stored alongside the token
        $user = User::where('email', $tokenData->email)->first();

        if (!$user) {
            return response()->json(['message' => 'User account not found.'], 404);
        }

        // Update target user password
        $user->forceFill([
            'password' => Hash::make($request->string('password')),
        ])->save();

        event(new PasswordReset($user));

        // Flush used token instantly
        DB::table('password_reset_tokens')->where('email', $user->email)->delete();

        return response()->json(['status' => 'Your password has been reset successfully.']);
    }
}
