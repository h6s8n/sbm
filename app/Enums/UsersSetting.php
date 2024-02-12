<?php

namespace App\Enums;


abstract class UsersSetting
{
    public const newVisitSmsAlert = 1;
    public const newVisitEmailAlert = 2;
    public const waitingListSmsAlert = 3;
    public const waitingListEmailAlert = 4;
    public const cancelVisitEmailAlert = 10;
    public const cancelVisitSmsAlert = 11;

    public const limitTodayVisit = 5;

    public const onlyMyPatient = 6;
    public const videoCallVisit = 7;
    public const phoneCallVisit = 8;
    public const textChatVisit = 9;

    public const persianLang = 12;
    public const turkishLang = 13;
    public const englishLang = 14;
    public const arabicLang = 15;
    public const spanishLang = 16;
    public const localTurkish = 17;
    public const localKurdish = 18;
    public const localLori = 19;
    public const localGilaki = 20;

    public static function toArray(): array
    {
        return [
            1 => 'ارسال اس ام اس برای ویزیت جدید',
            2 => 'ارسال ایمیل برای ویزیت جدید',
            3 => 'ارسال اس ام اس لیست انتظار',
            4 => 'ارسال ایمیل لیست انتظار',
            5 => 'قفل وقت های روز جاری',
            6 => 'فقط بیماران خودم',
            7 => 'چت تصویری',
            8 => 'چت صوتی',
            9 => 'چت متنی',
            10 => 'ارسال ایمیل کنسلی',
            11 => 'ارسال اس ام اس کنسلی',
            12 => 'زبان فارسی',
            13 => 'زبان ترکی',
            14 => 'زبان انگلیسی',
            15 => 'زبان عربی',
            16 => 'زبان اسپانیایی',
            17 => 'زبان ترکی محلی',
            18 => 'زبان کردی',
            19 => 'زبان لری',
            20 => 'زبان گیلکی'
        ];
    }

    public static function toValue($item): string
    {
        $data = [
            'newVisitSmsAlert' => 1,
            'newVisitEmailAlert' => 2,
            'waitingListSmsAlert' => 3,
            'waitingListEmailAlert' => 4,
            'cancelVisitEmailAlert' => 10,
            'cancelVisitSmsAlert' => 11,
            'limitTodayVisit' => 5,
            'onlyMyPatient' => 6,
            'videoCallVisit' => 7,
            'phoneCallVisit' => 8,
            'textChatVisit' => 9,
            'persianLang' => 12,
            'turkishLang' => 13,
            'englishLang' => 14,
            'arabicLang' => 15,
            'spanishLang' => 16,
            'localTurkish' => 17,
            'localKurdish' => 18,
            'localLori' => 19,
            'localGilaki' => 20
        ];
        return $data[$item];
    }

    public static function toKey($item) : int
    {
        $data = [
            1=> 'newVisitSmsAlert',
            2=>'newVisitEmailAlert',
            3=>'waitingListSmsAlert',
            4=>'waitingListEmailAlert',
            10=>'cancelVisitEmailAlert',
            11=>'cancelVisitSmsAlert',
            5=>'limitTodayVisit' ,
            6=>'onlyMyPatient' ,
            7=>'videoCallVisit' ,
            8=>'phoneCallVisit' ,
            9=>'textChatVisit' ,
            12=>'persianLang' ,
            13=>'turkishLang' ,
            14=>'englishLang' ,
            15=>'arabicLang' ,
            16=>'spanishLang' ,
            17=>'localTurkish' ,
            18=>'localKurdish' ,
            19=>'localLori' ,
            20=>'localGilaki'
        ];
        return $data[$item];
    }
}
