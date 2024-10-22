<?php

namespace App\Http\Controllers;

use App\Models\Installation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class InstallationController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    public function index()
    {
        try {
            $installations = Installation::where('user_id', Auth::id())
                ->orderBy('installation_id', 'desc')
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
        $validator = Validator::make($request->all(), [
            'imei_number' => 'required|string|max:255',
            'customername' => 'required|string|max:255',
            'amount_paid' => 'required|numeric',

        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $installation = new Installation($request->all());
            $installation->user_id = Auth::id(); // Set user_id to the logged-in user's ID
            $installation->save();

            return response()->json([
                'status' => 'success',
                'message' => 'Installation created successfully',
                'installation' => $installation,
            ], 201);
        } catch (\Exception $e) {
            Log::error('Error creating installation: ' . $e->getMessage());
            Log::error('Request Data: ' . json_encode($request->all()));

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create installation',
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $installation = Installation::where('installation_id', $id)
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
            'imei_number' => 'nullable|string|max:255',
            'customername' => 'nullable|string|max:255',
            'amount_paid' => 'nullable|numeric',
            'status' => 'nullable|string|in:pending,completed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $installation = Installation::where('installation_id', $id)
                ->where('user_id', Auth::id())
                ->first();

            if (!$installation) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Installation not found',
                ], 404);
            }

            $installation->update($request->all());

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
            $installation = Installation::where('installation_id', $id)
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
}
