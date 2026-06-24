<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\OtpCode;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use App\Notifications\VerifyEmailWithOtp;
class RegisteredUserController extends Controller
{
    /**
     * Handle an incoming registration request.
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'country' => ['nullable', 'string', 'max:100'],
            'age' => ['nullable', 'integer', 'min:5', 'max:100'],
            'phone_num' => ['nullable', 'string', 'max:20'],
            'gender' => ['nullable', 'in:male,female'],
            'role' => ['nullable', 'in:student,instructor'], // Admin registers through a secure route or seeder
        ]);

        $user = User::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'country' => $request->country,
            'age' => $request->age,
            'phone_num' => $request->phone_num,
            'gender' => $request->gender,
            'role' => $request->role ?? 'student', // Default to student
            'status' => 'active', // Default status from migration
        ]);

        // event(new Registered($user));

        // Generate Sanctum Token immediately upon registration
        $token = $user->createToken('auth_token')->plainTextToken;
        $otp = rand(100000, 999999);

        // Create record using the Eloquent Model
        OtpCode::create([
            'type' => 'email_verification',
            'user_id' => $user->id,
            'otp_code' => $otp,
            'expires_at' => now()->addMinutes(10),
        ]);

        // Send the notification
        $user->notify(new VerifyEmailWithOtp($otp));
        return response()->json([
            'message' => 'Registration successful',
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'email' => $user->email,
                'role' => $user->role,
                'status' => $user->status,
            ]
        ], 201);
    }
}