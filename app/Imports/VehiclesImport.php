<?php

namespace App\Imports;

use App\Models\Vehicle; // Make sure this points to your Vehicle model
use Maatwebsite\Excel\Concerns\ToModel;

class VehiclesImport implements ToModel
{
    public function model(array $row)
    {
        return new Vehicle([
            'plate_number' => $row[0],
            'vehicle_name' => $row[1],
            'category' => $row[2],
        ]);
    }
}
