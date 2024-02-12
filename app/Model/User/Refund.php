<?php

namespace App\Model\User;

use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Refund extends Model
{
    protected $guarded=['id'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function last_changed_user(): BelongsTo
    {
        return $this->belongsTo(User::class,'last_changed_user_id');

    }
}
