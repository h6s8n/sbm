<?php

namespace App\Enums;


abstract class LanguageEnum
{
    const Farsi = 1;
    const English = 2;
    const Spanish = 3;

    public static function getIdBySlug($slug): int
    {
        $array =[
            'fa'=>self::Farsi,
            'en'=>self::English,
            'es'=>self::Spanish
        ];
        return $array[$slug];
    }
}
