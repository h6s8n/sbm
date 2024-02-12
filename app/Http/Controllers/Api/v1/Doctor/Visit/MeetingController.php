<?php

namespace App\Http\Controllers\Api\v1\Doctor\Visit;

use App\Enums\LanguageEnum;
use App\Enums\UserActivityLogEnum;
use App\Enums\VisitLogEnum;
use App\Enums\DiscountEnum;
use App\Enums\PaymentSourceEnum;
use App\Enums\VisitTypeEnum;
use App\Events\MessageSent;
use App\Events\UserActivity;
use App\Http\Controllers\Api\v2\vandar\VandarController;
use App\Model\Arzpaya\ArzpayaTransaction;
use App\Model\Discount\Discount;
use App\Model\Transaction\AffiliateTransaction;
use App\Model\Vandar\VandarTransaction;
use App\Model\Visit\DoctorCalender;
use App\Model\Visit\Dossiers;
use App\Model\Visit\EventReserves;
use App\Model\Visit\Message;
use App\Model\Visit\TransactionCredit;
use App\Model\Visit\TransactionDoctor;
use App\Model\Visit\TransactionReserve;
use App\Repositories\v2\Logs\UserActivity\UserActivityLogInterface;
use App\Repositories\v2\Visit\VisitLogInterface;
use App\SendSMS;
use App\User;
use App\Services\Gateways\src\Zibal;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Ixudra\Curl\Facades\Curl;

class MeetingController extends Controller
{
    protected $request;
    private $log;
    private $wallet_id;
    private $ActivityLog;

    public function __construct(Request $request,
                                VisitLogInterface $visitLog,
                                UserActivityLogInterface $activityLog)
    {
        date_default_timezone_set("Asia/Tehran");
        $this->request = $request;
        $this->ActivityLog = $activityLog;
        $this->log = $visitLog;
        //$this->wallet_id = '1637935';
        $this->wallet_id = '1629014';

        require(base_path('app/jdf.php'));

    }


    public function MeetingList()
    {

        $user = auth()->user();
        $this->ActivityLog->CreateLog($user, UserActivityLogEnum::LoadFirstPage);
        $EventList = EventReserves::join('users', 'users.id', '=', 'event_reserves.user_id')
            ->where('event_reserves.doctor_id', $user->id)
            ->where('event_reserves.visit_status', '!=', 'refunded')
            ->orderBy('event_reserves.reserve_time', 'desc');

        if (\request()->has('lang') && \request()->input('lang'))
            $EventList = $EventList->leftJoin('user_dictionaries', function ($leftJoin) {
                $leftJoin->on('users.id', 'user_dictionaries.user_id')
                    ->where('user_dictionaries.language_id', LanguageEnum::getIdBySlug(request()->input('lang')));
            })->select(
                'event_reserves.id as key',
                'event_reserves.token_room',
                'event_reserves.fa_data',
                'event_reserves.data',
                'event_reserves.calender_id',
                'event_reserves.time',
                'event_reserves.last_activity_doctor',
                'event_reserves.last_activity_user',
                'event_reserves.visit_status',
                'event_reserves.reserve_time',
                'event_reserves.user_id as user_key',
                'users.username as user_username',
                DB::raw('coalesce(user_dictionaries.fullname,users.fullname) as user_name'),
                'users.show_phone as user_show_phone',
                'users.nationalcode',
                'users.mobile as user_mobile',
                'users.gender as user_gender',
                'users.birthday as user_birthday',
                'users.picture as user_image'
            )->get();
        else
            $EventList = $EventList->select(DB::raw('COUNT(event_reserves.id) as counts'),
                DB::raw('coalesce(user_dictionaries.name,users.name) as name'),
                DB::raw('coalesce(user_dictionaries.fullname,users.fullname) as fullname'),
                DB::raw('coalesce(user_dictionaries.job_title,users.job_title) as job_title'),
                'users.username', 'users.doctor_nickname',
                'users.family', 'users.picture', 'users.online_status', 'sp.name as sp_name',
                'user_dictionaries.fullname as dic_fullname')
                ->select(
                    'event_reserves.id as key',
                    'event_reserves.token_room',
                    'event_reserves.fa_data',
                    'event_reserves.data',
                    'event_reserves.calender_id',
                    'event_reserves.time',
                    'event_reserves.last_activity_doctor',
                    'event_reserves.last_activity_user',
                    'event_reserves.visit_status',
                    'event_reserves.reserve_time',
                    'event_reserves.user_id as user_key',
                    'users.username as user_username',
                    'users.fullname as user_name',
                    'users.show_phone as user_show_phone',
                    'users.nationalcode',
                    'users.mobile as user_mobile',
                    'users.gender as user_gender',
                    'users.birthday as user_birthday',
                    'users.picture as user_image'
                )->get();


        $UserInt = [];
        $UserList = [];
        if ($EventList) {
            foreach ($EventList as $evant) {

                if (!in_array($evant['user_username'], $UserInt)) {

                    $UserInt[] = $evant['user_username'];

                    $chat_count = Message::where('seen_audience', 0)
                        ->where('user_id', $evant['user_key'])
                        ->where('audience_id', $user->id)->count();

                    $UserList[$evant['user_username']] = [
                        'user_info' => [
                            'user_image' => $evant['user_image'],
                            'user_username' => $evant['user_username'],
                            'user_name' => $evant['user_name'],
                            'user_ncode' => $evant['nationalcode'],
                            'user_key' => $evant['user_key'],
                            'user_mobile' => ($evant['user_show_phone']) ? $evant['user_mobile'] : '',
                            'user_gender' => ($evant['user_gender'] == 0) ? 'مرد' : 'زن',
                            'user_birthday' => ($evant['user_birthday']) ? $evant['user_birthday'] : '',
                            'chat_count' => $chat_count,
                        ]
                    ];

                }
                $UserList[$evant['user_username']]['event_list'][] = $this->EvantFormat($evant, $user);

            }
        }


        return success_template($UserList);


    }

