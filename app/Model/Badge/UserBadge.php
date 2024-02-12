<?php

namespace App\Model\Badge;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserBadge extends Model
{
    protected $guarded=['id'];
    protected $appends=['flag'];

    public function badge(): BelongsTo
    {
        return $this->belongsTo(Badge::class);
    }

    public function getFlagAttribute(): bool
    {
        if ($this->activation_time >= Carbon::now()->format('Y-m-d') &&
            Carbon::now()->format('Y-m-d') <= $this->expiration_time )
            return true;
        return  false;
    }
}
