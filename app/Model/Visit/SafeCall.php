<?php

namespace App\Model\Visit;

use Illuminate\Database\Eloquent\Model;

class SafeCall extends Model
{
    protected $guarded=['id'];

    public function event()
    {
        return $this->belongsTo(EventReserves::class);
    }
}
