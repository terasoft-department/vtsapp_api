<?php

namespace App\Imports;

use App\Models\Device;
use Maatwebsite\Excel\Concerns\ToModel;

class DevicesImport implements ToModel
{
    public function model(array $row)
    {
        return new Device([
            'imei_number' => $row[0],   // Column index in the Excel file for `imei_number`
            'category'    => $row[1],   // Column index in the Excel file for `category`
        ]);
    }
}
