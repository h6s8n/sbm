<?php

namespace App\Model\Visit;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VisitAction extends Model
{
    protected $guarded=['id'];

    public function event(): BelongsTo
    {
        return $this->belongsTo(EventReserves::class,'event_id','id');
    }
}
