<?php

namespace App\Http\Controllers\Api\v2\Visit;

use App\Enums\VisitActionsEnum;
use App\Model\Visit\EventReserves;
use App\Model\Visit\VisitAction;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class VisitActionController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'action'=>'required'
        ]);
        $user = auth()->user();

        $event = EventReserves::where('token_room',$request->token)->first();
        if (!$event || ($event->doctor_id !== $user->id && $event->user_id !== $user->id))
            return error_template('شما به این ویزیت دسترسی ندارید');
        if (VisitAction::where('event_id',$event->id)->first())
            return error_template('شما قبلا درخواست خود را ثبت کرده اید');
        $now = Carbon::now();
        $date = Carbon::create($event->reserve_time);
        if ($now <= $date->addHours(1)) {
            return error_template('لطفا 1 ساعت پس از نوبت ویزیت درخواست خود را ثبت نمایید');
        }
        $data['last_changed_user_id'] = $user->id;
        $data['user_id'] = $user->id;
        $data['event_id'] = $event->id;
        $data['action'] = VisitActionsEnum::returnCode($request->action);
        VisitAction::create($data);
        $event->visit_status = $request->action;
        $event->save();
        return success_template(['message'=>'درخواست شما با موفقیت ثبت شد.']);
    }
}
