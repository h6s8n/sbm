<?php


namespace App\Enums;


abstract  class VisitTypeEnum
{
    public static function name($id)
    {
        $data=[
            1=>'عادی',
            2=>'فوری',
            3=>'آفلاین',
            4=>'تفسیرآزمایش',
            5=>'حضوری',
            6=>'مشاوره عمل جراحی',
            7=>'نوبت عمل',
        ];
        return $data[$id];
    }

    public static function type($name)
    {
        $data=[
            'normal'=>1,
            'immediate'=>2,
            'offline'=>3,
            'interpretation'=>4,
            'in-person'=>5,
            'surgical-advice'=>6,
            'surgery'=>7,
        ];
        return $data[$name];
    }
}
