<?php


namespace App\Enums;


abstract class UserActivityLogEnum
{
    const UserLogin = 1;
    const UserLogout = 2;
    const LoadFirstPage = 3;
    const GoToPulsyno = 4;
}
