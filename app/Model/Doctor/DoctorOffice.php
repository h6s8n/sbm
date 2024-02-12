<?php
namespace App\Model\Doctor;

use App\Model\Platform\City;
use App\Model\Platform\State;
use App\User;
use Illuminate\Database\Eloquent\Model;

class DoctorOffice extends Model
{

    protected $fillable = [
        'address',
        'title',
        'phones',
        'description',
        'latitude',
        'longitude',
        'state_id',
        'city_id',
        'secretaries',
        'doctor_id',
    ];
    public function doctor()
    {
        return $this->belongsTo(User::class,'doctor_id');
    }

    public function city()
    {
        return $this->belongsTo(City::class);
    }

    public function state()
    {
        return $this->belongsTo(State::class);
    }
}
