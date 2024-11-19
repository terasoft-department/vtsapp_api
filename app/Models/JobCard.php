<?php

namespace App\Http\Controllers;

use App\Models\NewInstallation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\JobCard;
use Illuminate\Support\Facades\Log;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use Illuminate\Support\Facades\Validator;

class NewInstallationController extends Controller
{
    public function __construct()
    {
        // Cloudinary is configured in the config file
        $this->cloudinary = new Cloudinary([
            'cloud' => [
                'cloud_name' => config('cloudinary.cloud.cloud_name'),
                'api_key' => config('cloudinary.cloud.api_key'),
                'api_secret' => config('cloudinary.cloud.api_secret'),
            ],
            'url' => [
                'secure' => true,
            ],
        ]);
    }

    public function index()
    {
        try {
            $installations = NewInstallation::where('user_id', Auth::id())
                ->orderBy('id', 'desc')
                ->get();

            return response()->json([
                'status' => 'success',
                'installations' => $installations,
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error fetching installations: ' . $e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch installations',
            ], 500);
        }
    }

    public function store(Request $request)
    {
        // Step 1: Validate the incoming request
        $validator = Validator::make($request->all(), [
            'customerName' => 'required|string|max:255',
            'DeviceNumber' => 'nullable|string|max:255',
            'CarRegNumber' => 'nullable|string|max:255',
            'customerPhone' => 'nullable|string|max:255',
            'simCardNumber' => 'nullable|string|max:255',
            'picha_ya_gari_kwa_mbele' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'picha_ya_device_anayoifunga' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'picha_ya_hiyo_karatasi_ya_simCardNumber' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
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
                'user_id' => Auth::id(), // Keep the user_id constant as the logged-in user
                // Include uploaded image URLs if available
                'picha_ya_gari_kwa_mbele' => $uploadedImages['picha_ya_gari_kwa_mbele'] ?? null,
                'picha_ya_device_anayoifunga' => $uploadedImages['picha_ya_device_anayoifunga'] ?? null,
                'picha_ya_hiyo_karatasi_ya_simCardNumber' => $uploadedImages['picha_ya_hiyo_karatasi_ya_simCardNumber'] ?? null,
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

    protected function uploadImages(Request $request)
    {
        $imageUrls = [];

        // Check and upload each image, if present
        if ($request->hasFile('picha_ya_gari_kwa_mbele')) {
            $imageUrls['picha_ya_gari_kwa_mbele'] = $this->uploadImageToCloud($request->file('picha_ya_gari_kwa_mbele'));
        }
        if ($request->hasFile('picha_ya_device_anayoifunga')) {
            $imageUrls['picha_ya_device_anayoifunga'] = $this->uploadImageToCloud($request->file('picha_ya_device_anayoifunga'));
        }
        if ($request->hasFile('picha_ya_hiyo_karatasi_ya_simCardNumber')) {
            $imageUrls['picha_ya_hiyo_karatasi_ya_simCardNumber'] = $this->uploadImageToCloud($request->file('picha_ya_hiyo_karatasi_ya_simCardNumber'));
        }

        return $imageUrls;
    }

    protected function uploadImageToCloud($image)
    {
        try {
            // Cloudinary upload
            $uploadedFile = Cloudinary::upload($image->getRealPath(), [
                'folder' => 'installations_images', // optional folder name
            ]);
            return $uploadedFile->getSecureUrl(); // Return the image URL
        } catch (\Exception $e) {
            Log::error('Cloudinary upload failed: ' . $e->getMessage());
            throw $e; // Rethrow exception for further handling
        }
    }

    public function show($id)
    {
        try {
            $installation = NewInstallation::where('id', $id)
                ->where('user_id', Auth::id())
                ->first();

            if (!$installation) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Installation not found or does not belong to the logged-in user',
                ], 404);
            }

            return response()->json([
                'status' => 'success',
                'installation' => $installation,
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error fetching installation: ' . $e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch installation',
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'customerName' => 'required|string|max:255',
            'DeviceNumber' => 'nullable|string|max:100',
            'CarRegNumber' => 'nullable|string|max:50',
            'customerPhone' => 'nullable|string|max:20',
            'simCardNumber' => 'nullable|string|max:50',
            'user_id' => 'nullable|exists:users,id',
            'picha_ya_gari_kwa_mbele' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'picha_ya_device_anayoifunga' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'picha_ya_hiyo_karatasi_ya_simCardNumber' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $installation = NewInstallation::where('id', $id)
                ->where('user_id', Auth::id())
                ->first();

            if (!$installation) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Installation not found',
                ], 404);
            }

            // Upload images to Cloudinary if they exist
            $imageUrls = [];
            if ($request->hasFile('picha_ya_gari_kwa_mbele')) {
                $imageUrls['picha_ya_gari_kwa_mbele'] = $this->uploadImageToCloud($request->file('picha_ya_gari_kwa_mbele'));
            }
            if ($request->hasFile('picha_ya_device_anayoifunga')) {
                $imageUrls['picha_ya_device_anayoifunga'] = $this->uploadImageToCloud($request->file('picha_ya_device_anayoifunga'));
            }
            if ($request->hasFile('picha_ya_hiyo_karatasi_ya_simCardNumber')) {
                $imageUrls['picha_ya_hiyo_karatasi_ya_simCardNumber'] = $this->uploadImageToCloud($request->file('picha_ya_hiyo_karatasi_ya_simCardNumber'));
            }

            // Update the installation with the new data
            $installation->update(array_merge($request->all(), $imageUrls));

            return response()->json([
                'status' => 'success',
                'message' => 'Installation updated successfully',
                'installation' => $installation,
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error updating installation: ' . $e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update installation',
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $installation = NewInstallation::where('id', $id)
                ->where('user_id', Auth::id())
                ->first();

            if (!$installation) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Installation not found',
                ], 404);
            }

            // Delete job card
            $installation->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Installation deleted successfully',
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error deleting installation: ' . $e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to delete installation',
            ], 500);
        }
    }
}