    public function EvantFormat($evant, $user)
    {
//        $dossiers_count = Message::where('audience_id', $user->id)
//            ->where('user_id',$evant['user_id'])
//            ->where('type','dossierFile')
//            ->where('seen_audience', 0)
//            ->count();
//        $dossiers_count = Dossiers::where('status', 'active')
//            ->where('seen_audience', 0)
//            ->where('audience_id', $user->id)->count();
        $dossiers_count = 0;

        $capacity_time = 15;
        $min = 15;
        $my_reserf = 0;


        $calender = DoctorCalender::where('id', $evant['calender_id'])->first();
        if ($calender) {

            $capacity = (int)$calender->capacity;
            if ($capacity > 0) {

                $capacity = 60 / $capacity;
                $capacity = round($capacity, 0, PHP_ROUND_HALF_DOWN);
                $capacity_time = $capacity;

                $count = 1;
                $my_number_transaction = 1;
                $transaction_reserves = TransactionReserve::where('calender_id', $evant['calender_id'])->get();
                if ($transaction_reserves) {
                    foreach ($transaction_reserves as $item) {
                        if ($evant['user_key'] === $item['user_id']) {
                            $my_number_transaction = $count;
                            $my_reserf = $count;
                        }
                        $count++;
                    }
                }

                if ($my_number_transaction > 1) $my_number_transaction = $my_number_transaction - 1;

                $min = $capacity * $my_number_transaction;

            }

        }

        $new_hours = (int)$evant['time'];
        if ($new_hours == 0) $new_hours = 24;

        $start_min = $min - $capacity_time;

        $fa_date_part = explode('-', $evant['fa_data']);
        $en_date = jalali_to_gregorian($fa_date_part[0], $fa_date_part[1], $fa_date_part[2], '-');

        $start = Carbon::parse($en_date)->addHours($new_hours)->addMinutes($start_min);
        $st_start = false;
        if (jdate('Y-m-d', strtotime($start)) == jdate('Y-m-d')) {

            if (change_number(jdate('H', strtotime($start))) == change_number(jdate('H'))) {
                $st_start = true;

                $new_min = change_number(jdate('i'));
                $new_min = $new_min + 15;
                $start = Carbon::parse($en_date)->addHours($new_hours)->addMinutes(($start_min + $new_min));
                $start_hours = jdate('H:i', strtotime($start));
                $start_date = jdate('l - d F Y', strtotime($start));

            }

        }

        if (!$st_start) {
            $start_hours = jdate('H:i', strtotime($start));
            $start_date = jdate('l - d F Y', strtotime($start));
        }

        if ($evant['reserve_time']) {
            $start_hours = jdate('H:i', strtotime($evant['reserve_time']));
            $start_date = jdate('l - d F Y', strtotime($evant['reserve_time']));
        }

        $finish = Carbon::parse($en_date)->addHours($new_hours)->addMinutes($min);
        $finish_hours = jdate('H:i', strtotime($finish));
        $finish_date = jdate('l - d F Y', strtotime($finish));

        return [
            'token' => $evant['token_room'],
            'fa_data' => $evant['fa_data'],
            'data' => $evant['data'],
            'time' => $evant['time'],
            'start_date' => $start_date,
            'start_hours' => $start_hours,
            'finish_date' => $finish_date,
            'finish_hours' => $finish_hours,
            'min' => $min,
            'my_reserf' => $my_reserf,
            'capacity_time' => $capacity_time,
            'last_activity_doctor' => $evant['last_activity_doctor'],
            'last_activity_user' => $evant['last_activity_user'],
            'visit_status' => $evant['visit_status'],
            'dossiers_count' => $dossiers_count,
        ];
    }


