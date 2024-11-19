<?php

namespace App\Http\Controllers;

use App\Models\JobCard;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Cloudinary\Cloudinary;

class JobCardController extends Controller
{
    public function __construct()
    {
        // Make sure the user is authenticated for all actions except register/login
        $this->middleware('auth:sanctum')->except(['register', 'login']);
    }

    public function index(Request $request)
    {
        // Ensure user is authenticated
        $userId = Auth::id();

        if (!$userId) {
            return response()->json([
                'status' => 'error',
                'message' => 'User not authenticated',
            ], 401); // Unauthorized
        }

        try {
            // Fetch job cards based on a jobcard_id or any other condition
            $jobCards = JobCard::where('user_id', $userId) // Assuming a jobcard has a user_id field
                ->orderBy('jobcard_id', 'desc') // Ordering by jobcard_id, change this field if necessary
                ->get();

            // Check if any job cards are found
            if ($jobCards->isEmpty()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'No job cards found',
                ], 404); // Not Found
            }

            return response()->json([
                'status' => 'success',
                'job_cards' => $jobCards,
            ], 200); // Success

        } catch (\Exception $e) {
            // Log error for debugging purposes
            Log::error('Error fetching job cards: ' . $e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch job cards',
                'error' => $e->getMessage(), // Include the error message for debugging
            ], 500); // Internal Server Error
        }
    }

    // You can also implement a show method if you want to fetch a single job card by jobcard_id
    public function show($jobcard_id)
    {
        // Ensure user is authenticated
        $userId = Auth::id();

        if (!$userId) {
            return response()->json([
                'status' => 'error',
                'message' => 'User not authenticated',
            ], 401); // Unauthorized
        }

        try {
            // Fetch the specific job card by jobcard_id
            $jobCard = JobCard::where('user_id', $userId) // Ensure this matches your logic
                ->where('jobcard_id', $jobcard_id)
                ->first();

            // Check if the job card exists
            if (!$jobCard) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Job card not found',
                ], 404); // Not Found
            }

            return response()->json([
                'status' => 'success',
                'job_card' => $jobCard,
            ], 200); // Success

        } catch (\Exception $e) {
            // Log the error for debugging
            Log::error('Error fetching job card: ' . $e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch job card',
                'error' => $e->getMessage(), // Include error message in response for debugging
            ], 500); // Internal Server Error
        }
    }


  public function store(Request $request)
{
    // Validate the incoming request data
    $validator = Validator::make($request->all(), [
        'Clientname' => 'required|string|max:255',
        'Tel' => 'nullable|string|max:20',
        'ContactPerson' => 'nullable|string|max:255',
        'title' => 'nullable|string|max:255',
        'mobilePhone' => 'nullable|string|max:20',
        'VehicleRegNo' => 'nullable|string|max:50',
        'physicalLocation' => 'nullable|string|max:255',
        'deviceID' => 'nullable|string|max:100',
        'problemReported' => 'nullable|string',
        'DateReported' => 'nullable|date',
        'DateAttended' => 'nullable|date',
        'natureOfProblem' => 'nullable|string',
        'workDone' => 'nullable|string',
        'clientComment' => 'nullable|string',
        'service_type' => 'required|string|max:255',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status' => 'error',
            'message' => 'Validation error',
            'errors' => $validator->errors(),
        ], 422);
    }

    try {
        $jobCard = JobCard::create($request->all() + ['user_id' => Auth::id()]);

        return response()->json([
            'status' => 'success',
            'message' => 'Job card created successfully',
            'job_card' => $jobCard,
        ], 201);
    } catch (\Exception $e) {
        Log::error('Error creating job card: ' . $e->getMessage());

        return response()->json([
            'status' => 'error',
            'message' => 'Failed to create job card',
        ], 500);
    }
}



    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'Clientname' => 'required|string|max:255',
            'Tel' => 'nullable|string|max:20',
            'ContactPerson' => 'nullable|string|max:255',
            'title' => 'nullable|string|max:255',
            'mobilePhone' => 'nullable|string|max:20',
            'VehicleRegNo' => 'nullable|string|max:50',
            'physicalLocation' => 'nullable|string|max:255',
            'deviceID' => 'nullable|string|max:100',
            'problemReported' => 'nullable|string',
            'DateReported' => 'nullable|date',
            'DateAttended' => 'nullable|date',
            'natureOfProblem' => 'nullable|string',
            'workDone' => 'nullable|string',
            'clientComment' => 'nullable|string',
            'service_type' => 'required|string|max:255', // Added service_type
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $jobCard = JobCard::where('id', $id)
                ->where('user_id', Auth::id())
                ->first();

            if (!$jobCard) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Job card not found',
                ], 404);
            }

            $jobCard->update($request->all());

            return response()->json([
                'status' => 'success',
                'message' => 'Job card updated successfully',
                'job_card' => $jobCard,
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error updating job card: ' . $e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update job card',
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $jobCard = JobCard::where('id', $id)
                ->where('user_id', Auth::id())
                ->first();

            if (!$jobCard) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Job card not found',
                ], 404);
            }

            $jobCard->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Job card deleted successfully',
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error deleting job card: ' . $e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to delete job card',
            ], 500);
        }
    }


    public function countJobCards()
{
    try {
        // Count the total number of job cards for the logged-in user
        $count = JobCard::where('user_id', Auth::id())
            ->count();

        // Return the count as JSON
        return response()->json([
            'status' => 'success',
            'count' => $count,
        ], 200);
    } catch (\Exception $e) {
        // Log the error message
        Log::error('Error counting job cards: ' . $e->getMessage());

        // Return an error response
        return response()->json([
            'status' => 'error',
            'message' => 'Failed to count job cards',
        ], 500);
    }
}
}
