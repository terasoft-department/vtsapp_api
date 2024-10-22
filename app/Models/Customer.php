<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;

    // Set the primary key
    protected $primaryKey = 'customer_id';

    // Allow mass assignment on the following attributes
    protected $fillable = [
        'customername',
        'address',
        'customer_phone',
        'TinNumber',
        'email',
        'start_date',
    ];

    // Specify the table name if it's different from the plural of the model name
    protected $table = 'customers';


}
