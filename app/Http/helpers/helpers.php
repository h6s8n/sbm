<?php

if (!function_exists('getOS')) {

    function getOS()
    {
        $user_agent = $_SERVER['HTTP_USER_AGENT'];

        $os_platform = "Unknown OS Platform";

        $os_array = array(
            '/windows nt 10/i' => 'Windows 10',
            '/windows nt 6.3/i' => 'Windows 8.1',
            '/windows nt 6.2/i' => 'Windows 8',
            '/windows nt 6.1/i' => 'Windows 7',
            '/windows nt 6.0/i' => 'Windows Vista',
            '/windows nt 5.2/i' => 'Windows Server 2003/XP x64',
            '/windows nt 5.1/i' => 'Windows XP',
            '/windows xp/i' => 'Windows XP',
            '/windows nt 5.0/i' => 'Windows 2000',
            '/windows me/i' => 'Windows ME',
            '/win98/i' => 'Windows 98',
            '/win95/i' => 'Windows 95',
            '/win16/i' => 'Windows 3.11',
            '/macintosh|mac os x/i' => 'Mac OS X',
            '/mac_powerpc/i' => 'Mac OS 9',
            '/linux/i' => 'Linux',
            '/ubuntu/i' => 'Ubuntu',
            '/iphone/i' => 'iPhone',
            '/ipod/i' => 'iPod',
            '/ipad/i' => 'iPad',
            '/android/i' => 'Android',
            '/blackberry/i' => 'BlackBerry',
            '/webos/i' => 'Mobile'
        );

        foreach ($os_array as $regex => $value)
            if (preg_match($regex, $user_agent))
                $os_platform = $value;

        return $os_platform;
    }

}

if (!function_exists('getBrowser')) {

    function getBrowser()
    {

        $user_agent = $_SERVER['HTTP_USER_AGENT'];

        $browser = "Unknown Browser";

        $browser_array = array(
            '/msie/i' => 'Internet Explorer',
            '/firefox/i' => 'Firefox',
            '/safari/i' => 'Safari',
            '/chrome/i' => 'Chrome',
            '/edge/i' => 'Edge',
            '/opera/i' => 'Opera',
            '/netscape/i' => 'Netscape',
            '/maxthon/i' => 'Maxthon',
            '/konqueror/i' => 'Konqueror',
            '/mobile/i' => 'Handheld Browser'
        );

        foreach ($browser_array as $regex => $value)
            if (preg_match($regex, $user_agent))
                $browser = $value;

        return $browser;
    }

}

if (!function_exists('getLocationInfoByIp')) {

    function getLocationInfoByIp()
    {
        $client = @$_SERVER['HTTP_CLIENT_IP'];
        $forward = @$_SERVER['HTTP_X_FORWARDED_FOR'];
        $remote = @$_SERVER['REMOTE_ADDR'];
        $result = array();
        if (filter_var($client, FILTER_VALIDATE_IP)) {
            $ip = $client;
        } elseif (filter_var($forward, FILTER_VALIDATE_IP)) {
            $ip = $forward;
        } else {
            $ip = $remote;
        }
        $ip_data = @json_decode(file_get_contents("http://www.geoplugin.net/json.gp?ip=" . $ip));
        if ($ip_data && $ip_data->geoplugin_countryName != null) {
            $result[] = strtolower($ip_data->geoplugin_countryCode);
            $result[] = $ip_data->geoplugin_city;
        }
        return join(', ', $result);
    }

}

if (!function_exists('getUserDiviceData')) {

    function getUserDiviceData()
    {
        $user_os = getOS();
        $user_browser = getBrowser();
        $user_location = getLocationInfoByIp();

        return 'Operating System: ' . $user_os . ', Browser: ' . $user_browser . ', Location: ' . $user_location;
    }

}

if (!function_exists('add_log_visit')) {

    function add_log_visit($user_id, $key = 'login', $status = 'success')
    {

        $requestNew = new \App\Model\Logs\LogVisit;
        $requestNew->user_id = $user_id;
        $requestNew->key = $key;
        $requestNew->status = $status;
        $requestNew->description = getUserDiviceData();
        $requestNew->save();

        return $requestNew->id;
    }

}

if (!function_exists('slugify')) {

    function slugify($text)
    {
        // replace non letter or digits by -
        $text = preg_replace('~[^\pL\d]+~u', '-', $text);

        // remove unwanted characters
        $text = preg_replace('~[^-\w]+~', '', $text);

        // trim
        $text = trim($text, '-');

        // remove duplicate -
        $text = preg_replace('~-+~', '-', $text);

        // lowercase
        $text = strtolower($text);

        if (empty($text)) {
            return 'n-a';
        }

        return $text;
    }

}

if (!function_exists('error_template')) {

    function error_template($message)
    {

        return response()->json([

            'message' => "The given data was invalid.",
            'errors' => [$message],
            'status' => 'error',
        ],422);

    }

}

if (!function_exists('success_template')) {

    function success_template($array)
    {
           
        return response()->json([
            'data' => $array,
            'status' => 'success',
        ]);

    }
}
if (!function_exists('change_number')) {

    function change_number($string)
    {

        $persian_num = array('۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹');
        $latin_num = range(0, 9);
        $string = trim(str_replace($persian_num, $latin_num, $string));

        return $string;

    }

}


if (!function_exists('change_phone')) {

    function change_phone($username)
    {


        $iran_zone = [
            '+98',
            '0098',
        ];
        $iranPhone = false;

        foreach ($iran_zone as $zone) {

            if (strpos($username, $zone) !== false) {
                $iranPhone = true;
            }

        }

        if (strlen($username) > 10) {

            if ($iranPhone) {

                $username = '0' . substr($username, -10);

            } else {


                if (strpos($username, '+') !== false) {
                    $username = '00' . substr($username, 1);
                }

            }

        } else {
            $username = '0' . substr($username, -10);
        }

        $username = preg_replace("/[^0-9]/", "", $username);

        return $username;


    }

}

