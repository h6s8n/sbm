<?php

namespace App\Model\Arzpaya;

use App\Model\Visit\DoctorCalender;
use App\Model\Visit\EventReserves;
use App\Model\Visit\TransactionReserve;
use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ArzpayaTransaction extends Model
{
    protected $guarded=['id'];

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(TransactionReserve::class,'transaction_reserve_id');
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(User::class,'doctor_id');
    }
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class,'user_id');
    }
    public function event(): BelongsTo
    {
        return $this->belongsTo(EventReserves::class,'event_id');
    }

    public function calendar(): BelongsTo
    {
        return $this->belongsTo(DoctorCalender::class,'calendar_id');
    }
}
