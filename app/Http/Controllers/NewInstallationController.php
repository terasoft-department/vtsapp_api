<?php

namespace App\Http\Controllers;

use App\Models\NewInstallation;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use Illuminate\Support\Facades\Validator;


class NewInstallationController extends Controller
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
        // Validate incoming request data
        $validatedData = $request->validate([
            'customerName' => 'required|string|max:255',
            'plateNumber' => 'nullable|string|max:255',
            'DeviceNumber' => 'nullable|string|max:255',
            'CarRegNumber' => 'nullable|string|max:255',
            'customerPhone' => 'nullable|string|max:255',
            'simCardNumber' => 'nullable|string|max:255',
            'picha_ya_gari_kwa_mbele' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'picha_ya_device_anayoifunga' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'picha_ya_hiyo_karatasi_ya_simCardNumber' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        // Upload images to Cloudinary and store their URLs
        $imageUrls = [];
        $imageFields = [
            'picha_ya_gari_kwa_mbele',
            'picha_ya_device_anayoifunga',
            'picha_ya_hiyo_karatasi_ya_simCardNumber'
        ];

        foreach ($imageFields as $field) {
            if ($request->hasFile($field)) {
                $uploadedImage = Cloudinary::upload($request->file($field)->getRealPath(), [
                    'folder' => 'installations',
                    'resource_type' => 'image',
                ]);
                $imageUrls[$field] = $uploadedImage->getSecurePath();
            }
        }

        // Store the installation data in the database
        $installation = NewInstallation::create([
            'customerName' => $validatedData['customerName'],
            'plateNumber' => $validatedData['plateNumber'] ?? null,
            'DeviceNumber' => $validatedData['DeviceNumber'] ?? null,
            'CarRegNumber' => $validatedData['CarRegNumber'] ?? null,
            'customerPhone' => $validatedData['customerPhone'] ?? null,
            'simCardNumber' => $validatedData['simCardNumber'] ?? null,
            'picha_ya_gari_kwa_mbele' => $imageUrls['picha_ya_gari_kwa_mbele'] ?? null,
            'picha_ya_device_anayoifunga' => $imageUrls['picha_ya_device_anayoifunga'] ?? null,
            'picha_ya_hiyo_karatasi_ya_simCardNumber' => $imageUrls['picha_ya_hiyo_karatasi_ya_simCardNumber'] ?? null,
            'user_id' => Auth::id(),
        ]);

        return response()->json([
            'message' => 'New installation created successfully!',
            'installation' => $installation
        ], 201);
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
            'plateNumber' => 'nullable|string|max:50',
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

            // Update the NewInstallation record
            $installation->update(array_merge(
                $request->except(['picha_ya_gari_kwa_mbele', 'picha_ya_device_anayoifunga', 'picha_ya_hiyo_karatasi_ya_simCardNumber']),
                $imageUrls
            ));

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

    protected function uploadImageToCloud($image)
    {
        try {
            $upload = $this->cloudinary->uploadApi()->upload($image->getRealPath(), [
                'folder' => 'new_installations',
            ]);

            return $upload['secure_url']; // Cloudinary URL
        } catch (\Exception $e) {
            Log::error('Error uploading image to Cloudinary: ' . $e->getMessage());
            throw new \Exception('Failed to upload image to Cloudinary');
        }
    }
}
