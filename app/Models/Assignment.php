<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Assignment extends Model
{
    use HasFactory;

    // Set the primary key
    protected $primaryKey = 'assignment_id';

    // Allow mass assignment on the following attributes
    protected $fillable = [
        'customer_id',
        'plate_number',
        'customer_phone',
        'location',
        'imei_number',
        'user_id', // Link to users table using 'user_id'
        'report_id',
        'status',
        'assigned_by',
        'case_reported',
        'customer_debt'
    ];

    // Specify the table name if it's different from the plural of the model name
    protected $table = 'assignments';

    /**
     * Get the customer that owns the assignment.
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id', 'customer_id');
    }

    /**
     * Get the user that owns the assignment.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id'); // Link 'user_id' to 'id'
    }

    public function vehicle()
{
    return $this->belongsTo(Vehicle::class, 'plate_number', 'plate_number');
}

}
