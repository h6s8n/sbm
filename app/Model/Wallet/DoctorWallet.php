<?php

namespace App\Model\Wallet;

use App\User;
use Illuminate\Database\Eloquent\Model;

class DoctorWallet extends Model
{
//    protected $casts = [
//        'account_id_info' => 'array',
//    ];
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function doctor()
    {
        return $this->belongsTo(User::class,'doctor_id');
    }
}
