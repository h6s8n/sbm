<?php

namespace App\Model\Partners;

use Illuminate\Database\Eloquent\Model;

class RegistrationRequest extends Model
{
    protected $table = 'partner_registration_requests';
    protected $guarded=['id'];

    public function getCreatedAtAttribute($value)
    {
      return \Hekmatinasser\Verta\Verta::instance($value)->format('Y-m-d , H:i:s');
    }
}
