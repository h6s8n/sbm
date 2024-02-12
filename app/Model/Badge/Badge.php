<?php

namespace App\Model\Badge;

use App\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Badge extends Model
{
    protected $guarded=['id'];

    public function users()
    {
        return $this->belongsToMany(User::class,'user_badges','badge_id','user_id');
    }

    public function getFlagAttribute(): bool
    {
        if (Carbon::now()->format('Y-m-d') <= $this->pivot->expiration_time )
            return true;

        return  false;
    }
}
