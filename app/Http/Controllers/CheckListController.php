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
use Carbon\Carbon;

class CheckListController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
        // Set default timezone to Nairobi (EAT)
        date_default_timezone_set('Africa/Nairobi');
    }

    public function autoFillDetails(Request $request)
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

        $vehicles = Vehicle::where('plate_number', 'like', '%' . $request->plate_number . '%')->get();

        if ($vehicles->isEmpty()) {
            return response()->json([
                'status' => 'error',
                'message' => 'No vehicles found for the provided plate number.',
            ], 404);
        }

        $data = [];

        foreach ($vehicles as $vehicle) {
            $customer = Customer::find($vehicle->customer_id);
            if (!$customer) {
                continue;
            }

            $invoice = $vehicle->invoice;

            $data[] = [
                'plate_number' => $vehicle->plate_number,
                'customer_id' => $customer->customer_id,
                'vehicle_id' => $vehicle->vehicle_id,
                'status' => $invoice ? $invoice->status : 'No Invoice',
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

        return response()->json([
            'status' => 'success',
            'data' => $data,
        ], 200);
    }

    public function submitChecklist(Request $request)
    {
        $request->validate([
            'checklists' => 'required|array',
            'checklists.*.vehicle_id' => 'required',
            'checklists.*.customer_id' => 'required',
            'checklists.*.rbt_status' => 'required|string',
            'checklists.*.batt_status' => 'required|string',
            'checklists.*.plate_number' => 'required|string',
            'checklists.*.check_date' => 'nullable|date',
        ]);

        DB::beginTransaction();
        try {
            $checklistsData = $request->checklists;
            $failedChecks = [];

            foreach ($checklistsData as $checklistData) {
                $checkList = new CheckList();
                $checkList->user_id = auth()->id();
                $checkList->vehicle_id = $checklistData['vehicle_id'];
                $checkList->customer_id = $checklistData['customer_id'];
                $checkList->plate_number = $checklistData['plate_number'];
                $checkList->rbt_status = $checklistData['rbt_status'];
                $checkList->batt_status = $checklistData['batt_status'];
                // Handle check_date with EAT timezone
                $checkList->check_date = $checklistData['check_date'] 
                    ? Carbon::parse($checklistData['check_date'])->setTimezone('Africa/Nairobi')
                    : Carbon::now('Africa/Nairobi');

                if (!$checkList->save()) {
                    $failedChecks[] = [
                        'data' => $checklistData,
                        'error' => 'Failed to save checklist entry.'
                    ];
                }
            }

            DB::commit();

            if (empty($failedChecks)) {
                return response()->json(['message' => 'All checklists submitted successfully!'], 201);
            }

            return response()->json(['message' => 'Some checklists failed to save.', 'failed' => $failedChecks], 400);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'An error occurred while processing your request.', 'error' => $e->getMessage()], 500);
        }
    }

    public function allChecklist(Request $request)
    {
        try {
            $checklists = CheckList::where('user_id', Auth::user()->user_id)
                ->with(['vehicle', 'customer'])
                ->orderBy('check_id', 'desc')
                ->get();

            if ($checklists->isEmpty()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'No checklist records found.',
                ], 404);
            }

            $response = $checklists->map(function ($checklist) {
                return [
                    'check_id' => $checklist->check_id,
                    'plate_number' => $checklist->plate_number,
                    'vehicle_name' => $checklist->vehicle->vehicle_name ?? 'Unknown Vehicle',
                    'customername' => $checklist->customer->customername ?? 'Unknown Customer',
                    'rbt_status' => $checklist->rbt_status,
                    'check_date' => $checklist->check_date 
                        ? Carbon::parse($checklist->check_date)->timezone('Africa/Nairobi')->format('Y-m-d H:i:s')
                        : null,
                    'batt_status' => $checklist->batt_status,
                    'created_at' => Carbon::parse($checklist->created_at)->timezone('Africa/Nairobi')->format('Y-m-d H:i:s'),
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

    public function showChecklist($check_id)
    {
        try {
            $checklist = CheckList::where('check_id', $check_id)
                ->where('user_id', Auth::user()->user_id)
                ->with(['vehicle', 'customer'])
                ->first();

            if (!$checklist) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Checklist not found or you do not have permission to view this checklist.',
                ], 404);
            }

            $response = [
                'check_id' => $checklist->check_id,
                'plate_number' => $checklist->plate_number,
                'vehicle_name' => $checklist->vehicle->vehicle_name ?? 'Unknown Vehicle',
                'customername' => $checklist->customer->customername ?? 'Unknown Customer',
                'rbt_status' => $checklist->rbt_status,
                'batt_status' => $checklist->batt_status,
                'check_date' => $checklist->check_date 
                    ? Carbon::parse($checklist->check_date)->timezone('Africa/Nairobi')->format('Y-m-d H:i:s')
                    : null,
                'created_at' => Carbon::parse($checklist->created_at)->timezone('Africa/Nairobi')->format('Y-m-d H:i:s'),
            ];

            return response()->json([
                'status' => 'success',
                'message' => 'Checklist retrieved successfully.',
                'data' => $response,
            ], 200);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve checklist record.',
            ], 500);
        }
    }

    public function editChecklist(Request $request, $check_id)
    {
        $request->validate([
            'rbt_status' => 'required|string|in:active,not active',
            'batt_status' => 'required|string|in:active,not active',
            'check_date' => 'nullable|date',
        ]);

        try {
            $checklist = CheckList::where('check_id', $check_id)
                ->where('user_id', auth()->user()->user_id)
                ->first();

            if (!$checklist) {
                return response()->json(['status' => 'error', 'message' => 'Checklist not found or unauthorized'], 404);
            }

            $checklist->rbt_status = $request->input('rbt_status');
            $checklist->batt_status = $request->input('batt_status');
            $checklist->check_date = $request->input('check_date') 
                ? Carbon::parse($request->input('check_date'))->setTimezone('Africa/Nairobi')
                : $checklist->check_date;
            $checklist->save();

            return response()->json(['status' => 'success', 'message' => 'Checklist updated successfully']);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'Failed to update checklist: ' . $e->getMessage()], 500);
        }
    }

    public function filterChecklistByDate(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        try {
            $startDate = Carbon::parse($request->start_date)->setTimezone('Africa/Nairobi')->startOfDay();
            $endDate = Carbon::parse($request->end_date)->setTimezone('Africa/Nairobi')->endOfDay();

            $checklists = CheckList::where('user_id', Auth::id())
                ->whereBetween('check_date', [$startDate, $endDate])
                ->with(['vehicle', 'customer'])
                ->get();

            if ($checklists->isEmpty()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'No checklist records found for the given date range.',
                ], 404);
            }

            $response = $checklists->map(function ($checklist) {
                return [
                    'check_id' => $checklist->check_id,
                    'plate_number' => $checklist->plate_number,
                    'vehicle_name' => $checklist->vehicle->vehicle_name ?? 'Unknown Vehicle',
                    'customername' => $checklist->customer->customername ?? 'Unknown Customer',
                    'rbt_status' => $checklist->rbt_status,
                    'check_date' => $checklist->check_date 
                        ? Carbon::parse($checklist->check_date)->timezone('Africa/Nairobi')->format('Y-m-d H:i:s')
                        : null,
                    'batt_status' => $checklist->batt_status,
                    'created_at' => Carbon::parse($checklist->created_at)->timezone('Africa/Nairobi')->format('Y-m-d H:i:s'),
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