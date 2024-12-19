<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Event; 
use App\Models\Feedback;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        // Validate the incoming request
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);
    
        // Create the user
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password), // Hash  password
        ]);
    
        // Return a response with the created user data
        return response()->json([
            'success' => true,
            'user' => $user,
        ], 201);
    }
    
    public function login(Request $request)
{
    // Validate the incoming request
    $request->validate([
        'email' => 'required|string|email',
        'password' => 'required|string',
    ]);

    // Find the user by email
    $user = User::where('email', $request->email)->first();

    // Check if user exists and password is correct
    if (!$user || !Hash::check($request->password, $user->password)) {
        return response()->json([
            'success' => false,
            'message' => 'The provided credentials are incorrect.',
        ], 401);
    }

    // Create a new token for the user
    $token = $user->createToken('YourAppName')->plainTextToken;

    return response()->json([
        'success' => true,
        'token' => $token,
        'role' => $user->role,  
        'message' => 'Login successful.',
    ]);
}


    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();
        return response()->json(['message' => 'Logged out successfully.']);
    }


}

