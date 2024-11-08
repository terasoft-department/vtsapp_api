<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\VehiclesImport;
use Illuminate\Validation\ValidationException;

class ImportVehicleController extends Controller
{
    public function uploadVehicles(Request $request)
    {
        try {
            // Validate that a file is uploaded and has the correct format
            $request->validate([
                'file' => 'required|mimes:xlsx,xls,csv',
            ]);

            // Import data from the uploaded file
            Excel::import(new VehiclesImport, $request->file('file'));

            // Return success response
            return response()->json(['message' => 'Vehicles uploaded and saved successfully'], 200);
        } catch (ValidationException $e) {
            // Return validation errors with a 422 Unprocessable Entity status code
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            // Log the exception message and return a generic error response
            \Log::error('Excel Import Error: ' . $e->getMessage());
            return response()->json(['message' => 'An error occurred during the import process'], 500);
        }
    }
}
