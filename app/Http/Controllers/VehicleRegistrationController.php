<?php
namespace App\Http\Controllers;

use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class VehicleRegistrationController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum'); // Ensure user is authenticated
    }

    // GET /vehicles
    public function index()
    {
        try {
            // Fetch all vehicles
            $vehicles = Vehicle::orderBy('vehicle_id', 'desc')->get();

            return response()->json([
                'status' => 'success',
                'vehicles' => $vehicles,
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error fetching vehicles: ' . $e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch vehicles',
            ], 500);
        }
    }

    // POST /vehicles
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'vehicle_name' => 'required|string|max:255',
            'category' => 'required|string|max:255',
            'plate_number' => 'required|string|max:255',
            'customer_id' => 'nullable',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422);
        }

        // Check if the vehicle with the same plate number already exists
        $existingVehicle = Vehicle::where('plate_number', $request->plate_number)->first();

        if ($existingVehicle) {
            return response()->json([
                'status' => 'error',
                'message' => 'Vehicle already registered',
                'vehicle' => $existingVehicle,
                'created_at' => $existingVehicle->created_at->format('Y-m-d H:i:s'),
            ], 409); // Conflict status code
        }

        try {
            $vehicle = new Vehicle($request->all());
            $vehicle->save();

            return response()->json([
                'status' => 'success',
                'message' => 'Vehicle registered successfully',
                'vehicle' => $vehicle,
            ], 201);
        } catch (\Exception $e) {
            Log::error('Error registering vehicle: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to register vehicle',
            ], 500);
        }
    }

    // GET /vehicles/{id}
    public function show($id)
    {
        try {
            $vehicle = Vehicle::where('vehicle_id', $id)->first();

            if (!$vehicle) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Vehicle not found',
                ], 404);
            }

            return response()->json([
                'status' => 'success',
                'vehicle' => $vehicle,
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error fetching vehicle: ' . $e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch vehicle',
            ], 500);
        }
    }

    // PUT /vehicles/{id}
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'vehicle_name' => 'nullable|string|max:255',
            'category' => 'nullable|string|max:255',
            'plate_number' => 'nullable|string|max:255',
            'customer_id' => 'nullable|integer|exists:customers,customer_id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $vehicle = Vehicle::where('vehicle_id', $id)->first();

            if (!$vehicle) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Vehicle not found',
                ], 404);
            }

            $vehicle->update($request->all());

            return response()->json([
                'status' => 'success',
                'message' => 'Vehicle updated successfully',
                'vehicle' => $vehicle,
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error updating vehicle: ' . $e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update vehicle',
            ], 500);
        }
    }

    // DELETE /vehicles/{id}
    public function destroy($id)
    {
        try {
            $vehicle = Vehicle::where('vehicle_id', $id)->first();

            if (!$vehicle) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Vehicle not found',
                ], 404);
            }

            $vehicle->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Vehicle deleted successfully',
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error deleting vehicle: ' . $e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to delete vehicle',
            ], 500);
        }
    }


// POST /vehicles/filter
public function filter(Request $request)
{
    $validator = Validator::make($request->all(), [
        'plate_number' => 'required|string|max:255',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status' => 'error',
            'message' => 'Validation error',
            'errors' => $validator->errors(),
        ], 422);
    }

    try {
        // Attempt to find the vehicle by plate_number and include the customer
        $vehicle = Vehicle::with('customer')->where('plate_number', $request->plate_number)->first();

        if ($vehicle) {
            return response()->json([
                'status' => 'success',
                'vehicle' => [
                    'vehicle_id' => $vehicle->vehicle_id,
                    'vehicle_name' => $vehicle->vehicle_name,
                    'category' => $vehicle->category,
                    'plate_number' => $vehicle->plate_number,
                    'customer' => [
                        'customer_name' => $vehicle->customer->customername,
                    ],
                ],
            ], 200);
        } else {
            return response()->json([
                'status' => 'error',
                'message' => 'Vehicle not found',
            ], 404);
        }
    } catch (\Exception $e) {
        Log::error('Error filtering vehicle: ' . $e->getMessage());

        return response()->json([
            'status' => 'error',
            'message' => 'Failed to filter vehicle',
        ], 500);
    }
}


}
















