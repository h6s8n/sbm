<?php

namespace App\Model\Visit;

use App\User;
use Illuminate\Database\Eloquent\Model;

class TransactionReserve extends Model
{
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function doctor()
    {
        return $this->belongsTo(User::class,'doctor_id');
    }
}
