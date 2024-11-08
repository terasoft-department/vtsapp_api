<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\VehiclesImport;

class ImportVehicleController extends Controller
{
   public function uploadVehicles(Request $request)
{
    $request->validate([
        'file' => 'required|mimes:xlsx,xls,csv',
    ]);

    try {
        Excel::import(new VehiclesImport, $request->file('file'));
        return response()->json(['message' => 'Vehicles uploaded and saved successfully'], 200);
    } catch (\Exception $e) {
        // Log the exception message
        \Log::error('Excel Import Error: ' . $e->getMessage());
        return response()->json(['message' => 'An error occurred during the import process'], 500);
    }
}

}
