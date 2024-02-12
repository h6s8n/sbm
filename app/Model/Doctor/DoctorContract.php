<?php
namespace App\Model\Doctor;

use App\User;
use Illuminate\Database\Eloquent\Model;

class DoctorContract extends Model
{

    protected $fillable = [
        'body',
        'percent',
        'start_at',
        'expire_at',
        'status',
        'type',
        'user_id',
    ];

    public function doctor()
    {
        return $this->belongsTo(User::class,'user_id');
    }
}
