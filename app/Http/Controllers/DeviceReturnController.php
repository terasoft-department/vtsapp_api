<?php

namespace App\Http\Controllers;

use App\Models\ReturnDevice;
use App\Models\Vehicle;
use App\Models\Customer;
use App\Models\JobCard; // Include JobCard model
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class DeviceReturnController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum'); // Ensure user is authenticated
    }


  // In your VehicleController.php

public function filterByPlateNumber(Request $request)
{
    // Validate the plate number input
    $request->validate([
        'plate_number' => 'required|string|max:255',
    ]);

    try {
        // Find the vehicle by plate number
        $vehicle = Vehicle::where('plate_number', $request->plate_number)->first();

        if (!$vehicle) {
            return response()->json([
                'status' => 'error',
                'message' => 'Vehicle not found for the provided plate number.',
            ], 404);
        }

        // Find the customer based on the vehicle's customer_id
        $customer = Customer::where('customer_id', $vehicle->customer_id)->first();

        // Find the job card by plate number to get the IMEI number
        $jobCard = JobCard::where('plate_number', $request->plate_number)->first();

        // Prepare the response data
        $responseData = [
            'status' => 'success',
            'plate_number' => $vehicle->plate_number,
            'customer_id' => $customer ? (string) $customer->customer_id : 'Unknown Customer ID', // Ensure customer_id is a string
            'customername' => $customer ? $customer->customername : 'Unknown Customer',
            'imei_number' => $jobCard ? $jobCard->imei_number : null, // Keep IMEI number as integer or null
        ];

        return response()->json($responseData);

    } catch (\Exception $e) {
        Log::error($e->getMessage());
        return response()->json([
            'status' => 'error',
            'message' => 'Failed to retrieve vehicle or customer data.',
        ], 500);
    }
}



    /**
     * Store the return device information.
     */
    public function store(Request $request)
    {
        // Validate the form input
        $validator = Validator::make($request->all(), [
            'plate_number' => 'required|string|max:255',
            'customer_id' => 'required|integer|exists:customers,customer_id', // Ensure the customer exists
            'imei_number' => 'required|string|max:255', // Validate imei_number
            'reason' => 'required|string',
        ]);

        // Check if validation fails
        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            // Create a new return device entry
            $return = new ReturnDevice();
            $return->user_id = Auth::id(); // Automatically set the logged-in user ID
            $return->plate_number = $request->plate_number;
            $return->customer_id = $request->customer_id;
            $return->imei_number = $request->imei_number; // Set imei_number
            $return->reason = $request->reason;

            $return->save(); // Save the return entry

            return response()->json([
                'status' => 'success',
                'message' => 'Device return created successfully',
                'return' => $return,
            ], 201);
        } catch (\Exception $e) {
            Log::error($e->getMessage()); // Log the error
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create device return',
            ], 500);
        }
    }

  public function getAllReturns()
{
    try {
        // Retrieve return device records for the authenticated user
        $userId = Auth::id(); // Get the logged-in user's ID

        // Debug: Log the user ID being used for the query
        Log::info('Fetching return device records for user ID: ' . $userId);

        // Retrieve returns with related models (vehicle, customer, imeiNumber)
        $returns = ReturnDevice::with(['vehicle', 'customer', 'imeiNumber']) // Include related models
            ->where('user_id', $userId) // Filter by logged-in user
            ->select('plate_number', 'customer_id', 'reason', 'imei_number','created_at') // Select required fields
            ->get();

        // Check if records exist
        if ($returns->isEmpty()) {
            return response()->json([
                'status' => 'error',
                'message' => 'No device return records found for the logged-in user.',
            ], 404); // HTTP 404 Not Found
        }

        // Format the response data
        $response = $returns->map(function ($return) {
            return [
                'plate_number' => $return->plate_number,
                'customername' => $return->customer->customername ?? 'Unknown Customer id',
                'reason' => $return->reason,
                'status' => $return->status,
                'imei_number' => $return->imei_number, // Include imei_number
                'created_at' => $return->created_at,
            ];
        });

        return response()->json([
            'status' => 'success',
            'message' => 'Device return records retrieved successfully',
            'data' => $response,
        ], 200); // HTTP 200 OK

    } catch (\Exception $e) {
        // Log detailed error message for debugging
        Log::error('Error fetching return device records: ' . $e->getMessage());

        // Return the error message
        return response()->json([
            'status' => 'error',
            'message' => 'Failed to retrieve device return records. ' . $e->getMessage(), // Include the exception message for clarity
        ], 500); // HTTP 500 Internal Server Error
    }
}


}
