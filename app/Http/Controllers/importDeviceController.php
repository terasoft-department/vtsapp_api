<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\DevicesImport;

class importDeviceController extends Controller
{
    public function uploadDevices(Request $request)
    {
        // Validate that a file is uploaded and it has the correct format
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:2048',
        ]);

        // Import data from the uploaded file
        Excel::import(new DevicesImport, $request->file('file'));

        return response()->json(['message' => 'Devices uploaded and saved successfully'], 200);
    }
}
