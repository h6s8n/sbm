<?php


namespace App\Repositories\v2\Visit;


use App\Model\Visit\EventReserves;

interface VisitLogInterface
{
    public function createLog(EventReserves  $event,$user_id,$action);
}
