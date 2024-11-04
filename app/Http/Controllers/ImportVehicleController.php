<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\VehiclesImport;

class ImportVehicleController extends Controller
{
    public function uploadVehicles(Request $request)
    {
        // Validate that a file is uploaded and it has the correct format
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:2048',
        ]);

        // Import data from the uploaded file
        Excel::import(new VehiclesImport, $request->file('file'));

        return response()->json(['message' => 'Vehicles uploaded and saved successfully'], 200);
    }
}
