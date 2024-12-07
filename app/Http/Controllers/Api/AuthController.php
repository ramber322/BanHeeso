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
    public function index()
{
    // Fetch all events
    $events = Event::all();
    return response()->json(['events' => $events]);
}

    public function storeEvent(Request $request)
    {
        // Validate the incoming request
        $request->validate([
            'title' => 'required|string|max:255',
            'event_date' => 'required|date',
            'event_time' => 'required|date_format:H:i',
            'location' => 'nullable|string|max:255',
            'description' => 'nullable|string',
        ]);
    
        // Create the event without mass assignment
        $event = new Event();
        $event->title = $request->title;
        $event->event_date = $request->event_date;
        $event->event_time = $request->event_time;
        $event->location = $request->location;
        $event->description = $request->description;
        $event->save(); // Save to the database
    
        // Return a response with the created event data
        return response()->json([
            'success' => true,
            'event' => $event,
        ], 201);
    }
    
    public function getUpcomingEvents(Request $request)
    {
        // Get the current date and add 30 days
        $now = Carbon::now();
        $endDate = $now->copy()->addDays(30);

        // Fetch events that are between now and the next 30 days
        $events = Event::where('event_date', '>=', $now)
            ->where('event_date', '<=', $endDate)
            ->orderBy('event_date')
            ->get();

        // Check if events are found
        if ($events->isEmpty()) {
            return response()->json([
                'success' => true,
                'events' => [],
            ]);
        }

        return response()->json([
            'success' => true,
            'events' => $events,
        ]);
    }
    public function fetchEvents(Request $request)
    {
        // Optionally, you can filter events by a specific date
        $date = $request->query('date'); // e.g., '2024-09-15'

        if ($date) {
            // Validate the date format
            $request->validate([
                'date' => 'date_format:Y-m-d'
            ]);

            // Fetch events for the specific date
            $events = Event::whereDate('event_date', $date)->get();
        } else {
            // Fetch all events if no specific date is provided
            $events = Event::all();
        }

        // Return a response with the events data
        return response()->json([
            'success' => true,
            'events' => $events,
        ], 200);
    }


    public function registerEvent(Request $request)
    {
        // Validate the request data
        $request->validate([
            'event_id' => 'required|exists:events,id',
        ]);
    
        // Check if user is authenticated
        $user = Auth::user();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'User not authenticated.'], 401);
        }
    
        // Check if the user is already registered for the event
        if ($user->events()->where('event_id', $request->event_id)->exists()) {
            return response()->json(['success' => false, 'message' => 'You are already registered for this event.']);
        }
    
        // Register the user for the event
        $user->events()->attach($request->event_id);
    
        return response()->json(['success' => true, 'message' => 'Registered for event successfully.']);
    }
    



public function getRegisteredEvents(Request $request) {
    // Get the authenticated user
    $user = $request->user();

    // Check if user is authenticated
    if (!$user) {
        return response()->json(['message' => 'User not authenticated.'], 401);
    }

    // Assuming you have a relationship set up for registered events
    $registeredEvents = $user->registeredEvents; // Make sure this is the correct relationship

    return response()->json(['success' => true, 'events' => $registeredEvents]);
}


public function submitFeedback(Request $request, Event $event)
{
    if (!auth()->check()) {
        return response()->json(['message' => 'User not authenticated'], 401);
    }

    

    // Proceed with validation and feedback submission
    $validated = $request->validate([
        'rating' => 'required|integer|min:1|max:5',
        'comment' => 'nullable|string|max:255',
    ]);

    $feedback = new Feedback([
        'user_id' => auth()->user()->id,
        'event_id' => $event->id,
        'rating' => $validated['rating'],
        'comment' => $validated['comment'] ?? null,
    ]);

    $feedback->save();

    return response()->json(['success' => true, 'message' => 'Feedback submitted successfully.']);
}
public function getFeedback(Request $request, Event $event)
{
    // NOT BEING USED !!!!! YOU CAN DELETE
    // Fetch feedback along with associated user data
    $feedback = Feedback::where('event_id', $event->id)
                        ->with('user') // Ensure the user relationship is loaded
                        ->get();

    // Check if feedback is not empty
    if ($feedback->isEmpty()) {
        return response()->json(['message' => 'No feedback found for this event.'], 404);
    }

    // Map the feedback data to include student name
    $feedbackWithStudentName = $feedback->map(function ($item) {
        $item->student_name = $item->user->name; // Assuming 'name' field in User model
        return $item;
    });

    return response()->json(['feedback' => $feedbackWithStudentName], 200);
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

