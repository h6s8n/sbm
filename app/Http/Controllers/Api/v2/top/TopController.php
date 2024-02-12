<?php

namespace App\Http\Controllers\Api\v2\top;

use App\Http\Controllers\Api\v1\User\Visit\ReserveController;
use App\Model\Notification\UserDoctorNotification;
use App\Model\Transaction\AffiliateTransaction;
use App\Model\Visit\DoctorCalender;
use App\Model\Visit\EventReserves;
use App\Model\Visit\Message;
use App\Model\Visit\TransactionReserve;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Str;

class TopController extends Controller
{
    private $pin = "IO556JWMaNx7d3d5RKeC";
    private $terminal = "98633196";

    public function __construct()
    {
        if (!function_exists('i_to_gregorian'))
            require(base_path('app/jdf.php'));

    }

    public function pay($data)
    {
        ini_set("soap.wsdl_cache_enabled", "0");
        $PIN = $this->pin;
        $wsdl_url = "https://pec.shaparak.ir/NewIPGServices/Sale/SaleService.asmx?WSDL";
        $site_call_back_url = route('top.confirm');

        $amount = $data['Amount'];
        $order_id = $data['OrderId'];

        $amount = '50000';
        $params = array(
            "LoginAccount" => $PIN,
            "Amount" => $amount,
            "OrderId" => $order_id,
            "CallBackUrl" => $site_call_back_url
        );
//        dd(data);
        $client = new \SoapClient($wsdl_url);
        try {
            $result = $client->SalePaymentRequest(array(
                "requestData" => $params
            ));
            if ($result->SalePaymentRequestResult->Token && $result->SalePaymentRequestResult->Status === 0) {
                header("Location: https://pec.shaparak.ir/NewIPG/?Token=" . $result->SalePaymentRequestResult->Token); /* Redirect browser */
                exit ();
            } elseif ($result->SalePaymentRequestResult->Status != '0') {
                return $result->SalePaymentRequestResult->Status;
            }
        } catch (Exception $ex) {
            $err_msg = $ex->getMessage();
        }
    }

    public function confirm()
    {
        $PIN = $this->pin;
        $wsdl_url = "https://pec.shaparak.ir/NewIPGServices/Confirm/ConfirmService.asmx?WSDL";

        $Token = $_REQUEST ["Token"];
        $status = $_REQUEST ["status"];
        $factorNumber = $_REQUEST ["OrderId"];

        $trans = TransactionReserve::where('factorNumber', $factorNumber)->first();

        if (!$trans)
            return redirect(get_ev('cp_live') . '/user/reserve_fail/' . $factorNumber);

        if ($status == "0") {

            $TerminalNo = $this->terminal;
            $Amount = $_REQUEST ["Amount"];
            $transId = $_REQUEST ["RRN"];

            $params = array(
                "LoginAccount" => $PIN,
                "Token" => $Token
            );
            $client = new \SoapClient($wsdl_url);
            try {
                $result = $client->ConfirmPayment(array(
                    "requestData" => $params
                ));
                if ($result->ConfirmPaymentResult->Status != '0') {
                    dd($result->ConfirmPaymentResult->Status);
                    return redirect(get_ev('cp_live') . '/user/reserve_fail/' . $factorNumber);
                } else {
                    return $this->verified();
                }
            } catch (Exception $ex) {
                $err_msg = $ex->getMessage();
            }
        } elseif ($status) {
            return redirect(get_ev('cp_live') . '/user/reserve_fail/' . $factorNumber);
        } else {

            return redirect(get_ev('cp_live') . '/user/reserve_fail/' . $factorNumber);
        }

    }

