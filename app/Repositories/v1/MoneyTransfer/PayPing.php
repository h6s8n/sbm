<?php


namespace App\Repositories\v1\MoneyTransfer;


class PayPing implements GateWayInterface
{
    private $header;
    private $url;
    private $amount;
    private $shaba;
    private $description;

    public function __construct($amount,$shaba,$description=null)
    {
        $this->header = array(
            "authorization: bearer 619d9ab6f1e0f6fbb8134eede2eb5f9f02509e97e7158a3f72d30f95e3743d2e",
            "cache-control: no-cache",
            "content-type: application/json",
            "postman-token: 573be774-6b5a-85d5-f85a-de98b6e47bbd"
        );
        $this->url="https://api.payping.ir/v1/withdraw/refund";
        $this->amount = ((int)$amount)/10;
        $this->shaba='IR'.$shaba;
        $this->description=$description;
    }

    /**
     * Transfer Money
     * @param $data
     * @return array
     */
    public function transfer()
    {
        $data = [
            'amount'=>$this->amount,
            'shaba'=>$this->shaba,
            'description'=>$this->description
        ];
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $this->url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => $this->header,
        ));
        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);
        return [
            'error'=>$err,
            'response'=>$response
        ];
    }
}
