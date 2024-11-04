<?php

namespace App\Imports;

use App\Models\Customer; // Make sure this points to your Customer model
use Maatwebsite\Excel\Concerns\ToModel;

class CustomersImport implements ToModel
{
    public function model(array $row)
    {
        return new Customer([
            'customername' => $row[0],  // Adjust according to the column order in your Excel file
            'address' => $row[1],
            'customer_phone' => $row[2],
            'TinNumber' => $row[3],
            'email' => $row[4],
        ]);
    }
}
