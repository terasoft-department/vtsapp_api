<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

      protected $fillable = [
        'customername',
        'status',
        'pay_id'
    ];

      public function customer()
    {
        return $this->belongsTo(Customer::class, 'customername', 'customername');
    }
}
