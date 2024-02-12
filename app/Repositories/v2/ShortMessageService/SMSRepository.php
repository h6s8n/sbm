<?php


namespace App\Repositories\v2\ShortMessageService;

use Ixudra\Curl\Facades\Curl;

class SMSRepository
{
    private $SecurityCode;
    private $key;

    public function __construct()
    {
        $this->SecurityCode = "SBM24)(!@!@%&am";
        $this->key = "a2c9f7f25e815f427863f7f2";
    }

    public function SendVerificationCode($code, $mobile)
    {
        $data = array(
            "ParameterArray" => array(
                array(
                    "Parameter" => "code",
                    "ParameterValue" => $code
                ),
            ),
            "Mobile" => $mobile,
            "TemplateId" => "36676"
        );
        $token = $this->MakeToken();
        if ($token===false)
            return false;
        $response = Curl::to('https://ws.sms.ir/api/UltraFastSend')
            ->withHeader('Content-Type:application/json')
            ->withHeader('x-sms-ir-secure-token:' . $token)
            ->withData($data)
            ->asJson(true)
            ->post();
        return $response['IsSuccessful'];
    }

    public function template($mobile,$params,$template_id)
    {
        $data = array(
            "ParameterArray" => $params,
            "Mobile" => $mobile,
            "TemplateId" => $template_id
        );
        $token = $this->MakeToken();
        if ($token===false)
            return false;
        $response = Curl::to('https://ws.sms.ir/api/UltraFastSend')
            ->withHeader('Content-Type:application/json')
            ->withHeader('x-sms-ir-secure-token:' . $token)
            ->withData($data)
            ->asJson(true)
            ->post();
        return $response['IsSuccessful'];
    }

    private function MakeToken()

    {
        $postData = [
            'UserApiKey' => $this->key,
            'SecretKey' => $this->SecurityCode,
        ];

        $result = Curl::to('https://ws.sms.ir/api/Token')
            ->withData($postData)
            ->withHeader('Content-Type: application/json')
            ->asJson(true)
            ->post();
        if ($result['IsSuccessful']) {
            return $result['TokenKey'];
        } else {
            return false;
        }
    }
}
