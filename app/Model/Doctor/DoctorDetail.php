<?php

namespace App\Model\Doctor;

use App\User;
use Illuminate\Database\Eloquent\Model;

class DoctorDetail extends Model
{
    protected $fillable=['description','title','content','user_id','video_url'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
