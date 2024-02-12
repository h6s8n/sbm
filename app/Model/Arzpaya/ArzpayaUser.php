<?php

namespace App\Model\Arzpaya;

use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ArzpayaUser extends Model
{
    protected $guarded=['id'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class,'internal_user_id');
    }
}
