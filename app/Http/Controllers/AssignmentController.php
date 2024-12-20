<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Customer;
use App\Models\Assignment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use App\Mail\NewAssignmentNotification;

use Illuminate\Support\Facades\Validator;

class AssignmentController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth:sanctum')->except(['register', 'login']);
    }

public function index()
{
    try {
        // Retrieve assignments for the logged-in user where status is null
        $assignments = Assignment::with(['customer', 'user']) // Eager load customer and user relationships
            ->where('user_id', Auth::id()) // Filter by the logged-in user's user_id
            ->whereNull('status') // Only include assignments where status is null
            ->orderBy('assignment_id', 'desc') // Order by assignment_id descending
            ->get();

        // Map the assignments to include customer name, user email, and days passed since created_at
        $assignments = $assignments->map(function ($assignment) {
            $daysPassed = Carbon::parse($assignment->created_at)->diffInDays(Carbon::now());

            // Automatically send email notifications to the user
            if ($assignment->user && $assignment->user->email) {
                $this->sendEmailNotification(
                    $assignment->user->email,
                    'New Assignment Notification',
                    "You have a new assignment: {$assignment->case_reported} located at {$assignment->location}"
                );
            }

            return [
                'assignment_id' => $assignment->assignment_id,
                'user_id' => $assignment->user_id,
                'status' => $assignment->status,
                'plate_number' => $assignment->plate_number,
                'customer_phone' => $assignment->customer_phone,
                'location' => $assignment->location,
                'case_reported' => $assignment->case_reported,
                'customer_debt' => $assignment->customer_debt,
                'assigned_by' => $assignment->assigned_by,
                'customername' => $assignment->customer->customername ?? 'N/A',
                'user_email' => $assignment->user->email ?? 'N/A', // Include user email
                'created_at' => $assignment->created_at->format('m-d-Y'),
                'days_passed' => $daysPassed,
            ];
        });

        return response()->json([
            'status' => 'success',
            'assignments' => $assignments,
        ], 200);
    } catch (\Exception $e) {
        Log::error('Error fetching assignments: ' . $e->getMessage());

        return response()->json([
            'status' => 'error',
            'message' => 'Failed to fetch assignments',
        ], 500);
    }
}

/**
 * Send an email notification.
 *
 * @param string $recipient
 * @param string $subject
 * @param string $body
 * @return void
 */
protected function sendEmailNotification($recipient, $subject, $body)
{
    try {
        // Set up the email
        Mail::raw($body, function ($message) use ($recipient, $subject) {
            $message->to($recipient)
                ->subject($subject)
                ->from(config('mail.from.address'), config('mail.from.name')); // Use FROM settings from .env
        });
    } catch (\Exception $e) {
        Log::error('Failed to send email: ' . $e->getMessage());
    }
}



public function fetchcustomer()
{
    try {
        // Retrieve assignments for the logged-in user where status is null
        $assignments = Assignment::with('customer') // Eager load the customer relationship
            ->where('user_id', Auth::id()) // Filter by the logged-in user's user_id
            ->whereNotNull('status') // Only include assignments where status is null
            ->orderBy('assignment_id', 'desc') // Order by assignment_id descending
            ->get();

        // Map the assignments to include customer_id and customername
        $assignments = $assignments->map(function ($assignment) {
            return [
                'customer_id' => $assignment->customer->customer_id ?? 'N/A', // Get customer_id
                'customername' => $assignment->customer->customername ?? 'N/A', // Get customer name
                // Add other assignment fields as necessary
            ];
        });

        // Return the assignments as JSON
        return response()->json([
            'status' => 'success',
            'assignments' => $assignments,
        ], 200);
    } catch (\Exception $e) {
        // Log the error message
        Log::error('Error fetching assignments: ' . $e->getMessage());

        // Return an error response
        return response()->json([
            'status' => 'error',
            'message' => 'Failed to fetch assignments',
        ], 500);
    }
}



    public function AssignmentNotification(Request $request)
{
    try {
        // Retrieve assignments for the logged-in user, where status is not null
        $assignments = Assignment::with('customer') // Eager load the customer relationship
            ->where('assigned_to', $request->user()->email) // Filter by the logged-in user's email
            ->whereNotNull('status') // Only include assignments where status is not null
            ->orderBy('assignment_id', 'desc') // Order by assignment_id descending
            ->get();

        // Map the assignments to include customer_id and customername
        $assignments = $assignments->map(function ($assignment) {
            return [
                'customer_id' => $assignment->customer->customer_id ?? 'N/A', // Get customer_id
                'customername' => $assignment->customer->customername ?? 'N/A', // Get customer name
                'plate_number' => $assignment->plate_number, // Include plate_number
                'location' => $assignment->location, // Include location
                'case_reported' => $assignment->case_reported, // Include case_reported
                'assigned_by' => $assignment->assigned_by, // Include assigned_by
                'status' => $assignment->status, // Include status
            ];
        });

        // Send email notifications for each assignment
        foreach ($assignments as $assignment) {
            Mail::to($request->user()->email)->send(new NewAssignmentNotification($assignment));
        }

        return response()->json(['message' => 'Notifications sent successfully.']);
    } catch (\Exception $e) {
        return response()->json(['message' => 'Failed to send notifications.'], 500);
    }
}


