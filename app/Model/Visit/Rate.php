<?php

namespace App\Model\Visit;

use Illuminate\Database\Eloquent\Model;

class Rate extends Model
{

    protected $fillable = ['rate', 'event_reserve_id', 'comment', 'type'];
    public function event()
    {
        return $this->belongsTo(EventReserves::class, 'event_reserve_id');
    }
}
