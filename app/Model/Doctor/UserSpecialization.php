<?php

namespace App\Model\Doctor;

use Illuminate\Database\Eloquent\Model;

class UserSpecialization extends Model
{
    public $timestamps=false;
    protected $primaryKey=['user_id','specialization_id'];
    protected $fillable=['user_id','specialization_id'];
}
