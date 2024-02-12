<?php

namespace App\Model\Notification;

use App\User;
use Illuminate\Database\Eloquent\Model;

class UserDoctorNotification extends Model
{
    protected $guarded=['id'];

    public function user()
    {
        return $this->belongsTo(User::class,'user_id');
    }
    public function doctor()
    {
        return $this->belongsTo(User::class,'doctor_id');
    }
}
