<?php

namespace App\Http\Controllers;

use App\Models\JobCard;
use App\Models\User;
use App\Models\Assignment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Cloudinary\Cloudinary; // Make sure you have this imported
use Cloudinary\Transformation\Transformation;

class JobCardController extends Controller
{
    protected $cloudinary;

    public function __construct()
    {
        $this->cloudinary = new Cloudinary([
            'cloud' => [
                'cloud_name' => config('cloudinary.cloud.cloud_name'),
                'api_key' => config('cloudinary.cloud.api_key'),
                'api_secret' => config('cloudinary.cloud.api_secret'),
            ],
            'url' => [
                'secure' => true, // Use HTTPS
            ],
        ]);
    }

    public function index()
{
    try {
        // Fetch job cards with related customer data
        $jobCards = JobCard::where('user_id', Auth::id())
            ->with('customer:customer_id,customername') // Include the customername from the Customer model
            ->orderBy('jobcard_id', 'desc')
            ->get();

        // Modify the output to include customername
        $jobCards->transform(function ($jobCard) {
            return [
                'customer_id' => $jobCard->customer_id,
                'jobcard_id' => $jobCard->jobcard_id,
                'customername' => $jobCard->customer->customername ?? 'N/A', // Use customername instead of customer_id
                'contact_person' => $jobCard->contact_person,
                'title' => $jobCard->title,
                'mobile_number' => $jobCard->mobile_number,
                'physical_location' => $jobCard->physical_location,
                'plate_number' => $jobCard->plate_number,
                'problem_reported' => $jobCard->problem_reported,
                'title' => $jobCard->title,
                'natureOf_ProblemAt_site' => $jobCard->natureOf_ProblemAt_site,
                'service_type' => $jobCard->service_type,
                'date_attended' => $jobCard->date_attended,
                'assignment_id' => $jobCard->assignment_id,
                'work_done' => $jobCard->work_done,
                'imei_number' => $jobCard->imei_number,
                'vehicle_regNo' => $jobCard->vehicle_regNo,
                'client_comment' => $jobCard->client_comment,
                'user_id' => $jobCard->user_id,
                 'pre_workdone_picture' => $jobCard->pre_workdone_picture,
                  'post_workdone_picture' => $jobCard->post_workdone_picture,
                   'carPlateNumber_picture' => $jobCard->carPlateNumber_picture,
                    'tampering_evidence_picture' => $jobCard->tampering_evidence_picture,
            ];
        });

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



    public function fetchvehico_regNo()
    {
        try {
            // Fetch job cards with only the 'vehicle_regno' attribute
            $jobCards = JobCard::where('user_id', Auth::id())
                ->orderBy('jobcard_id', 'desc')
                ->get(['vehicle_regno']); // Select only the 'vehicle_regno' field

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
    // Step 1: Validate the incoming request
    $validator = Validator::make($request->all(), [
        'customer_id' => 'nullable',
        'contact_person' => 'nullable|string|max:255',
        'mobile_number' => 'nullable|string|max:20',
        'physical_location' => 'nullable|string|max:255',
        'problem_reported' => 'nullable|string',
        'natureOf_ProblemAt_site' => 'nullable|string',
        'service_type' => 'nullable|string',
        'title' => 'nullable|string',
        'date_attended' => 'nullable|date',
        'plate_number' => 'nullable|string',
        'imei_number' => 'nullable|string',
        'work_done' => 'nullable|string',
        'client_comment' => 'nullable|string',
        // Ensure images are optional
        'pre_workdone_picture' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        'post_workdone_picture' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        'carPlateNumber_picture' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        'tampering_evidence_picture' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
    ]);

    // Step 2: Check for validation failures
    if ($validator->fails()) {
        return response()->json([
            'status' => 'error',
            'message' => 'Validation error',
            'errors' => $validator->errors(),
        ], 422);
    }

    // Step 3: Upload images to Cloudinary
    try {
        $uploadedImages = $this->uploadImages($request);
    } catch (\Exception $e) {
        Log::error('Image upload failed: ' . $e->getMessage());

        return response()->json([
            'status' => 'error',
            'message' => 'Image upload failed',
        ], 500);
    }

    // Step 4: Create a new job card
    try {
        $jobCard = JobCard::create(array_merge($request->all(), [
            'user_id' => Auth::id(), // Associate job card with logged-in user
            // Include uploaded image URLs if available
            'pre_workdone_picture' => $uploadedImages['pre_workdone_picture'] ?? null,
            'post_workdone_picture' => $uploadedImages['post_workdone_picture'] ?? null,
            'carPlateNumber_picture' => $uploadedImages['carPlateNumber_picture'] ?? null,
            'tampering_evidence_picture' => $uploadedImages['tampering_evidence_picture'] ?? null,
        ]));

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
            $jobCard = JobCard::where('jobcard_id', $id)
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
            'customername' => 'nullable|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'title' => 'nullable|string|max:255',
            'mobile_number' => 'nullable|string|max:20',
            'physical_location' => 'nullable|string|max:255',
            'device_id' => 'nullable|exists:devices,device_id',
            'problem_reported' => 'nullable|string',
            'natureOf_ProblemAt_site' => 'nullable|string',
            'service_type' => 'nullable|string',
            'date_attended' => 'nullable|date',
            'work_done' => 'nullable|string',
            'vehicle_regno' => 'nullable|string|max:20',
            'client_comment' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $jobCard = JobCard::where('jobcard_id', $id)
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
            $jobCard = JobCard::where('jobcard_id', $id)
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

    private function uploadImages(Request $request)
    {
        $uploadedImages = [];

        // Handle each image upload
        foreach (['pre_workdone_picture', 'post_workdone_picture', 'carPlateNumber_picture', 'tampering_evidence_picture'] as $imageField) {
            if ($request->hasFile($imageField)) {
                $file = $request->file($imageField);

                // Upload to Cloudinary
                $uploadResult = $this->cloudinary->uploadApi()->upload($file->getRealPath(), [
                    'folder' => 'job_cards', // Optional: Set a folder for organization
                ]);

                // Store the image URL
                $uploadedImages[$imageField] = $uploadResult['secure_url'];
            }
        }

        return $uploadedImages;
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



