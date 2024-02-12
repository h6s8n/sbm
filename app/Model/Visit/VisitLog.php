<?php

namespace App\Model\Visit;

use App\User;
use Illuminate\Database\Eloquent\Model;

class VisitLog extends Model
{
    protected $guarded=['id'];
    protected $fillable=['user_id','event_id','action_type'];

    public function visit()
    {
        return $this->belongsTo(EventReserves::class,'event_id');
    }
    public function user()
    {
        return $this->belongsTo(User::class,'user_id');
    }
}
