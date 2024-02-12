<?php


namespace App\Repositories\v2\Visit;


use App\Model\Visit\VisitLog;

class VisitLogRepository implements VisitLogInterface
{
    public function createLog($event, $user_id, $action)
    {
      
        return $log = VisitLog::create([
            'action_type'=>$action,
            'event_id'=>$event->id,
            'user_id'=>$user_id,
        ]);
    }
}
