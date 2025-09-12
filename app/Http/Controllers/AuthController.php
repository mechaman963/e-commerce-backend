<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use Laravel\Passport\RefreshTokenRepository;
use Laravel\Passport\TokenRepository;

class AuthController extends Controller
{
    // Register Method

    public function Register(RegisterRequest $request)
{
    $validated = $request->validated();
    
    $user = User::create([
        'name' => $validated['name'],
        'email' => $validated['email'],
        'password' => Hash::make($validated['password']),
    ]);

    // Create token using Passport's createToken
    $token = $user->createToken('authToken')->accessToken;

    return response()->json([
        'user' => $user,
        'token' => $token,
        'token_type' => 'Bearer',
        'expires_in' => 60 * 24 * 7, // 1 week in minutes
    ], 201); // Use 201 for resource created
}

    // Login Method
    public function login(Request $request)
{
    \Log::info('Login attempt', ['email' => $request->email]);
    
    try {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $credentials = $request->only('email', 'password');

        if (!Auth::attempt($credentials)) {
            \Log::warning('Invalid credentials', ['email' => $request->email]);
            return response()->json(['error' => 'Invalid credentials'], 401);
        }

        $user = $request->user();
        \Log::info('User authenticated', ['user_id' => $user->id]);
        
        // Check if user model has the expected methods
        if (!method_exists($user, 'createToken')) {
            \Log::error('User model does not have createToken method');
            throw new \Exception('User model is not properly set up for API authentication');
        }

        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'user' => $user,
            'token' => $token,
            'role' => $user->role,
            'currentUserId' => $user->id,
        ], 200);

    } catch (\Exception $e) {
        \Log::error('Login error: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
        return response()->json(['error' => 'Server error: ' . $e->getMessage()], 500);
    }
}

    // Logout Method
    public function logout(Request $request)
    {
        $request->user()->token()->revoke();
        return response()->json(['message' => 'Successfully logged out'], 200);
    }
}
