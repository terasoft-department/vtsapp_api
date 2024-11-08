<?php

namespace App\Imports;

use App\Models\Vehicle; // Make sure this points to your Vehicle model
use Maatwebsite\Excel\Concerns\ToModel;

class VehiclesImport implements ToModel
{
    public function model(array $row)
    {
        return new Vehicle([
            'vehicle_name' => $row[0],
            'category' => $row[1],
            'plate_number' => $row[2],
        ]);
    }
}