public function index1()
{
    try {
        // Retrieve assignments for the logged-in user where status is not null
        $assignments = Assignment::with('customer') // Eager load the customer relationship
            ->where('user_id', Auth::id()) // Filter by the logged-in user's user_id
            ->whereNotNull('status') // Only include assignments where status is not null
            ->orderBy('assignment_id', 'desc') // Order by assignment_id descending
            ->get();

        // Map the assignments to include the customer name and days passed since created_at
        $assignments = $assignments->map(function ($assignment) {
            // Calculate the days passed since the assignment was created
            $createdAt = $assignment->created_at ? Carbon::parse($assignment->created_at) : null;
            $daysPassed = $createdAt ? $createdAt->diffInDays(Carbon::now()) : null;

            return [
                'assignment_id' => $assignment->assignment_id,
                'user_id' => $assignment->user_id,
                'status' => $assignment->status,
                'plate_number' => $assignment->plate_number,
                'customer_phone' => $assignment->customer_phone,
                'location' => $assignment->location,
                'case_reported' => $assignment->case_reported,
                'customer_debt' => $assignment->customer_debt,
                'assigned_by' => $assignment->assigned_by,
                'return_comment' => $assignment->return_comment ?? 'N/A',
                'customername' => $assignment->customer->customername ?? 'N/A', // Get customer name, or 'N/A' if not available
                'created_at' => $createdAt ? $createdAt->format('m-d-Y') : null, // Format only if not null
                'accepted_at' => $assignment->accepted_at
                    ? Carbon::parse($assignment->accepted_at)->format('m-d-Y H:i:s') : null,
                'days_passed' => $daysPassed, // Add the days passed field
            ];
        });

        // Return the assignments as JSON
        return response()->json([
            'status' => 'success',
            'assignments' => $assignments,
        ], 200);
    } catch (\Exception $e) {
        // Log the error message
        Log::error('Error fetching assignments: ' . $e->getMessage());

        // Return an error response
        return response()->json([
            'status' => 'error',
            'message' => 'Failed to fetch assignments',
        ], 500);
    }
}




    public function store(Request $request)
{
    $validator = Validator::make($request->all(), [
        'customer_id' => 'required|exists:customers,customer_id',
        'plate_number' => 'required|string|max:255',
        'customer_phone' => 'required|string|max:20',
        'location' => 'required|string|max:255',
        'report_id' => 'required|exists:reports,report_id',
        'status' => 'nullable|string|in:taken,canceled', // Optional status field
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status' => 'error',
            'message' => 'Validation error',
            'errors' => $validator->errors(),
        ], 422);
    }

    try {
        $assignment = new Assignment($request->all());
        $assignment->user_id = Auth::id(); // Set user_id to the authenticated user's ID
        $assignment->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Assignment created successfully',
            'assignment' => $assignment,
        ], 201);
    } catch (\Exception $e) {
        Log::error('Error creating assignment: ' . $e->getMessage());

        return response()->json([
            'status' => 'error',
            'message' => 'Failed to create assignment',
        ], 500);
    }
}



