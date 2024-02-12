<?php

namespace App\Model\Partners;

use App\User;
use Illuminate\Database\Eloquent\Model;

class Partner extends Model
{
    protected $guarded=['id'];

    public function insurances()
    {
        return $this->hasManyThrough(Insurance::class,
            PartnerInsurance::class,'partner_id',
            'id','id',
            'insurance_id');
    }
    public function services()
    {
        return $this->hasManyThrough(Service::class,
            PartnerService::class,'partner_id',
            'id','id','service_id');
    }

    public function doctors()
    {
        return $this->hasManyThrough(User::class,
        PartnerDoctor::class,
        'partner_id','id','id','user_id');
    }
}
