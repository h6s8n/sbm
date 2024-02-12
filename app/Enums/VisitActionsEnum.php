<?php


namespace App\Enums;


abstract class VisitActionsEnum
{
    public const refundRequest =1;
    public const newTimeRequest =2;
    public const newDoctorRequest =3;

    public static function returnCode($data): int
    {
        $array =[
            'refundRequest'=>1,
            'newTimeRequest'=>2,
            'newDoctorRequest'=>3
        ];
        return $array[$data];
    }
    public static function returnMessage($data): string
    {
        $array =[
            1=>'بازگشت به حساب کاربری',
            2=>'وقت جدید از همین پزشک',
            3=>'وقت جدید از پزشک جدید'
        ];
        return $array[$data];
    }

}
