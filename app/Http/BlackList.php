<?php


namespace App\Http;


class BlackList
{
    public static function black($ip)
    {
        $data= array(
            1=>'153.92.0.19',
            2=>'153.92.0.11',
            3=>'195.248.240.111',
            4=>'185.141.133.158',
            5=>'185.27.134.195',
            6=>'88.198.51.176',
            7=>'153.92.0.16',
            8=>'80.249.113.41',
            9=>'88.99.104.17',
            10=>'46.4.174.204',
            11=>'45.155.194.191',
            12=>'96.30.193.216',
            13=>'94.130.34.82',
            14=>'153.92.0.23',
            15=>'52.237.96.235',
            16=>'185.27.134.190',
            17=>'153.92.0.22',
            18=>'82.102.22.9',
            19=>'149.202.97.218',
            20=>'153.92.0.22',
            21=>'185.105.237.98'
        );
        if (array_search($ip,$data))
            return true;
        return false;
    }
}
