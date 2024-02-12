<?php

namespace App\Model\Visit;

use App\Enums\VisitLogEnum;
use App\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use phpDocumentor\Reflection\Types\This;
use test\Mockery\MockingParameterAndReturnTypesTest;

class EventReserves extends Model
{

    public function doctor()
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function UserTransaction($status = null)
    {
        $transactions = $this->hasOne(TransactionReserve::class,
            'calender_id', 'calender_id');
        if ($status)
            return $transactions->where('status', $status);
        return $transactions;
    }

    public function DoctorTransaction($status = null)
    {
        $transactions = $this->hasOne(TransactionDoctor::class,'event_id');
//            ->where('event_id', $this->id)
//            ->where('doctor_id', $this->doctor_id)
//            ->where('user_id', $this->user_id);
        if ($status)
            return $transactions->where('status', $status);
        return $transactions;
    }

    public function DoctorMessages()
    {
        return Message::where('room_token', $this->token_room)
            ->where('user_id', $this->doctor_id);
    }

    public function UserMessages()
    {
        return Message::where('room_token', $this->token_room)
            ->where('user_id', $this->user_id);
    }

    public function UserRate()
    {
        return $this->hasOne(Rate::class, 'event_reserve_id')
            ->where('type', 2);
    }

    public function DoctorRate()
    {
        return $this->hasOne(Rate::class, 'event_reserve_id')
            ->where('type', 1);
    }

    public function MedicalRecords()
    {
        return $this->hasMany(Dossiers::class, 'event_id')
            ->where('user_id', $this->user_id)
            ->where('audience_id', $this->doctor_id);
    }

    public function MedicalInstructions()
    {
        return $this->hasMany(Dossiers::class, 'event_id')
            ->where('user_id', $this->doctor_id)
            ->where('audience_id', $this->user_id);
    }

    public function getFilterAttribute()
    {
        $date = Carbon::parse($this->reserve_time)->format('Y-m-d');
        $now = Carbon::now()->format('Y-m-d');
        if ($this->visit_status != 'end') {
            if ($date == $now)
                return 1;
            elseif ($date > $now)
                return 2;
            elseif ($date < $now)
                return 3;
        }
        return 4;
    }

    public function calendar()
    {
        return $this->belongsTo(DoctorCalender::class, 'calender_id');
    }

    public function messages()
    {
        return $this->hasMany(Message::class,'room_token','token_room');
    }

    public function logs($action = null)
    {
        $data =  $this->hasMany(VisitLog::class, 'event_id');
        if ($action)
            return $data->where('action_type',$action);
        return $data;
    }

    public function lastJoin($mode = 1)
    {
        $data = $this->logs()->select('created_at');
        if ($mode == 1)
            $data = $data->where('action_type',
                VisitLogEnum::DoctorEnter);
        else
            $data = $data->where('action_type',
                VisitLogEnum::PatientEnter);

        return $data->orderBy('created_at', 'DESC');
    }

    public function getVisitTypeString()
    {
        $calendar = $this->calendar()->first();

        switch (optional($calendar)->type){
            case null:
            case "":
            case 1:{
                return 'ویزیت معمولی';
                break;
            }
            case 2:{
                return 'ویزیت فوری';
                break;
            }
            case 3:{
                return 'ویزیت آفلاین';
                break;
            }
            case 4:
            {
                return 'تفسیر آزمایش';
                break;
            }
            case 5:{
                    return 'حضوری';
                    break;
            }
            default :{
                return 'ویزیت معمولی';
            }
        }
    }
}