    public function finish()
    {

        $user = auth()->user();

        $ValidData = $this->validate($this->request, [
            'event' => 'required',
        ]);

        $user = auth()->user();

        try {
            DB::beginTransaction();

            $event = EventReserves::where('token_room', $ValidData['event'])
                ->where('doctor_id', $user->id)->where('visit_status', 'not_end')
                ->whereIn('status', ['active','secretary_end'])->first();
            if (!$event) {
                return error_template('این ویزیت خاتمه یافته است لطفا صفحه را رفرش کنید.');
            }

            $calendar = DoctorCalender::find($event->calender_id);
            $has_partner = $calendar->partner_id > 0 && $calendar->partner->sheba;
            $sheba = $has_partner ? $calendar->partner->sheba : $user->account_sheba;

            if (!$sheba){
                return error_template('لطفا از قسمت ویرایش اطلاعات شماره شبای خود را ثبت نمایید.');
            }

            $event->visit_status = 'end';
            $event->finish_at = date('Y-m-d h:i:s');
            $event->doctor_payment_status = 'debtor';
            $event->save();

            $this->log->createLog($event, auth()->id(), VisitLogEnum::FinishByDoctor);

            broadcast(new MessageSent($event->token_room))->toOthers();

            $transaction = TransactionReserve::where('user_id', $event->user_id)
                ->where('doctor_id', $user->id)
                ->where('calender_id', $event->calender_id)
                ->where('status', 'paid')
                ->first();

            if ($transaction) {

                if (!$transaction->factorNumber) {
                    $temp_factor = TransactionReserve::where('user_id', $event->user_id)
                        ->whereNotNull('factorNumber')
                        ->where('status', 'paid')
                        ->orderBy(DB::raw('updated_at'), 'DESC')
                        ->first();
                    if ($temp_factor)
                        $temp_factor = $temp_factor->factorNumber;
                    else
                        $temp_factor = rand(111111, 999999).time();
                }
                $ck_Dr = TransactionDoctor::where('user_id', $event->user_id)
                    ->where('doctor_id', $user->id)
                    ->where('event_id', $event->id)
                    ->whereIn('status', ['paid'])
                    ->first();

                if (!$ck_Dr) {
                    $transactionDr = TransactionDoctor::where('user_id', $event->user_id)
                        ->where('doctor_id', $user->id)
                        ->where('event_id', $event->id)
                        ->whereIn('status', ['pending'])
                        ->first();

                    $amount_visit = $transaction->amount;
                    $amount = 0;

                    $arzpaya = auth()->user()->arzpayaUser()->where('flag', 1)->first();

                    $discount_amount = 0;

                    if ($transaction->discount_id) {
                        $discount = Discount::find($transaction->discount_id);
                        if ($discount) {
                            switch ($discount->type) {
                                case DiscountEnum::ConstAmount:
                                {
                                    $discount_amount = $transaction->discount_amount;
                                    break;
                                }
                            }
                        }
                    }

                    $calendar = DoctorCalender::find($event->calender_id);

                    if ($calendar->type == VisitTypeEnum::type('in-person')){
                        $amount = $calendar->price - 49000;
                    }else{
                        $amount = $calendar->price;
                    }

                    if ($arzpaya) {
                        if (!$transactionDr) {
                            $transactionDr = new TransactionDoctor();
                        }
                        $transactionDr->user_id = $event->user_id;
                        $transactionDr->doctor_id = $user->id;
                        $transactionDr->event_id = $event->id;
                        $transactionDr->amount = $amount;
                        $transactionDr->status = 'paid';
                        $transactionDr->source = PaymentSourceEnum::Arzpaya;
                        $transactionDr->message = 'انتقال به ارزپایا';
                        $transactionDr->save();

                        $data = [
                            'user_id' => $event->user_id,
                            'doctor_id' => $user->id,
                            'event_id' => $event->id,
                            'external_user_id' => $arzpaya->external_user_id,
                            'uuid' => uniqid('', false),
                            'transaction_number' => $transaction->factorNumber ?: ($temp_factor ?: null),
                            'transaction_reserve_id' => $transaction->id,
                            'amount' => $amount,
                            'migrated' => 0,
                            'calendar_id' => $transaction->calender_id
                        ];
                        ArzpayaTransaction::create($data);
                    } else {

                        if (!$transactionDr) {
                            $transactionDr = new TransactionDoctor();
                        }



                        $has_partner = $calendar->partner_id > 0 && $calendar->partner->sheba;

                        $sheba = $has_partner ? $calendar->partner->sheba : $user->account_sheba;

                        if (!$sheba){
                            return error_template('لطفا از قسمت ویرایش اطلاعات شماره شبای خود را ثبت نمایید.');
                        }
                        $doctor_paid = ($transaction->amount - OurBeneficiary($calendar->type ?? null)) / 10;

                        $data = [
                            'track_id' => (string)uniqid('', false),
                            'amount' => $doctor_paid,
                            'iban' => 'IR' . $sheba
                        ];

                        $use_zibal = true;

                        if ($use_zibal){
                            $patient = User::where('id',$event->user_id)->first();

                            $zibal = new Zibal();
                            $zdata = [
                                'amount' =>  $amount,
                                'bankAccount' =>  'IR' . $sheba,
                                'description' => ' بیمار: ' . $patient->fullname . ' - پزشک: ' . $user->fullname .' - تاریخ ویزیت: ' . $event->fa_data . ' - ساعت ویزیت: ' . $event->time,
                                'uniqueCode' => (string)uniqid('', false),
                                'wallet_id' => $this->wallet_id,
                            ];

                            $transactionDr->user_id = $event->user_id;
                            $transactionDr->doctor_id = $user->id;
                            $transactionDr->event_id = $event->id;
                            $transactionDr->amount = $amount;
                            $transactionDr->status = 'pending';
                            $transactionDr->partner_id = $has_partner ? $calendar->partner->id : 0;
                            $transactionDr->save();


                            if ($doctor_paid > 0) {
//                            $result = $zibal->checkout($zdata);
                                $result = null;
                                if ($result == null){
                                    $result = (object)[];
                                    $result->result = 0;
                                }
                            } else {
                                $transactionDr->user_id = $event->user_id;
                                $transactionDr->doctor_id = $user->id;
                                $transactionDr->event_id = $event->id;
                                $transactionDr->amount = $amount;
                                $transactionDr->status = 'paid';
                                $transactionDr->save();
                                $transactionDr->message = 'مبلغ ویزیت صفر بوده است';
                                $result = (object)[];
                                $result->result = 0;
                            }
                            if ($result->result == 1) {

                                $extend_message = $has_partner ? ' - ' . $calendar->partner->name : NULL;

                                $transactionDr->user_id = $event->user_id;
                                $transactionDr->doctor_id = $user->id;
                                $transactionDr->event_id = $event->id;
                                $transactionDr->amount = $amount;
                                $transactionDr->status = 'paid';
                                $transactionDr->partner_id = $has_partner ? $calendar->partner->id : 0;
                                $transactionDr->transaction_id = $result->data->id;
                                $transactionDr->receipt = $result->data->receipt;
                                $transactionDr->message = 'پرداخت شده با کد شناسایی ' . $result->data->id . $extend_message;
                                $transactionDr->save();

                                if ($user->mobile) {
                                    SendSMS::sendTemplateTwo($user->mobile, $event->user()->first()->fullname ?? jdate('Y-m-d', strtotime($event->reserve_time)),
                                        $result->data->id, 'CheckoutDr');
                                }

                            } else {
                                $transactionDr->user_id = $event->user_id;
                                $transactionDr->doctor_id = $user->id;
                                $transactionDr->event_id = $event->id;
                                $transactionDr->amount = $amount;
                                $transactionDr->save();
                            }
                        }else {

                            if ($doctor_paid > 0) {
                                // $result = $zibal->checkout($zdata);
                                $ch_ = curl_init();
                                curl_setopt($ch_, CURLOPT_HTTPHEADER, $headers);
                                curl_setopt($ch_, CURLOPT_URL, 'https://api.vandar.io/v3/business/Sbm/settlement/store');
                                curl_setopt($ch_, CURLOPT_POSTFIELDS, $data);
                                curl_setopt($ch_, CURLOPT_RETURNTRANSFER, true);
                                $result = curl_exec($ch_);
                                curl_close($ch_);
                                $result = json_decode($result, true);
                            } else {
                                $transactionDr->user_id = $event->user_id;
                                $transactionDr->doctor_id = $user->id;
                                $transactionDr->event_id = $event->id;
                                $transactionDr->amount = $amount;
                                $transactionDr->status = 'paid';
                                $transactionDr->save();
                                $transactionDr->message = 'مبلغ ویزیت صفر بوده است';
                                $result['status'] = 0;
                            }

                            if ($result['status'] == 1) {

                                $extend_message = $has_partner ? ' - ' . $calendar->partner->name : NULL;

                                $transactionDr->user_id = $event->user_id;
                                $transactionDr->doctor_id = $user->id;
                                $transactionDr->event_id = $event->id;
                                $transactionDr->amount = $amount;
                                $transactionDr->status = 'paid';
                                $transactionDr->partner_id = $has_partner ? $calendar->partner->id : 0;
                                $transactionDr->transaction_id = $result['data']['settlement'][0]['transaction_id'];
                                $transactionDr->message = 'پرداخت شده با کد شناسایی ' . $result['data']['settlement'][0]['transaction_id'] . $extend_message;
                                $transactionDr->save();
                                SendSMS::sendTemplateTwo($user->mobile, $event->user()->first()->fullname ?? jdate('Y-m-d', strtotime($event->reserve_time)),
                                    $result['data']['settlement'][0]['transaction_id'], 'CheckoutDr');

                                try {
                                    VandarTransaction::create([
                                        'vandar_id' => $result['data']['settlement'][0]['id'],
                                        'iban_id' => $result['data']['settlement'][0]['iban_id'],
                                        'transaction_id' => $result['data']['settlement'][0]['transaction_id'],
                                        'amount' => $result['data']['settlement'][0]['amount']
                                    ]);
                                } catch (\Exception $exception) {
                                }


                            } else {

                                $transactionDr->user_id = $event->user_id;
                                $transactionDr->doctor_id = $user->id;
                                $transactionDr->event_id = $event->id;
                                $transactionDr->amount = $amount;
                                $transactionDr->save();
                            }
                        }
                    }


                    $affiliate = AffiliateTransaction::where('event_id', $event->id)->first();
                    if ($affiliate) {
                        $affiliate->status = 1;
                        $affiliate->save();
                    }

                }

            }

            DB::commit();
        }
        catch (\Exception $exception){
            DB::rollBack();

            \Log::error('finishing the visit. user_id: ' . $event->user->id .
                "\n\n message: " .
                $exception->getMessage() .
                "\n\n trace: " .
                $exception->getTraceAsString()
            );

            return error_template('مشکل ارتباط در زیر ساخت. لطفا دقایقی دیگر مجددا تلاش کنید');
        }

        return success_template(['event' => 'update']);

    }

