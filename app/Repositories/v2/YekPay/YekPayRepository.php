<?php


namespace App\Repositories\v2\YekPay;


use Ixudra\Curl\Facades\Curl;

class YekPayRepository
{
    private $merchantId;
    private $request_URL;
    private $start_URL;
    private $start_verify;
    private $invoice_number;


    private $sandbox_request;
    private $sandbox_start;
    private $sandbox_verify;

    /** Currency Codes
     * 978 = EUR
     * 364 = IRR
     * 784 = AED
     * 826 = GBP
     * 949 = TRY
     */

    public function __construct($invoice_number=null)
    {
        $this->merchantId = 'CJM6T4DSE389HKTFTU2PDJCXS5YP3NBD';
        $this->request_URL = 'https://gate.yekpay.com/api/payment/request';
        $this->start_URL='https://gate.yekpay.com/api/payment/start/';
        $this->start_verify='https://gate.yekpay.com/api/payment/verify';


        $this->invoice_number = $invoice_number;

        $this->sandbox_request='https://api.yekpay.com/api/sandbox/request';
        $this->sandbox_start = 'https://api.yekpay.com/api/sandbox/payment/';
        $this->sandbox_verify = 'https://api.yekpay.com/api/sandbox/verify';


    }

    public function pay($data)
    {

        $posts = [
            'merchantId' => $this->merchantId,
//            'fromCurrencyCode' => $data['fromCurrencyCode'],
            'fromCurrencyCode' => 978,
//            'toCurrencyCode' => $data['toCurrencyCode'],
            'toCurrencyCode' => 978,
            'email' => $data['email'],
            'mobile' => $data['mobile'],
            'firstName' => $data['firstName'],
            'lastName' => $data['lastName'],
            'address' => $data['address'],
            'postalCode' => $data['postalCode'],
            'country' => $data['country'],
            'city' => $data['city'],
            'description' => $data['description'],
            'callback'=>route('yek-pay.verify'),
//            'amount' => $data['amount'],
            'amount' => number_format($data['amount'],2),
            'orderNumber' => $this->invoice_number
        ];
        try {
//            $result = Curl::to($this->sandbox_request)
            $result = Curl::to($this->request_URL)
                ->withData($posts)
                ->returnResponseObject(true)
                ->post();

            $result=json_decode($result->content,true);
            if ($result["Code"] == 100) {
//                $Payment_URL = $this->sandbox_start . $result['Authority'];
                $Payment_URL = $this->start_URL . $result['Authority'];
               return success_template(["url"=>$Payment_URL]);
            }

            return error_template($result['Description']);
        } catch (exception $ex) {
            var_dump($ex);
        }
    }

    public function verify()
    {
        try
        {
            if (request()->has('status') && request()->input('status') )
            {
                $Authority  = request()->input('authority');
                $posts = [
                    'merchantId' => $this->merchantId,
                    'authority'  => $Authority,
                ];

//                $result = Curl::to($this->sandbox_verify)
                $result = Curl::to($this->start_verify)
                    ->withData($posts)
                    ->returnResponseObject(true)
                    ->post();

                $object = json_decode($result->content,true);

                if ( $object['Code'] == 100 )
                {
                    return $object;
                }
                else
                {
                    return  false;
                    echo('YekPay Error : ' . $object['Code']);
                }
            }
            else
            {
                return  request()->input('authority');
                echo('YekPay Payment Cancelled');
            }
        }
        catch (exception $ex)
        {
            var_dump($ex);
        }
    }
}
