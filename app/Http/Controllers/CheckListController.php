<?php

namespace App\Http\Controllers;

use App\Models\CheckList;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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

    // Search for vehicles with a plate number that contains the input string
    $vehicles = Vehicle::where('plate_number', 'like', '%' . $request->plate_number . '%')->get();

    if ($vehicles->isEmpty()) {
        return response()->json([
            'status' => 'error',
            'message' => 'No vehicles found for the provided plate number.',
        ], 404);
    }

    $data = [];

    foreach ($vehicles as $vehicle) {
        // Find the customer associated with the vehicle
        $customer = Customer::find($vehicle->customer_id);
        if (!$customer) {
            continue; // Skip if customer not found
        }

        // Use the relationship to find the invoice or assignment details for the vehicle
        $invoice = $vehicle->invoice;  // Assuming the relationship `invoice` is defined on `Vehicle` model

        // Collect the details for each matched vehicle
        $data[] = [
            'plate_number' => $vehicle->plate_number,
            'customer_id' => $customer->customer_id,
            'vehicle_id' => $vehicle->vehicle_id,
            'status' => $invoice ? $invoice->status : 'No Invoice', // Default status if no invoice found
            'vehicle_name' => $vehicle->vehicle_name,
            'customername' => $customer->customername,
        ];
    }

    if (empty($data)) {
        return response()->json([
            'status' => 'error',
            'message' => 'No records found for the provided plate number.',
        ], 404);
    }

    // Return all matching vehicles with customer and invoice details
    return response()->json([
        'status' => 'success',
        'data' => $data,
    ], 200);
}

public function submitChecklist(Request $request)
{
    // Validate that the request is an array of checklists with the required fields
    $request->validate([
        'checklists' => 'required|array',
        'checklists.*.vehicle_id' => 'required',
        'checklists.*.customer_id' => 'required',
        'checklists.*.rbt_status' => 'required|string',
        'checklists.*.batt_status' => 'required|string',
        'checklists.*.plate_number' => 'required|string',
        'checklists.*.check_date' => 'nullable|date', // Make check_date optional
    ]);

    // Use database transactions to ensure atomicity
    DB::beginTransaction();
    try {
        $checklistsData = $request->checklists; // Get the checklists array
        $failedChecks = [];

        // Loop through each checklist entry and save to the database
        foreach ($checklistsData as $checklistData) {
            // Create a new checklist entry
            $checkList = new CheckList();
            $checkList->user_id = auth()->id(); // Set the authenticated user
            $checkList->vehicle_id = $checklistData['vehicle_id'];
            $checkList->customer_id = $checklistData['customer_id'];
            $checkList->plate_number = $checklistData['plate_number'];
            $checkList->rbt_status = $checklistData['rbt_status'];
            $checkList->batt_status = $checklistData['batt_status'];
            $checkList->check_date = $checklistData['check_date'];

            // Attempt to save the checklist, catch any errors
            if (!$checkList->save()) {
                $failedChecks[] = [
                    'data' => $checklistData,
                    'error' => 'Failed to save checklist entry.' // Customize error message as needed
                ];
            }
        }

        // Commit the transaction if all entries saved
        DB::commit();

        // Provide feedback on successful submissions
        if (empty($failedChecks)) {
            return response()->json(['message' => 'All checklists submitted successfully!'], 201);
        }

        return response()->json(['message' => 'Some checklists failed to save.', 'failed' => $failedChecks], 400);

    } catch (\Exception $e) {
        DB::rollBack(); // Rollback the transaction in case of an error
        return response()->json(['message' => 'An error occurred while processing your request.', 'error' => $e->getMessage()], 500);
    }
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
















