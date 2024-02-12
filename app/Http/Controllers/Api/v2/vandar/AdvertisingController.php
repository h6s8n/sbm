<?php

namespace App\Http\Controllers\Api\v2\vandar;

use App\Http\Controllers\Api\v1\User\Visit\ReserveController;
use App\Model\Notification\UserDoctorNotification;
use App\Model\Transaction\AffiliateTransaction;
use App\Model\Vandar\VandarToken;
use App\Model\Visit\DoctorCalender;
use App\Model\Visit\EventReserves;
use App\Model\Visit\Message;
use App\Model\Advertising\Advertising;
use App\Model\Visit\TransactionReserve;
use App\SendSMS;
use App\Services\Gateways\src\Zibal;
use App\User;
use App\Model\Badge\BadgeRequest;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Vandar\Laravel\Facade\Vandar;

class AdvertisingController extends Controller
{

    private $mobile;
    private $password;
    private $callback;
    private $api_key;
    protected $request;


    public function __construct()
    {
        if (!function_exists('jalali_to_gregorian')){
            require(base_path('app/jdf.php'));
        }
        $this->merchant_key = "621f370718f9343027368c9e";
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
            $header
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
        $success = request()->get('success');
        $orderId = request()->get('orderId');
        $trackId = request()->get('trackId');

        if ($success) {

            //start verfication
            $parameters = array(
                "merchant" => $this->merchant_key,//required
                "trackId" => $trackId,//required

            );


            try {
                $response = $this->prepare('https://gateway.zibal.ir/v1/verify', $parameters);

            }catch (\Exception $e){
                return redirect('https://sbm24.com/payment_fail?token=' . $trackId . '&status=NOK');
            }

            if ($response->result == 100){

                $factor_number = $response->orderId;
                $transId= $response->refNumber;

                $request = BadgeRequest::where('token', $factor_number)->first();

                if ($request) {

                    $request->pay_status = 'PAYED';
                    $request->transaction_id = $transId;
                    $request->save();

                    return redirect('https://sbm24.com/payment_success?token=' . $factor_number . '&status=OK');

                } elseif ($request->pay_status != 'PAYED') {
                    return redirect('https://sbm24.com/payment_fail?token=' . $factor_number . '&status=NOK');
                }
            }else{
                return redirect('https://sbm24.com/payment_fail?token=' . $trackId . '&status=NOK');
            }
        }
        return redirect('https://sbm24.com/payment_fail?token=' . $trackId . '&status=NOK');
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

    public function gateway($token){

        $factorNumber = rand(111111, 999999);

        $ad = Advertising::where('token' , $token)->where('payment_status','pending')->first();
        if(!$ad) return redirect('https://sbm24.com/payment_fail?token=' . $token);

        $CallbackURL = url('payment/verify/advertising/' . $token);


        $pay = new Zibal();

        $data['amount'] = (int)$ad->amount;
        $data['$description'] = $ad->fullname;
        $data['mobile'] = $ad->mobile;
        $data['callback'] = $CallbackURL;
        $data['merchant_key'] = "6255560318f934730ba39dd1"; //For Zibal
        $data['factorNumber'] = $factorNumber;

        $pay->pay2($data);

    }

    public function adVerify($token)
    {
        $success = request()->get('success');
        $orderId = request()->get('orderId');
        $trackId = request()->get('trackId');

        if ($success) {

            //start verfication
            $parameters = array(
                "merchant" => "6255560318f934730ba39dd1",//required
                "trackId" => $trackId,//required

            );


            try {
                $response = $this->prepare('https://gateway.zibal.ir/v1/verify', $parameters);

            }catch (\Exception $e){
                return redirect('https://sbm24.com/payment_fail?token=' . $trackId . '&status=NOK');
            }

            if ($response->result == 100){

                $factor_number = $response->orderId;
                $transId= $response->refNumber;

                $ad = Advertising::where('token', $token)->first();

                if ($ad) {

                    $ad->payment_status = 'paid';
                    $ad->transId = $transId;
                    $ad->paid_at = Carbon::now()->format('Y-m-d h:i:s');
                    $ad->save();

                    return redirect('https://sbm24.com/payment_success?token=' . $factor_number . '&status=OK');

                } elseif ($ad->payment_status != 'paid') {
                    return redirect('https://sbm24.com/payment_fail?token=' . $factor_number . '&status=NOK');
                }
            }else{
                return redirect('https://sbm24.com/payment_fail?token=' . $trackId . '&status=NOK');
            }
        }
        return redirect('https://sbm24.com/payment_fail?token=' . $trackId . '&status=NOK');
    }

}