public function show($id)
{
    try {
        // Fetch the assignment that matches the given ID and belongs to the logged-in user, eager load the customer relationship
        $assignment = Assignment::with('customer') // Eager load customer data
            ->where('assignment_id', $id)
            ->where('user_id', Auth::id()) // Ensure the assignment belongs to the logged-in user
            ->first();

        // If assignment is not found, return a 404 response
        if (!$assignment) {
            return response()->json([
                'status' => 'error',
                'message' => 'Assignment not found or does not belong to the logged-in user',
            ], 404);
        }

        // Map the assignment to include the customer name
        $assignmentDetails = [
               'assignment_id' => $assignment->assignment_id,
                'user_id' => $assignment->user_id,
                'status' => $assignment->status,
                 'plate_number' => $assignment->plate_number,
                  'customer_phone' => $assignment->customer_phone,
                   'location' => $assignment->location,
                   'assigned_by' => $assignment->assigned_by,
                'customername' => $assignment->customer->customername ?? 'N/A',
        ];

        // Return the assignment details with a 200 response
        return response()->json([
            'status' => 'success',
            'assignment' => $assignmentDetails,
        ], 200);
    } catch (\Exception $e) {
        // Log the error and return a 500 response
        Log::error('Error fetching assignment: ' . $e->getMessage());

        return response()->json([
            'status' => 'error',
            'message' => 'Failed to fetch assignment',
        ], 500);
    }
}


 public function update(Request $request, $id)
{
    $validator = Validator::make($request->all(), [
        'status' => 'nullable|string',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status' => 'error',
            'message' => 'Validation error',
            'errors' => $validator->errors(),
        ], 422);
    }

    try {
        // Fetch the assignment ensuring it belongs to the logged-in user
        $assignment = Assignment::where('assignment_id', $id)
            ->where('user_id', Auth::id())
            ->first();

        if (!$assignment) {
            return response()->json([
                'status' => 'error',
                'message' => 'Assignment not found',
            ], 404);
        }

        // Update the assignment's status
        $assignment->status = $request->input('status');

        // Check if the status is "accepted" and set the accepted_at timestamp
        if ($request->input('status') == 'accepted') {
            // Set the accepted_at timestamp to the current time in East Africa time zone
            $assignment->accepted_at = Carbon::now('Africa/Nairobi'); // East Africa Time
        }

        $assignment->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Assignment updated successfully',
            'assignment' => $assignment,
        ], 200);
    } catch (\Exception $e) {
        Log::error('Error updating assignment: ' . $e->getMessage());

        return response()->json([
            'status' => 'error',
            'message' => 'Failed to update assignment',
        ], 500);
    }
}

public function UpdateComment(Request $request, $assignment_id)
{
    // Validate the input to ensure 'return_comment' is a string and nullable
    $validator = Validator::make($request->all(), [
        'return_comment' => 'nullable|string',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status' => 'error',
            'message' => 'Validation error',
            'errors' => $validator->errors(),
        ], 422);
    }

    try {
        // Fetch the assignment ensuring it belongs to the logged-in user (by user_id)
        $assignment = Assignment::where('assignment_id', $assignment_id)
            ->where('user_id', Auth::id()) // Ensure the logged-in user owns the assignment
            ->first();

        if (!$assignment) {
            return response()->json([
                'status' => 'error',
                'message' => 'Assignment not found or you do not have permission to update it',
            ], 404);
        }

        // Only update the return_comment
        $assignment->return_comment = $request->input('return_comment', $assignment->return_comment);

        // Save the updated assignment
        $assignment->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Comment updated successfully',
            'assignment' => $assignment,
        ], 200);

    } catch (\Exception $e) {
        // Log the error and return a generic failure message
        Log::error('Error updating assignment: ' . $e->getMessage());

        return response()->json([
            'status' => 'error',
            'message' => 'Failed to update assignment',
        ], 500);
    }
}




   public function destroy($id)
{
    try {
        $assignment = Assignment::where('assignment_id', $id)
            ->where('user_id', Auth::id()) // Ensure assignment belongs to the logged-in user
            ->first();

        if (!$assignment) {
            return response()->json([
                'status' => 'error',
                'message' => 'Assignment not found',
            ], 404);
        }

        $assignment->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Assignment deleted successfully',
        ], 200);
    } catch (\Exception $e) {
        Log::error('Error deleting assignment: ' . $e->getMessage());

        return response()->json([
            'status' => 'error',
            'message' => 'Failed to delete assignment',
        ], 500);
    }
}

public function countAssignments()
    {
        try {
            // Count assignments for the logged-in user
            $count = Assignment::where('user_id', Auth::id())->count();

            return response()->json([
                'status' => 'success',
                'count' => $count,
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error counting assignments: ' . $e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to count assignments',
            ], 500);
        }
    }





}



