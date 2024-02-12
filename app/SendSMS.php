<?php namespace App;


use Ixudra\Curl\Facades\Curl;
use soapclient;

class SendSMS
{

    public static function sendConfirmTemplate($to, $token1, $template = "confirmsbm", $token2 = null, $token3 = null)
    {


        $getPr = "&token=" . $token1;
        if ($token2) $getPr .= "&token2=" . $token2;
        if ($token3) $getPr .= "&token3=" . $token3;

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://api.kavenegar.com/v1/463878334E357372564E5A3244796257356A7675375879443879756468726C6F/verify/lookup.json?receptor=" . $to . $getPr . "&template=" . $template,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HEADER => 0,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_POSTFIELDS => "------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"_toke\"\r\n\r\nOCcmJZfUnGPqvH6voW4tLnGIFunTScqZ8zcoLCQc\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"ipa\"\r\n\r\n\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"change_log\"\r\n\r\n\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW--",
            CURLOPT_HTTPHEADER => array(
                "accept: application/json",
                "cache-control: no-cache",
                "content-type: multipart/form-data; boundary=----WebKitFormBoundary7MA4YWxkTrZu0gW",
                "postman-token: e32cedc2-5edc-1daf-e636-4c00fa8078b0"
            ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            return "Error #:" . $err;
        } else {

            return $response;
        }

    }

    public static function sendTemplateTwo($to, $token_1, $token_2, $template)
    {
        $token_1 = str_replace(' ', "‌", $token_1);
        $token_2 = str_replace(' ', "‌", $token_2);

        $useragent = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_8_2) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.89 Safari/537.36';
        $timeout = 120;

        $ch = curl_init("https://api.kavenegar.com/v1/463878334E357372564E5A3244796257356A7675375879443879756468726C6F/verify/lookup.json?receptor=" . $to . "&token=" . $token_1 . "&token2=" . $token_2 . "&template=" . $template);
        curl_setopt($ch, CURLOPT_FAILONERROR, true);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_ENCODING, "");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_AUTOREFERER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
        curl_setopt($ch, CURLOPT_USERAGENT, $useragent);
        curl_setopt($ch, CURLOPT_REFERER, 'http://www.google.com/');
        $response = curl_exec($ch);
        $err = curl_error($ch);

        curl_close($ch);

        if ($err) {
            return "Error #:" . $err;
        } else {
            return $response;
        }


    }

    public static function sendTemplateTree($to, $token_1, $token_2, $token_3, $template)
    {
        $url = "https://api.kavenegar.com/v1/463878334E357372564E5A3244796257356A7675375879443879756468726C6F/verify/lookup.json";
        $headers = array(
            "Content-Type: application/json",
        );
        return Curl::to($url)
            ->withHeaders($headers)
            ->withData([
            'receptor' => $to,
            "token" => $token_2,
            "token2" => $token_3,
            "token10" => $token_1,
            "template" => $template
        ])->get();

        return $response;
//
//        $ch = curl_init("https://api.kavenegar.com/v1/463878334E357372564E5A3244796257356A7675375879443879756468726C6F/verify/lookup.json?receptor=" . $to . "&token10=" . $token_1 . "&token=" . $token_2 . "&token2=" . $token_3 . "&template=" . $template);
//        curl_setopt($ch, CURLOPT_FAILONERROR, true);
//        curl_setopt($ch, CURLOPT_HEADER, 0);
//        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
//        curl_setopt($ch, CURLOPT_ENCODING, "");
//        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
//        curl_setopt($ch, CURLOPT_AUTOREFERER, true);
//        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
//        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
//        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
//        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
//        curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
//
//        $response = curl_exec($ch);
//        $err = curl_error($ch);
//        curl_close($ch);
//
//        if ($err) {
//            return "Error #:" . $err;
//        } else {
//            return $response;
//        }

    }

    public static function sendTemplateFive($to, $token_1, $token_2, $token_3, $token_10, $token_20, $template)
    {

        $url = "https://api.kavenegar.com/v1/463878334E357372564E5A3244796257356A7675375879443879756468726C6F/verify/lookup.json";
        $headers = array(
            "Content-Type: application/json",
        );

        return Curl::to($url)->withData([
            'receptor' => $to,
            'token' => $token_1,
            "token2" => $token_10,
            "token3" => $token_3,
            "token10" => $token_2,
            "token20" => $token_20,
            "template" => $template
        ])->get();

        return $response;
    }

    public static function send($to,$template,$params = [])
    {
        $getPr = '&';
        foreach ($params as $key => $value){
            $value = str_replace(' ', "‌", $value);
            $getPr .= $key.'='.$value.'&';
        }
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://api.kavenegar.com/v1/463878334E357372564E5A3244796257356A7675375879443879756468726C6F/verify/lookup.json?receptor=" . $to . $getPr . "template=" . $template,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HEADER => 0,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_POSTFIELDS => "------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"_toke\"\r\n\r\nOCcmJZfUnGPqvH6voW4tLnGIFunTScqZ8zcoLCQc\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"ipa\"\r\n\r\n\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"change_log\"\r\n\r\n\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW--",
            CURLOPT_HTTPHEADER => array(
                "accept: application/json",
                "cache-control: no-cache",
                "content-type: multipart/form-data; boundary=----WebKitFormBoundary7MA4YWxkTrZu0gW",
                "postman-token: e32cedc2-5edc-1daf-e636-4c00fa8078b0"
            ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            return "Error #:" . $err;
        }

        return $response;

    }
}

?>
