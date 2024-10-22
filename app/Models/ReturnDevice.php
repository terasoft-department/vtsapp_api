<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReturnDevice extends Model
{
    use HasFactory;

    protected $primaryKey = 'return_id';

    // Allow mass assignment on the following attributes
    protected $fillable = [
        'user_id',
        'plate_number',
        'imei_number',
        'reason',
        'status',
        'customer_id',
    ];

    protected $table = 'return_devices';

    /**
     * Get the user that made the return.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id', 'customer_id');
    }

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class, 'vehicle_id', 'vehicle_id');
    }

    public function jobcard()
    {
        return $this->belongsTo(JobCard::class, 'jobcard_id', 'jobcard_id');
    }

    /**
     * Get the IMEI number based on plate number matching with jobcard
     */
    public function imeiNumber()
    {
        return $this->hasOne(JobCard::class, 'plate_number', 'plate_number')->select('imei_number');
    }
}