    public function zibal($event,$doctor_paid,$transactionDr)
    {
        $user = auth()->user();
        $patient = User::where('id',$event->user_id)->first();
        $sheba = $user->account_sheba;

        $amount = $doctor_paid*10;
        $zibal = new Zibal();
        $zdata = [
            'amount' =>  $doctor_paid*10,
            'bankAccount' =>  'IR' . $sheba,
            'description' => ' بیمار: ' . $patient->fullname . ' - پزشک: ' . $user->fullname .' - تاریخ ویزیت: ' . $event->fa_data . ' - ساعت ویزیت: ' . $event->time,
            'uniqueCode' => (string)uniqid('', false),
        ];

        if ($doctor_paid > 0){
             $result = $zibal->checkout($zdata);
        }else{
            $transactionDr->user_id = $event->user_id;
            $transactionDr->doctor_id = $user->id;
            $transactionDr->event_id = $event->id;
            $transactionDr->amount = $doctor_paid*10;
            $transactionDr->status = 'paid';
            $transactionDr->save();
            $transactionDr->message = 'مبلغ ویزیت صفر بوده است';
        }

        if ($result->result == 1) {
            $extend_message = NULL;

            $transactionDr->user_id = $event->user_id;
            $transactionDr->doctor_id = $user->id;
            $transactionDr->event_id = $event->id;
            $transactionDr->amount = $doctor_paid*10;
            $transactionDr->status = 'paid';
            $transactionDr->partner_id = 0;
            $transactionDr->transaction_id = $result->data->id;
            $transactionDr->receipt = $result->data->receipt;
            $transactionDr->message = 'پرداخت شده با کد شناسایی ' . $result->data->id . $extend_message;
            $transactionDr->save();
            if ($user->mobile) {
                SendSMS::sendTemplateTwo($user->mobile, $event->user()->first()->fullname ?? jdate('Y-m-d', strtotime($event->reserve_time)),
                    $result->data->id, 'CheckoutDr');
            }
        } else {

            $transactionDr->user_id = $event->user_id;
            $transactionDr->doctor_id = $user->id;
            $transactionDr->event_id = $event->id;
            $transactionDr->amount = $doctor_paid*10;
            $transactionDr->save();
        }
    }

