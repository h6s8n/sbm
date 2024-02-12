<?php

namespace App\Model\Transaction;

use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AffiliateTransaction extends Model
{
    protected $guarded=['id'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class,'user_id');
    }
    public function doctor(): BelongsTo
    {
        return $this->belongsTo(User::class,'doctor_id');
    }
    public function affiliate(): BelongsTo
    {
        return $this->belongsTo(User::class,'affiliate_id');
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(User::class,'event_id');

    }
}
