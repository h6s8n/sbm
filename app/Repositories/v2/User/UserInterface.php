<?php


namespace App\Repositories\v2\User;


interface UserInterface
{
    public function store($data);

    public function findByMobile($mobile);

    public function findByEmail($email);

    public function NotifyMeNewTime($data);

    public function HasSetTimeNotification($doctor_id);
}
