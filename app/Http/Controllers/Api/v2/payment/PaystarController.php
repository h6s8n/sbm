<?php

namespace App\Http\Controllers\Api\v2\payment;

use App\Model\Visit\Message;
use App\Model\Advertising\Advertising;
use App\Model\Badge\BadgeRequest;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Str;

class PaystarController extends Controller
{

    private $mobile;
    private $password;
    private $callback;
    private $TERMINAL_ID;
    private $sign;
    protected $request;


    public function __construct()
    {
        if (!function_exists('jalali_to_gregorian')){
            require(base_path('app/jdf.php'));
        }
        $this->TERMINAL_ID = "67m4630gm9wdzn";
        $this->sign = "EFF45CB23F08FC48128B25DB7B1E5B10DD66106A7CE906D5F352E0924CEA257B0E8F1987CDF3D7420D54336FCB4FA7FC6C758BFFC315096D5DF0B442296A9AEBE212FCE5A45893FC329C6BCBC7020C93F972284DB478D1EB551B6AEFFC3DD737427601DE7F4C7A8C5B523AD5EAFACB9F0AF0B162CA327B2FB9B3DD393EE2FA36";
        $this->wallet_id = "1496647";
        $this->wallet_api_token = '85af5f525dfe4cc88547444c481f455c';
        $this->merchant_api_token = '54de427666bf4e5eb6424445fa22e70e';
    }

    public function pay($data)
    {
        $amount = $data['amount'];
        $callback = $data['callback'] ?? route('zibal.verify2');
        $mobile = $data['mobile'] ?? null;
        $factorNumber = $data['factorNumber'];
        $description = $data['$description'] ?? null;

        $parameters = array(
            "id" => $this->wallet_id,
            "merchant"=> $this->merchant_key,//required
            "gatewayMerchant"=> $this->merchant_key,
            "callbackUrl"=> $callback,//required
            "amount"=> $amount,//required
            "description"=> $description,//optional
            "orderId"=> $factorNumber,//optional
            "mobile"=> $mobile,//optional for mpg
        );

        $response = $this->prepare('https://api.zibal.ir/v1/wallet/charge', $parameters, "Authorization: Bearer " . $this->merchant_api_token);


        if ($response->result == 1){
            return success_template('https://gateway.zibal.ir/start/'.$response->trackId);
        }

        return redirect('https://sbm24.com/payment_fail?token=' . $token . '&status=NOK');
    }

    /**
     * connects to zibal's rest api
     * @param $path
     * @param $parameters
     */
    function prepare($url, $parameters,$header = null)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->TERMINAL_ID
        ]);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS,json_encode($parameters));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response  = curl_exec($ch);
        curl_close($ch);
        return json_decode($response);
    }

    public function verify()
    {
        $success = request()->get('status');
        $orderId = request()->get('order_id');
        $trackId = request()->get('ref_num');

        $advertising = Advertising::where('factorNumber',$orderId)->first();

        if ($success == 1) {

            //start verfication
            $parameters = array(
                "ref_num" => $trackId,//required
                "amount" => $advertising->amount,//required
                "sign" => $this->sign,//required

            );


            try {
                $response = $this->prepare('https://core.paystar.ir/api/pardakht/verify', $parameters);

            }catch (\Exception $e){
                return redirect('https://sbm24.com/payment_fail?token=' . $trackId . '&status=NOK');
            }

            if ($response->status == 1){

                $transId= $response->data->ref_num;

                if ($advertising) {

                    $advertising->pay_status = 'paid';
                    $advertising->transId = $transId;
                    $advertising->paid_at = Carbon::now()->format('Y-m-d h:i:s');
                    $advertising->save();

                    return redirect('https://sbm24.com/payment_success?token=' . $orderId . '&status=OK');

                } elseif ($request->pay_status != 'paid') {
                    return redirect('https://sbm24.com/payment_fail?token=' . $orderId . '&status=NOK');
                }
            }else{
                return redirect('https://sbm24.com/payment_fail?token=' . $orderId . '&status=NOK');
            }
        }
        return redirect('https://sbm24.com/payment_fail?token=' . $orderId . '&status=NOK');
    }

    public function register(Request $request)
    {
        \request()->validate([
            'mobile' => 'required|digits:11|starts_with:09',
            'fullname' => 'required',
            'plan' => 'required',
            'title' => 'required',
            'subject' => 'required',
            'picture' => 'required',
            'link' => 'nullable'
        ], [
            'mobile.digits' => 'شماره همراه را لاتین و با فرمت صحیح وارد کنید',
            'mobile.starts_with' => 'شماره همراه نامعتبر',
            'mobile.required' => 'شماره همراه الزامی',
            'fullname.required' => 'نام و نام خانوادگی الزامی',
            'plan.required' => 'نوع جایگاه الزامی',
            'title.required' => 'عنوان الزامی',
            'picture.required' => 'تصویر الزامی',
            'subject.required' => 'حوزه فعالیت الزامی',
        ]);


        $this->request = $request;
        try {
            $Advertising = Advertising::create(\request()->all());
            if ($Advertising instanceof Advertising)
                if (\request()->file('picture')) {
                    $picture = $this->uploadImageCt('picture', 'files');
                    $Advertising->picture = $picture;
                    $Advertising->save();
                }
            $response = [
                'status' => true,
                'data' => $Advertising
            ];
        } catch (Exception $ex) {
            $response = [
                'status' => false,
                'message' => $ex->getMessage()
            ];
        }

        if ($response['status'])
            return success_template($response['data']);
        return error_template($response['message']);
    }

    public function index($plan = null)
    {
        if ($plan){
            $items = Advertising::wherePlan($plan)->whereStatus('active')->get();
        }else{
            $items = Advertising::whereStatus('active')->get();
        }

        return success_template($items);
    }
}
