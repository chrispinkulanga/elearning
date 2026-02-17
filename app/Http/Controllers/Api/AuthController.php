<?php
// app/Http/Controllers/Api/AuthController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Mail\OTPMail;
use App\Mail\EmailVerificationMail;
use App\Rules\InternationalPhoneNumber;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        \Log::info('=== REGISTRATION CONTROLLER START ===');
        \Log::info('Request data:', $request->all());
        
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|in:student,instructor',
            'phone' => 'nullable|string|max:25',
            'bio' => 'nullable|string|max:1000',
        ]);
        
        \Log::info('Validation rules applied');

        if ($validator->fails()) {
            \Log::info('Validation failed:', $validator->errors()->toArray());
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }
        
        \Log::info('Validation passed, creating user');
        
        try {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'phone' => $request->phone,
                'bio' => $request->bio,
                'email_verified_at' => null, // Require email verification
            ]);
            
            \Log::info('User created successfully:', ['user_id' => $user->id, 'email' => $user->email]);

            // Assign role
            $user->assignRole($request->role);
            \Log::info('Role assigned:', ['role' => $request->role]);

            // Generate verification token and send email
            $verificationToken = $user->generateEmailVerificationToken();
            $verificationUrl = url("/verify-email?token={$verificationToken}&email=" . urlencode($user->email));
            
            try {
                Mail::to($user->email)->send(new EmailVerificationMail($user, $verificationUrl));
                \Log::info('Verification email sent successfully');
            } catch (\Exception $e) {
                \Log::error('Failed to send verification email:', ['error' => $e->getMessage()]);
                // Continue with registration even if email fails
            }

            // Don't create token yet - user needs to verify email first
            // $token = $user->createToken('auth_token')->plainTextToken;

            $response = response()->json([
                'status' => 'success',
                'message' => 'Registration successful! Please check your email to verify your account.',
                'data' => [
                    'user' => $user->load('roles'),
                    'email_verified' => false,
                    'verification_sent' => true
                ]
            ], 201);
            
            \Log::info('Response prepared, sending to client');
            return $response;
            
        } catch (\Exception $e) {
            \Log::error('Error creating user:', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create user: ' . $e->getMessage()
            ], 500);
        }
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid login credentials'
            ], 401);
        }

        $user = User::where('email', $request->email)->firstOrFail();

        if ($user->status === 'banned') {
            return response()->json([
                'status' => 'error',
                'message' => 'Your account has been banned'
            ], 403);
        }

        if (!$user->isEmailVerified()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Please verify your email address before logging in. Check your email for the verification link.',
                'email_verified' => false
            ], 403);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'status' => 'success',
            'message' => 'Login successful',
            'data' => [
                'user' => $user->load('roles'),
                'token' => $token,
                'token_type' => 'Bearer'
            ]
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Logged out successfully'
        ]);
    }

    public function user(Request $request)
    {
        return response()->json([
            'status' => 'success',
            'data' => $request->user()->load('roles')
        ]);
    }


    public function forgotPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $status = Password::sendResetLink($request->only('email'));

        if ($status === Password::RESET_LINK_SENT) {
            return response()->json([
                'status' => 'success',
                'message' => 'Password reset link sent to your email'
            ]);
        }

        return response()->json([
            'status' => 'error',
            'message' => 'Unable to send reset link'
        ], 400);
    }

    public function resetPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password)
                ])->save();
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return response()->json([
                'status' => 'success',
                'message' => 'Password reset successfully'
            ]);
        }

        return response()->json([
            'status' => 'error',
            'message' => 'Invalid reset token'
        ], 400);
    }

    // OTP-based Password Reset Methods
    public function sendOTP(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'User not found'
            ], 404);
        }

        // Generate OTP
        $otp = $user->generateOTP();

        try {
            // Send OTP email
            Mail::to($user->email)->send(new OTPMail($user, $otp));

            return response()->json([
                'status' => 'success',
                'message' => 'OTP sent successfully to your email address',
                'data' => [
                    'email' => $user->email,
                    'expires_in' => 10 // minutes
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to send OTP. Please try again later.'
            ], 500);
        }
    }

    public function verifyOTP(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
            'otp' => 'required|string|size:6',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'User not found'
            ], 404);
        }

        if ($user->verifyOTP($request->otp)) {
            return response()->json([
                'status' => 'success',
                'message' => 'OTP verified successfully',
                'data' => [
                    'email' => $user->email,
                    'verified' => true
                ]
            ]);
        }

        return response()->json([
            'status' => 'error',
            'message' => 'Invalid or expired OTP'
        ], 400);
    }

    public function resetPasswordWithOTP(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
            'otp' => 'nullable|string|size:6',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'User not found'
            ], 404);
        }

        // Verify OTP first (only if OTP is provided)
        if (!empty($request->otp) && !$user->verifyOTP($request->otp)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid or expired OTP'
            ], 400);
        }

        // Update password
        $user->update([
            'password' => Hash::make($request->password)
        ]);

        // Clear OTP data
        $user->clearOTP();

        return response()->json([
            'status' => 'success',
            'message' => 'Password reset successfully'
        ]);
    }

    public function verifyEmail(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'token' => 'required|string',
            'email' => 'required|email|exists:users,email',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid verification data',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::where('email', $request->email)
                   ->where('email_verification_token', $request->token)
                   ->first();

        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid or expired verification link'
            ], 400);
        }

        if ($user->isEmailVerified()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Email already verified'
            ]);
        }

        $user->verifyEmail();

        return response()->json([
            'status' => 'success',
            'message' => 'Email verified successfully! You can now login to your account.'
        ]);
    }
}