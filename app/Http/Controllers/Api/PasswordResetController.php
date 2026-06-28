<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;

class PasswordResetController extends Controller
{
    /**
     * Send a password reset link to the given email.
     * POST /forgot-password
     */
    public function sendResetLink(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        $status = Password::sendResetLink(
            $request->only('email')
        );

        // Return a generic success message regardless of whether the email
        // exists – prevents user enumeration attacks.
        if ($status === Password::RESET_LINK_SENT) {
            return response()->json([
                'message' => 'If an account with that email exists, a reset link has been sent.',
            ]);
        }

        if ($status === Password::RESET_THROTTLED) {
            return response()->json([
                'message' => 'Too many requests. Please wait before trying again.',
            ], 429);
        }

        // Email not found → still return 200 to prevent enumeration
        return response()->json([
            'message' => 'If an account with that email exists, a reset link has been sent.',
        ]);
    }

    /**
     * Reset the user's password.
     * POST /reset-password
     */
    public function resetPassword(Request $request)
    {
        $request->validate([
            'token'    => ['required'],
            'email'    => ['required', 'email'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                $user->forceFill([
                    'password'       => Hash::make($password),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return response()->json([
                'message' => 'Password has been reset successfully. You may now log in.',
            ]);
        }

        return response()->json([
            'message' => 'The provided token is invalid or has expired.',
        ], 422);
    }
}
