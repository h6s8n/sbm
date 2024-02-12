<?php

namespace App\Model\Visit;

use App\Events\SMS\SetTimeNotificationEvent;
use App\Model\Partners\Partner;
use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;

class DoctorCalender extends Model
{
    protected $guarded = ['id'];

    public function visits(): HasMany
    {
        return $this->hasMany(EventReserves::class, 'calender_id');
    }

    public function partner(): BelongsTo
    {
        return $this->belongsTo(Partner::class);
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
