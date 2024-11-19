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
        $this->middleware('auth:sanctum')->except(['register', 'login']);
    }

    public function index()
    {
        try {
            $jobCards = JobCard::where('user_id', Auth::id())
                ->orderBy('id', 'desc')
                ->get();

            return response()->json([
                'status' => 'success',
                'job_cards' => $jobCards,
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error fetching job cards: ' . $e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch job cards',
            ], 500);
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



    public function show($id)
    {
        try {
            $jobCard = JobCard::where('id', $id)
                ->where('user_id', Auth::id())
                ->first();

            if (!$jobCard) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Job card not found or does not belong to the logged-in user',
                ], 404);
            }

            return response()->json([
                'status' => 'success',
                'job_card' => $jobCard,
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error fetching job card: ' . $e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch job card',
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
