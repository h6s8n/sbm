<?php


namespace App\Repositories\v2\Visit;


use App\Model\Visit\VisitLog;

class VisitLogRepository implements VisitLogInterface
{
    public function createLog($event, $user_id, $action)
    {
        return $log = VisitLog::create([
            'event_id'=>$event->id,
            'user_id'=>$user_id,
            'action_type'=>$action
        ]);
    }
    public function find($event_id,$user_id,$type)
    {
        return VisitLog::where('event_id',$event_id)
            ->where('user_id',$user_id)
            ->where('action_type',$type)
            ->first();
    }
}