    public function cancel()
    {


        $ValidData = $this->validate($this->request, [
            'event' => 'required',
        ]);

        $user = auth()->user();


        $event = EventReserves::where('token_room', $ValidData['event'])->where('doctor_id', $user->id)->where('visit_status', 'not_end')->where('status', 'active')->first();
        if (!$event) return error_template('خطا ، شما نمیتوانید به این ویزیت دسترسی داشته باشید.');

        $event->visit_status = 'cancel';
        $event->finish_at = date('Y-m-d h:i:s');
        $event->doctor_payment_status = 'pending';
        $event->save();
        $this->log->createLog($event, auth()->id(), VisitLogEnum::CancelByDoctor);
        $transaction = TransactionReserve::where('user_id', $event->user_id)->where('doctor_id', $user->id)
            ->where('calender_id', $event->calender_id)->where('status', 'paid')->first();
        if ($transaction) {
            $amount_visit = $transaction->amount;
            $amount = 0;

            $discount_amount = 0;

            if ($transaction->discount_id) {
                $discount = Discount::find($transaction->discount_id);
                if ($discount) {
                    switch ($discount->type) {
                        case 1:
                        {
                            $discount_amount = $transaction->discount_amount;
                            break;
                        }
                    }
                }
            }

            $calendar = DoctorCalender::find($event->calender_id);
//            if ($amount_visit >= OurBeneficiary($calendar->type ?? null) && date('Y-m-d', strtotime($event->created_at)) >=
//                date('Y-m-d', strtotime(dateChangedBeneficiary())))
//                $amount = $amount_visit - OurBeneficiary($calendar->type ?? null) + $discount_amount;
//            else {
//
//                if ($amount_visit > 400000) {
////                    $amount_pe = ($amount_visit * 20) / 100;
//                    $amount = $amount_visit - 108000;
//                } else {
//                    $amount = $amount_visit - 108000;
//                }
//            }

            if ($calendar->type == VisitTypeEnum::type('in-person')){
                $amount = $calendar->price - 49000;
            }else{
                $amount = $calendar->price;
            }

            --$calendar->reservation;
            $calendar->save();

            /*$profits_type = $this->get_optien('system_profits_type');
            $profits = $this->get_optien('system_profits');

            switch ($profits_type){
                case 'percentage';

                    $amount_pe = ( $amount_visit * $profits ) / 100;
                    $amount = $amount_visit - $amount_pe;

                    break;
                case 'price';

                    $amount = $amount_visit - $profits;
                    if($amount < 0) $amount = 0;

                    break;
            }*/

            $client = User::where('id', $event->user_id)->first();
            if ($client) {
                $client->credit = ($client->credit + $amount);
                $client->save();
                if ($amount > 0) {
                    $sku = str_random(20);
                    $newTransaction = new TransactionCredit();
                    $newTransaction->user_id = $client->id;
                    $newTransaction->amount = $amount;
                    $newTransaction->token = $sku;
                    $newTransaction->status = 'paid';
                    $newTransaction->message = 'افزایش - عودت ویزیت با دکتر ' . $event->doctor->fullname;
                    $newTransaction->save();
                }
            }

        }


        return success_template(['event' => 'cancel']);

    }

