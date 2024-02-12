<?php

namespace App\Model\Doctor;

use App\User;
use Illuminate\Database\Eloquent\Model;

class DoctorInformation extends Model
{
    protected $guarded=['id'];

    public function doctor()
    {
        return $this->belongsTo(User::class,'doctor_id','id');
    }
}
