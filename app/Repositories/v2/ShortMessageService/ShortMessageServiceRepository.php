<?php


namespace App\Repositories\v2\ShortMessageService;


use App\Traites\RepositoryResponseTrait;

class ShortMessageServiceRepository implements ShortMessageInterface
{
    use RepositoryResponseTrait;

    public function sendConfirmationCode($mobile,$code)
    {
      //  dd(1);
        $template = "confirmsbm";
        $getPr = "&token=" . $code;

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://api.kavenegar.com/v1/463878334E357372564E5A3244796257356A7675375879443879756468726C6F/verify/lookup.json?receptor=" . $mobile . $getPr . "&template=" . $template,
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
        curl_close($curl);
        $response = json_decode($response,true);
        if ($response['return']['status']!=200) {
            return $this->ErrorTemplate('Wrong request');
        } else {
            return $this->SuccessResponse($response);
        }
    }

    public function SendSmsWithOneToken($mobile,$token,$template)
    {

        $useragent = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_8_2) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.89 Safari/537.36';
        $timeout = 120;

        $ch = curl_init("https://api.kavenegar.com/v1/463878334E357372564E5A3244796257356A7675375879443879756468726C6F/verify/lookup.json?receptor=" . $mobile . "&token=" . $token  . "&template=" . $template);
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
}
