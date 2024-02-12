<?php

namespace App\Model\Visit;

use App\User;
use Illuminate\Database\Eloquent\Model;

class TransactionCredit extends Model
{
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
