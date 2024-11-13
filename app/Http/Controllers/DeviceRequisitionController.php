<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\DeviceRequisition;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class DeviceRequisitionController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum')->except(['register', 'login']);
    }

    public function index()
    {
        try {
            $requisitions = DeviceRequisition::where('user_id', Auth::id())
                ->orderBy('requisition_id', 'desc')
                ->get();

            return response()->json([
                'status' => 'success',
                'requisitions' => $requisitions,
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error fetching requisitions: ' . $e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch requisitions',
            ], 500);
        }
    }


  public function index1()
    {
        try {
            $requisitions = DeviceRequisition::where('user_id', Auth::id())
                ->where(function ($query) {
                    $query->where('status', 'approved')
                        ->orWhere(function ($q) {
                            $q->where('dispatched_status', 'dispatched')
                              ->where('dispatched_imeis', '!=', 'available');
                        });
                })
                ->orderBy('requisition_id', 'desc')
                ->get();

            return response()->json([
                'status' => 'success',
                'requisitions' => $requisitions,
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error fetching requisitions: ' . $e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch requisitions',
            ], 500);
        }
    }


public function store(Request $request)
{
    $validator = Validator::make($request->all(), [
        'descriptions' => 'required|string',
        'user_id' => 'required|integer',
        'master' => 'nullable|integer', // Changed to integer
        'I_button' => 'nullable|integer', // Changed to integer
        'buzzer' => 'nullable|integer', // Changed to integer
        'panick_button' => 'nullable|integer', // Changed to integer
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status' => 'error',
            'message' => 'Validation error',
            'errors' => $validator->errors(),
        ], 422);
    }

    try {
        // Debug log the request data
        Log::info('Creating requisition with data: ' . json_encode($request->all()));

        $requisition = new DeviceRequisition($request->all());
        $requisition->user_id = Auth::id(); // Override user_id with the logged-in user's ID
        $requisition->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Requisition created successfully',
            'requisition' => $requisition,
        ], 201);
    } catch (\Exception $e) {
        Log::error('Error creating requisition: ' . $e->getMessage());
        Log::error('Request Data: ' . json_encode($request->all()));

        return response()->json([
            'status' => 'error',
            'message' => 'Failed to create requisition',
        ], 500);
    }
}


    public function show($id)
    {
        try {
            $requisition = DeviceRequisition::where('requisition_id', $id)
                ->where('user_id', Auth::id())
                ->first();

            if (!$requisition) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Requisition not found or does not belong to the logged-in user',
                ], 404);
            }

            return response()->json([
                'status' => 'success',
                'requisition' => $requisition,
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error fetching requisition: ' . $e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch requisition',
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|string|in:pending,approved,denied',
            'descriptions' => 'nullable|string',
            'dateofProvision' => 'nullable|date', // Keep this nullable
            'master' => 'nullable|integer', // Changed to integer
            'I_button' => 'nullable|integer', // Changed to integer
            'buzzer' => 'nullable|integer', // Changed to integer
            'panick_button' => 'nullable|integer', // Changed to integer
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $requisition = DeviceRequisition::where('requisition_id', $id)
                ->where('user_id', Auth::id())
                ->first();

            if (!$requisition) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Requisition not found',
                ], 404);
            }

            // Only update the fields if they are provided
            $requisition->update($request->only([
                'status',
                'descriptions',
                'dateofProvision',
                'master',
                'I_button',
                'buzzer',
                'panick_button',
            ]));

            return response()->json([
                'status' => 'success',
                'message' => 'Requisition updated successfully',
                'requisition' => $requisition,
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error updating requisition: ' . $e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update requisition',
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $requisition = DeviceRequisition::where('requisition_id', $id)
                ->where('user_id', Auth::id())
                ->first();

            if (!$requisition) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Requisition not found',
                ], 404);
            }

            $requisition->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Requisition deleted successfully',
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error deleting requisition: ' . $e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to delete requisition',
            ], 500);
        }
    }


     public function countRequisitions()
    {
        try {
            $count = DeviceRequisition::where('user_id', Auth::id())
                ->where(function ($query) {
                    $query->where('status', 'approved')
                        ->orWhere(function ($q) {
                            $q->where('dispatched_status', 'dispatched')
                              ->where('dispatched_imeis', '!=', 'available');
                        });
                })
                ->count();

            return response()->json([
                'status' => 'success',
                'count' => $count,
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error counting requisitions: ' . $e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to count requisitions',
            ], 500);
        }
    }

    public function countMaster()
    {
        try {
            $masterCount = (int) DeviceRequisition::where('user_id', Auth::id())
                ->where('status', 'approved')
                ->where(function ($query) {
                    $query->where('status', 'approved')
                        ->orWhere(function ($q) {
                            $q->where('dispatched_status', 'dispatched')
                              ->where('dispatched_imeis', '!=', 'available');
                        });
                })
                ->sum('master');

            return response()->json([
                'status' => 'success',
                'master_count' => $masterCount
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error counting master devices: ' . $e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to count master devices',
            ], 500);
        }
    }

    public function countI_button()
    {
        try {
            $iButtonCount = (int) DeviceRequisition::where('user_id', Auth::id())
                ->where('status', 'approved')
                ->where(function ($query) {
                    $query->where('status', 'approved')
                        ->orWhere(function ($q) {
                            $q->where('dispatched_status', 'dispatched')
                              ->where('dispatched_imeis', '!=', 'available');
                        });
                })
                ->sum('I_button');

            return response()->json([
                'status' => 'success',
                'i_button_count' => $iButtonCount
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error counting I_button devices: ' . $e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to count I_button devices',
            ], 500);
        }
    }

    public function countBuzzer()
    {
        try {
            $buzzerCount = (int) DeviceRequisition::where('user_id', Auth::id())
                ->where('status', 'approved')
                ->where(function ($query) {
                    $query->where('status', 'approved')
                        ->orWhere(function ($q) {
                            $q->where('dispatched_status', 'dispatched')
                              ->where('dispatched_imeis', '!=', 'available');
                        });
                })
                ->sum('buzzer');

            return response()->json([
                'status' => 'success',
                'buzzer_count' => $buzzerCount
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error counting buzzer devices: ' . $e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to count buzzer devices',
            ], 500);
        }
    }

    public function countPanick_button()
    {
        try {
            $panickButtonCount = (int) DeviceRequisition::where('user_id', Auth::id())
                ->where('status', 'approved')
                ->where(function ($query) {
                    $query->where('status', 'approved')
                        ->orWhere(function ($q) {
                            $q->where('dispatched_status', 'dispatched')
                              ->where('dispatched_imeis', '!=', 'available');
                        });
                })
                ->sum('panick_button');

            return response()->json([
                'status' => 'success',
                'panick_button_count' => $panickButtonCount
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error counting panick_button devices: ' . $e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to count panick_button devices',
            ], 500);
        }
    }

    public function countTotalDevices()
    {
        try {
            $masterCount = (int) DeviceRequisition::where('user_id', Auth::id())
                ->where('status', 'approved')
                ->where(function ($query) {
                    $query->where('status', 'approved')
                        ->orWhere(function ($q) {
                            $q->where('dispatched_status', 'dispatched')
                              ->where('dispatched_imeis', '!=', 'available');
                        });
                })
                ->sum('master');

            $iButtonCount = (int) DeviceRequisition::where('user_id', Auth::id())
                ->where('status', 'approved')
                ->where(function ($query) {
                    $query->where('status', 'approved')
                        ->orWhere(function ($q) {
                            $q->where('dispatched_status', 'dispatched')
                              ->where('dispatched_imeis', '!=', 'available');
                        });
                })
                ->sum('I_button');

            $buzzerCount = (int) DeviceRequisition::where('user_id', Auth::id())
                ->where('status', 'approved')
                ->where(function ($query) {
                    $query->where('status', 'approved')
                        ->orWhere(function ($q) {
                            $q->where('dispatched_status', 'dispatched')
                              ->where('dispatched_imeis', '!=', 'available');
                        });
                })
                ->sum('buzzer');

            $panickButtonCount = (int) DeviceRequisition::where('user_id', Auth::id())
                ->where('status', 'approved')
                ->where(function ($query) {
                    $query->where('status', 'approved')
                        ->orWhere(function ($q) {
                            $q->where('dispatched_status', 'dispatched')
                              ->where('dispatched_imeis', '!=', 'available');
                        });
                })
                ->sum('panick_button');

            $totalDevices = $masterCount + $iButtonCount + $buzzerCount + $panickButtonCount;

            return response()->json([
                'status' => 'success',
                'total_devices' => $totalDevices
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error counting total devices: ' . $e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to count total devices',
            ], 500);
        }
    }

}





