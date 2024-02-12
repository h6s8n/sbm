<?php


namespace App\Repositories\v2\Calendar;


interface CalendarInterface
{
    public function update($data);

    public function getOnlineDoctors();
}
