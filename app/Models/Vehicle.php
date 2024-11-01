<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vehicle extends Model
{
    use HasFactory;

    // Set the primary key
    protected $primaryKey = 'vehicle_id';

    // Allow mass assignment on the following attributes
    protected $fillable = [
        'vehicle_name',
        'customer_id',
        'category',
        'plate_number',
    ];

    protected $table = 'vehicles';

    /**
     * Get the customer that owns the vehicle.
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id', 'customer_id');
    }

     public function assignment(){
        return $this->belongsTo(Assignment::class, 'assignment_id', 'assignment_id');
     }
}
