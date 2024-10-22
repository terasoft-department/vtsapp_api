<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    use HasFactory;

    // Set the primary key
    protected $primaryKey = 'role_id';

    // Allow mass assignment on the following attributes
    protected $fillable = [
        'category',
        'description',
    ];

    // Specify the table name if it's different from the plural of the model name
    protected $table = 'roles';
}
