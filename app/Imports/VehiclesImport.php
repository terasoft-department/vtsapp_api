<?php

namespace App\Imports;

use App\Models\Vehicle; // Make sure this points to your Vehicle model
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class VehiclesImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        return new Vehicle([
            'vehicle_name' => $row['vehicle_name'], // Use the header names
            'category'     => $row['category'],     // Use the header names
            'plate_number' => $row['plate_number'], // Use the header names
        ]);
    }
}
