<?php

namespace App\Repositories\v1\Rate;

use App\Model\Visit\EventReserves;
use App\Repositories\v1\Rate\RateInterface;
use App\Model\Visit\Rate;
use App\Traites\UsersTypeTraites;
use Exception;

class RateRepository implements RateInterface
{
    use UsersTypeTraites;

    /**
     * @param $data
     * @return array
     */
    public function store($data)
    {
        $event = EventReserves::where('token_room', $data['token'])->first();
        if (!$event)
            return [
                'status' => false,
                'message' => 'Wrong Token !'
            ];
        $data['type'] = $this->ConvertTypeNameToId($data['type']);
        $data['event_reserve_id'] = $event->id;
        try {
            $rate = Rate::create($data);
            if ($rate instanceof Rate)
                return [
                    'status' => true,
                    'data' => $rate
                ];
        } catch (Exception $ex) {
            return [
                'status' => false,
                'message' => $ex->getMessage()
            ];
        }
    }
}