    public function verified()
    {
        $Token = $_REQUEST ["Token"];
        $status = $_REQUEST ["status"];
        $factorNumber = $_REQUEST ["OrderId"];
        $TerminalNo = $this->terminal;
        $Amount = $_REQUEST ["Amount"];
        $transId = $_REQUEST ["RRN"];

        $request = TransactionReserve::where('factorNumber', $factorNumber)->first();
        if ($request && $status == 0) {
            $request->message = 'OK';
            $request->status = 'paid';
            $request->transId = $transId;
            $request->save();

            $calender = DoctorCalender::find($request->calender_id);
            ++$calender->reservation;
            $calender->save();


            $fa_date_part = explode('-', $calender->fa_data);
            $en_date = jalali_to_gregorian($fa_date_part[0], $fa_date_part[1], $fa_date_part[2], '-');


            $capacity_mints = 60 / $calender->capacity;
            $max_time = $calender->time + 1;
            $reserve_time = null;


            $start_date = date('Y-m-d', strtotime($calender->data));
            if ((jdate('Y-m-d') == jdate('Y-m-d', strtotime($start_date))) &&
                (((int)date('H')) == $calender->time)) {
                $start = Carbon::parse($start_date)->addHours($calender->time)->addMinutes(date('i'));
            } else {
                $start = Carbon::parse($start_date)->addHours($calender->time);
            }

            $getevents = EventReserves::where('doctor_id', $calender->user_id)
                ->where('fa_data', $calender->fa_data)
                ->where('time', $calender->time)
                ->where('visit_status', 'not_end')->orderBy('reserve_time', 'DESC')->first();
            if ($getevents) {

                $start = Carbon::parse($getevents->reserve_time)->addMinutes($capacity_mints);

            }

            $reserve_time = date('Y-m-d H:i', strtotime($start));

            if (((int)date('H', strtotime($start))) >= $max_time) {
                $min = date('i', strtotime($start));
                $min += 10;

                $start = Carbon::parse($reserve_time)->subMinutes($min);
                $reserve_time = date('Y-m-d H:i', strtotime($start));
            }

            $user = auth()->user();

            $tokenRoom = Str::random(15);
            $newVisit = new EventReserves();
            $newVisit->user_id = $user->id;
            $newVisit->doctor_id = $calender->user_id;
            $newVisit->calender_id = $calender->id;
            $newVisit->token_room = $tokenRoom;
            $newVisit->fa_data = $calender->fa_data;
            $newVisit->data = $en_date;
            $newVisit->time = $calender->time;
            $newVisit->reserve_time = $reserve_time;
            $newVisit->save();

            $credit = $user->credit - $request->amount;
            $credit = ($credit <= 0) ? 0 : $credit;

            $user->credit = $credit;
            $user->save();
//            return success_template($request);
            if ($newVisit->id && $request->affiliate_id) {
                $affiliate = User::find($request->affiliate_id);
                AffiliateTransaction::create([
                    'user_id' => $user->id,
                    'doctor_id' => $request->doctor_id,
                    'affiliate_id' => $request->affiliate_id,
                    'event_id' => $newVisit->id,
                    'total' => $request->amount,
                    'amount' => (OurBeneficiary() * $affiliate->affiliate_percent) / 100
                ]);
            }

            if ($newVisit->id) {
                $dossiers = Message::where('user_id', $user->id)
                    ->where('audience_id', $request->doctor_id)
                    ->where('type', 'dossierText')
                    ->whereNull('room_token')
                    ->orderBy('created_at', 'desc')
                    ->first();
                if ($dossiers) {
                    $dossiers->room_token = $newVisit->token_room;
                    $dossiers->save();
                }
                $newVisit->safe_call_mobile = $user->phone;
                $newVisit->save();
            }

            $waiting = UserDoctorNotification::where('user_id', $user->id)
                ->where('doctor_id', $request->doctor_id)
                ->where('sent_message', 0)->update(['sent_message' => 1]);

            $doctor = User::find($calender->user_id);
            $sms = new ReserveController();
            $sms->send_user_notification();
            $this->send_user_notification($doctor, $user, $calender, $newVisit);

            return redirect(get_ev('cp_live') . '/user/reserve_save/' . $this->request->token);

        } elseif ($request->status != 'paid') {

            $request->message = 'تراکنش لغو شد';
            $request->status = 'cancel';
            $request->save();
        }
        return redirect(get_ev('cp_live') . '/user/reserve_save/' . $factorNumber);
    }
}