if (!function_exists('specialties_array')) {

    function specialties_array()
    {


        $json = '{
          "زنان و زایمان": "زنان و زایمان",
          "پوست و مو": "پوست و مو",
          "جراحی پلاستیک و زیبایی": "جراحی پلاستیک و زیبایی",
          "روانشناسی": "روانشناسی",
          "گوارش و کبد": "گوارش و کبد",
          "طب اورژانس": "طب اورژانس",
          "اعصاب و روان": "اعصاب و روان",
          "روانپزشک": "روانپزشک",
          "تغذیه": "تغذیه",
          "غدد": "غدد",
          "رشد و متابولیسم": "رشد و متابولیسم",
          "خون و سرطان": "خون و سرطان",
          "کودکان": "کودکان",
          "گوش و حلق و بینی": "گوش و حلق و بینی",
          "چشم پزشکی": "چشم پزشکی",
          "ریه": "ریه",
          "بیماری  عفونی": "بیماری  عفونی",
          "کلینیک ریه": "کلینیک ریه",
          "جراح مغز و اعصاب": "جراح مغز و اعصاب",
          "فیزیوتراپیست": "فیزیوتراپیست",
          "رادیولوژی و سونوگرافی": "رادیولوژی و سونوگرافی",
          "ژنتیک": "ژنتیک",
          "بیهوشی": "بیهوشی",
          "پزشکی قانونی": "پزشکی قانونی",
          "بیماری های روماتیسمی و استخوان و مفاصل": "بیماری های روماتیسمی و استخوان و مفاصل",
          "داروسازی": "داروسازی",
          "طب کار و بیماری های شغلی": "طب کار و بیماری های شغلی",
          "طب مکمل": "طب مکمل",
          "پیراپزشکی": "پیراپزشکی",
          "پزشکی عمومی": "پزشکی عمومی",
          "آسیب شناسی": "آسیب شناسی",
          "پاتولوژی": "پاتولوژی",
          "پرتو درمانی": "پرتو درمانی",
          "رادیوتراپی": "رادیوتراپی",
          "پزشکی اجتماعی": "پزشکی اجتماعی",
          "پزشکی ورزشی": "پزشکی ورزشی",
          "پزشکی فیزیکی و توانبخشی": "پزشکی فیزیکی و توانبخشی",
          "جراحی استخوان و مفاصل": "جراحی استخوان و مفاصل",
          "ارتوپدی": "ارتوپدی",
          "بیهوشی و مراقبت های ویژه": "بیهوشی و مراقبت های ویژه",
          "کلیه و مجاری ادراری": "کلیه و مجاری ادراری",
          "ارولوژی": "ارولوژی",
          "بیماری داخلی": "بیماری داخلی",
          "نورولوژی": "نورولوژی",
          "مغز و اعصاب": "مغز و اعصاب",
          "قلب و عروق": "قلب و عروق",
          "جراحی": "جراحی",
          "دندان پزشکی": "دندان پزشکی",
          "بیماری های کلیه و فشار خون": "بیماری های کلیه و فشار خون",
          "نفرولوژی": "نفرولوژی",
          "کلینیک درد": "کلینیک درد",
          "جراحی روده ی بزرگ": "جراحی روده ی بزرگ",
          "کولورکتال": "کولورکتال",
          "آلرژی و ایمونولوژی": "آلرژی و ایمونولوژی",
          "جراح قلب و عروق": "جراح قلب و عروق"
        }';
        return json_decode($json);


    }

}

if (!function_exists('get_ev')) {

    function get_ev($str)
    {


        $json = [
            'cp_live' => 'https://cp.sbm24.com',
            'statics_server' => 'https://sandbox.sbm24.net/statics-public',
            'path_root' => 'httpdocs/statics-public',
            'path_live' => 'application',
            'zarin_key' => 'e7de51e6-eb96-11e6-a17f-000c295eb8fc'
        ];
        return $json[$str];


    }


    if (!function_exists('TestAccount')) {

        function TestAccount()
        {

            return [
                146,
                806,
                933,
                1071,
                4101,
                6349,
                7805,
                7949,
                10694,
                3334,
                321,
                10910,
            ];


        }

    }
    if (!function_exists('DayOfWeek')) {

        function DayOfWeek($id)
        {
//dd($id);
            $days = [
                0 => "یکشنبه",
                1 => "دوشنبه",
                2 => "سه شنبه",
                3 => "چهارشنبه",
                4 => "پنج شنبه",
                5 => "جمعه",
                6 => "شنبه"
            ];
            return $days[$id];
        }

    }
    if (!function_exists('StandardNumber')) {

        function StandardNumber($number)
        {
            $number = change_number($number);
            $number = substr($number, 1);
            $number = substr_replace($number, '98', 0, 0);
            return $number;
        }

    }
    if (!function_exists('OurBeneficiary')) {

        function OurBeneficiary($type = null)
        {
            $cost = 338000;
            if ($type) {
                switch ($type) {
                    case 4:
                    {
                        $cost = 78000;
                        break;
                    }
                    case 5:
                    {
                        $cost = 0;
                        break;
                    }
                }
            } else $cost = 338000;
            return $cost;
        }
    }
    if (!function_exists('dateChangedBeneficiary')) {
        function dateChangedBeneficiary()
        {
//            return '2021-04-01';
//            return '2022-01-16';
//            return '2022-05-08';
            return '2023-03-04';
        }
    }
}