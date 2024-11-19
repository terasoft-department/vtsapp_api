<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JobCard extends Model
{
    use HasFactory;

    protected $table = 'job_cards';

    protected $primaryKey = 'jobcard_id';

    protected $fillable = [
      'Clientname',
      'Tel',
       'ContactPerson',
        'title',
        'mobilePhone',
        'VehicleRegNo',
          'physicalLocation',
        'deviceID',
       'problemReported',
       'DateReported',
      'DateAttended',
       'natureOfProblem',
       'workDone',
       'clientComment',
       'service_type',
       'user_id',
    ];


    /**
     * Get the user that created the job card.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    /**
     * Get the customer associated with the job card.
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id', 'customer_id');
    }

    /**
     * Get the device associated with the job card.
     */
    public function device()
    {
        return $this->belongsTo(Device::class, 'device_id', 'device_id');
    }

    public function jobcard(){
        return $this->belongsTo(JobCard::class,'jobcard_id','jobcard_id');
    }

    public function assignment(){
        return $this->belongsTo(Assignment::class,'assignment_id','assignment_id');
    }
}
