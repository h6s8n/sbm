<?php

namespace App\Model\Wallet;

use App\User;
use Illuminate\Database\Eloquent\Model;

class DoctorWalletTransaction extends Model
{
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function wallet()
    {
        return $this->belongsTo(DoctorWallet::class,'doctor_wallet_id');
    }
}
