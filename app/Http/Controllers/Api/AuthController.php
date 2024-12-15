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
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);
    
        $user = User::where('email', $request->email)->first();
    
        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'The provided credentials are incorrect.',
            ], 401);
        }
    
        $token = $user->createToken('YourAppName')->plainTextToken;
    
        return response()->json([
            'success' => true,
            'token' => $token,
            'message' => 'Login successful.',
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();
        return response()->json(['message' => 'Logged out successfully.']);
    }





























   

    
    
    
    

   











public function getEventFeedback($eventId)
{
    // Check if the user is authenticated
    if (!Auth::check()) {
        return response()->json(['error' => 'Unauthorized'], 401);
    }

    // Find the event by ID
    $event = Event::find($eventId);

    // If the event is not found, return a 404 error
    if (!$event) {
        return response()->json(['error' => 'Event not found'], 404);
    }

    // Fetch feedback along with the associated User (student) data
    $feedback = $event->feedback()->with('user')->get(); // Assuming 'feedback' is a relationship on Event model

    // Map the feedback data to include the student_name
    $feedbackWithStudentName = $feedback->map(function ($item) {
        $item->student_name = $item->user->name; // Assuming 'name' is the field that stores the student's name
        return $item;
    });

    // Return the feedback data with student names
    return response()->json(['feedback' => $feedbackWithStudentName]);
}


}

//index, getUpcomingEvents, getRegisteredEvents, submitFeedback, getFeedback