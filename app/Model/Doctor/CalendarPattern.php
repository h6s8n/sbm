<?php

namespace App\Model\Doctor;

use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CalendarPattern extends Model
{
    protected $guarded=['id'];

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(User::class,'user_id');
    }
}
