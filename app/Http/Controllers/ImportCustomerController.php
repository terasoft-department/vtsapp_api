<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\CustomersImport;

class ImportCustomerController extends Controller
{
    public function uploadCustomers(Request $request)
    {
        // Validate that a file is uploaded and it has the correct format
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:2048',
        ]);

        // Import data from the uploaded file
        Excel::import(new CustomersImport, $request->file('file'));

        return response()->json(['message' => 'Customers uploaded and saved successfully'], 200);
    }
}
