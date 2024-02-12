<?php


namespace App\Repositories\v2\Logs\UserActivity;


use App\Model\Logs\UserActivityLog;

class UserActivityLogRepository implements UserActivityLogInterface
{
    public function CreateLog($user, $action_type)
    {
        return UserActivityLog::create([
            'user_id'=>$user->id,
            'approve' =>$user->approve,
            'action_type'=>$action_type
        ]);
    }
}
