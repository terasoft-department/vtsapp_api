<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Device;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AddDevicesController extends Controller
{
    public function store(Request $request)
{
    try {
        // Retrieve all devices from the request
        $devices = $request->all();

        // Initialize arrays to hold errors and successful insertions
        $errors = [];
        $insertedDevices = [];

        // Loop through each device and validate
        foreach ($devices as $index => $deviceData) {
            // Validate each device's data
            $validator = Validator::make($deviceData, [
                'imei_number' => 'required|numeric',
                'category' => 'required|string|max:255',
            ]);

            if ($validator->fails()) {
                $errors[] = [
                    'index' => $index,
                    'errors' => $validator->errors()
                ];
                continue; // Skip to the next device if validation fails
            }

            // Check if IMEI number already exists
            $existingDevice = Device::where('imei_number', $deviceData['imei_number'])->first();

            if ($existingDevice) {
                $errors[] = [
                    'index' => $index,
                    'errors' => ['imei_number' => 'This IMEI number already exists.']
                ];
                continue; // Skip to the next device if IMEI already exists
            }

            // Create the device
            $device = Device::create([
                'imei_number' => $deviceData['imei_number'],
                'category' => $deviceData['category'],
            ]);

            $insertedDevices[] = $device;
        }

        // Return a response
        if (!empty($errors)) {
            return response()->json([
                'status' => 'partial success',
                'message' => 'Some devices could not be added due to validation errors or existing IMEI numbers',
                'inserted_devices' => $insertedDevices,
                'errors' => $errors
            ], 207); // 207 Multi-Status
        }

        return response()->json([
            'status' => 'success',
            'message' => 'All devices added successfully',
            'inserted_devices' => $insertedDevices
        ], 201); // Created

    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => 'Failed to add devices',
            'error' => $e->getMessage()
        ], 500); // Internal Server Error
    }
}



    // Function to get all devices
    public function index()
    {
        try {
            $devices = Device::all();
            return response()->json([
                'status' => 'success',
                'data' => $devices
            ], 200); // OK
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch devices',
                'error' => $e->getMessage()
            ], 500); // Internal Server Error
        }
    }

    // Function to get a single device by ID
    public function show($id)
    {
        try {
            $device = Device::find($id);

            if (!$device) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Device not found'
                ], 404); // Not Found
            }

            return response()->json([
                'status' => 'success',
                'data' => $device
            ], 200); // OK

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch device',
                'error' => $e->getMessage()
            ], 500); // Internal Server Error
        }
    }

    // Function to update a device
    public function update(Request $request, $id)
    {
        try {
            $device = Device::find($id);

            if (!$device) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Device not found'
                ], 404); // Not Found
            }

            // Validate the request data
            $validator = Validator::make($request->all(), [
                'imei_number' => 'sometimes|unique:devices,imei_number,' . $id . '|numeric',
                'category' => 'sometimes|string|max:255',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 400); // Bad Request
            }

            // Update the device
            $device->update($request->only(['imei_number', 'category']));

            return response()->json([
                'status' => 'success',
                'message' => 'Device updated successfully',
                'data' => $device
            ], 200); // OK

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update device',
                'error' => $e->getMessage()
            ], 500); // Internal Server Error
        }
    }

    // Function to delete a device
    public function destroy($id)
    {
        try {
            $device = Device::find($id);

            if (!$device) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Device not found'
                ], 404); // Not Found
            }

            $device->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Device deleted successfully'
            ], 200); // OK

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to delete device',
                'error' => $e->getMessage()
            ], 500); // Internal Server Error
        }
    }
}
