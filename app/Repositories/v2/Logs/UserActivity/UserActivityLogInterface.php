<?php


namespace App\Repositories\v2\Logs\UserActivity;


use App\User;

interface UserActivityLogInterface
{
    public function CreateLog(User $user,$action_type);
}
