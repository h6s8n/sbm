<?php

namespace App\Model\User;

use Illuminate\Database\Eloquent\Model;

class UserDictionary extends Model
{
    protected $table = 'user_dictionaries';
    public $timestamps=false;
    protected $guarded=['id'];
}
