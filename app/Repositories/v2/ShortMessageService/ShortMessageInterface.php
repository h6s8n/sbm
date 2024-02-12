<?php


namespace App\Repositories\v2\ShortMessageService;


interface ShortMessageInterface
{
    public function sendConfirmationCode($mobile,$code);

    public function SendSmsWithOneToken($mobile,$token,$template);
}
