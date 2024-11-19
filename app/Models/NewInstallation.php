<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NewInstallation extends Model
{
    use HasFactory;

     protected $fillable = [
        'customerName',
        'plateNumber',
        'DeviceNumber',
        'CarRegNumber',
        'customerPhone',
        'simCardNumber',
        'picha_ya_gari_kwa_mbele',
        'picha_ya_device_anayoifunga',
        'picha_ya_hiyo_karatasi_ya_simCardNumber',
        'user_id',
    ];

     public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
