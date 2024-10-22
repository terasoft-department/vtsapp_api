<?php

namespace App\Http\Controllers;

use App\Models\CheckList;
use App\Models\Customer;
use App\Models\Payment;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class CheckListController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

  public function autoFillDetails(Request $request)
{
    // Validate the input for plate_number
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

    // Search for the vehicle using the plate_number
    $vehicle = Vehicle::where('plate_number', $request->plate_number)->first();
    if (!$vehicle) {
        return response()->json([
            'status' => 'error',
            'message' => 'Vehicle not found for the provided plate number.',
        ], 404);
    }

    // Find the customer associated with the vehicle
    $customer = Customer::find($vehicle->customer_id);
    if (!$customer) {
        return response()->json([
            'status' => 'error',
            'message' => 'Customer not found for the vehicle.',
        ], 404);
    }

    // Now, find the payment information for the customer
    $payment = Payment::where('customername', $customer->customername)->first();
    if (!$payment) {
        return response()->json([
            'status' => 'error',
            'message' => 'Payment not found for the customer.',
        ], 404);
    }

    // Return the vehicle, customer, and payment details
    return response()->json([
        'status' => 'success',
        'plate_number' => $vehicle->plate_number,
        'customer_id' => $customer->customer_id,
        'vehicle_id' => $vehicle->vehicle_id,
        'status' => $payment->status,
        'vehicle_name' => $vehicle->vehicle_name,
        'customername' => $customer->customername, // Assuming there's a name field
    ], 200);
}


  public function submitChecklist(Request $request)
{
    $request->validate([
        'vehicle_id' => 'required',
        'customer_id' => 'required',
        'rbt_status' => 'required|string',
        'batt_status' => 'required|string',
        'check_date' => 'required|date',
    ]);

    $checkList = new CheckList();
    $checkList->user_id = auth()->id(); // Authenticated user
    $checkList->vehicle_id = $request->vehicle_id;
    $checkList->customer_id = $request->customer_id;
    $checkList->plate_number = $request->plate_number;
    $checkList->rbt_status = $request->rbt_status;
    $checkList->batt_status = $request->batt_status;
    $checkList->check_date = $request->check_date;
    $checkList->save();

    return response()->json([
        'status' => 'success',
        'message' => 'Checklist submitted successfully!',
    ], 201);
}

public function filterChecklistByDate(Request $request)
{
    // Validate the date inputs
    $request->validate([
        'start_date' => 'required|date',
        'end_date' => 'required|date|after_or_equal:start_date',
    ]);

    try {
        // Retrieve checklist records for the logged-in user and within the provided date range
        $checklists = CheckList::where('user_id', Auth::id()) // Filter by logged-in user
            ->whereBetween('created_at', [$request->start_date, $request->end_date]) // Filter by created_at date range
            ->with(['vehicle', 'customer']) // Include related vehicle and customer data
            ->get();

        if ($checklists->isEmpty()) {
            return response()->json([
                'status' => 'error',
                'message' => 'No checklist records found for the given date range.',
            ], 404);
        }

        // Format the response
        $response = $checklists->map(function ($checklist) {
            return [
                'check_id' => $checklist->check_id,
                'plate_number' => $checklist->plate_number,
                'vehicle_name' => $checklist->vehicle->vehicle_name ?? 'Unknown Vehicle',
                'customername' => $checklist->customer->customername ?? 'Unknown Customer',
                'rbt_status' => $checklist->rbt_status,
                'check_date' => $checklist->check_date,
                'batt_status' => $checklist->batt_status,
                'created_at' => $checklist->created_at->format('Y-m-d H:i:s'), // Format created_at for response
            ];
        });

        return response()->json([
            'status' => 'success',
            'message' => 'Checklists retrieved successfully.',
            'data' => $response,
        ], 200);
    } catch (\Exception $e) {
        Log::error($e->getMessage());
        return response()->json([
            'status' => 'error',
            'message' => 'Failed to retrieve checklist records.',
        ], 500);
    }
}



}