    public function leftRoom($token)
    {
        $event = EventReserves::where('token_room', $token)->first();
        $event->duration = $this->request->input('visitTime') ?: 0;
        $event->save();
        $this->log->createLog($event, $event->doctor_id, VisitLogEnum::DoctorExit);
        return success_template(true);
    }

    public function duration()
    {
        $token = $this->request->input('token');
        $event = EventReserves::where('token_room', $token)->first();
        return success_template($event->duration);
    }


    public function vandarFinish()
    {
        $vandar = new VandarController();
        $token = $vandar->return_token();

        $headers = array(
            "Accept: application/json",
            "Authorization: Bearer " . $token,
        );

        $ch_ = curl_init();
        curl_setopt($ch_, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch_, CURLOPT_URL, 'https://api.vandar.io/v3/business/Sbm/settlement/store');
        curl_setopt($ch_, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch_, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($ch_);
        curl_close($ch_);
        $result = json_decode($result, true);

        $extend_message = $has_partner ? ' - ' . $calendar->partner->name : NULL;

        $transactionDr->user_id = $event->user_id;
        $transactionDr->doctor_id = $user->id;
        $transactionDr->event_id = $event->id;
        $transactionDr->amount = $amount;
        $transactionDr->status = 'paid';
        $transactionDr->partner_id = $has_partner ? $calendar->partner->id : 0;
        $transactionDr->transaction_id = $result['data']['settlement'][0]['transaction_id'];
        $transactionDr->message = 'پرداخت شده با کد شناسایی ' . $result['data']['settlement'][0]['transaction_id'] . $extend_message;
        $transactionDr->save();
        SendSMS::sendTemplateTwo($user->mobile, $event->user()->first()->fullname ?? jdate('Y-m-d', strtotime($event->reserve_time)),
            $result['data']['settlement'][0]['transaction_id'], 'CheckoutDr');

        try {
            VandarTransaction::create([
                'vandar_id' => $result['data']['settlement'][0]['id'],
                'iban_id' => $result['data']['settlement'][0]['iban_id'],
                'transaction_id' => $result['data']['settlement'][0]['transaction_id'],
                'amount' => $result['data']['settlement'][0]['amount']
            ]);
        } catch (\Exception $exception) {
        }



    }
}
