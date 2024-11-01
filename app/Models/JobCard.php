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
        'customer_id',
        'contact_person',
        'mobile_number',
        'title',
        'physical_location',
        'plate_number',
        'problem_reported',
        'natureOf_ProblemAt_site',
        'service_type',
        'date_attended',
        'work_done',
        'imei_number',
        'client_comment',
        'user_id',
        'pre_workdone_picture',
        'post_workdone_picture',
        'carPlateNumber_picture',
        'tampering_evidence_picture',
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
