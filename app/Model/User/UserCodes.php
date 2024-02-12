<?php


namespace App\Model\User;


use Illuminate\Database\Eloquent\Model;

class UserCodes extends Model
{
    protected $fillable=['mobile','code','created_at','updated_at'];
}

