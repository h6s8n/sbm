<?php

namespace App\Http\Controllers\Admin\Bill;

use App\Enums\VisitTypeEnum;
use App\Http\Controllers\Api\v2\vandar\VandarController;
use App\Model\Discount\Discount;
use App\Model\Partners\Partner;
use App\Model\Visit\DoctorCalender;
use App\Model\Wallet\DoctorWallet;
use App\Model\Doctor\DoctorContract;
use App\Model\Visit\EventReserves;
use App\Model\Visit\TransactionCredit;
use App\Model\Visit\TransactionDoctor;
use App\Model\Visit\TransactionReserve;
use App\Model\Wallet\DoctorWalletTransaction;
use App\SendSMS;
use App\User;
use App\Services\Gateways\src\Zibal;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Hekmatinasser\Verta\Verta;
use Illuminate\Support\Collection;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use niklasravnsborg\LaravelPdf\Facades\Pdf as PDF;


class DrBillController extends Controller
{
    protected $request;
    private $zarrin_merchant;
    private $zarrin_terminal_id;
    private $visit_wallet_id;
    private $online_wallet_id;


    public function __construct(Request $request)
    {
        $this->middleware('admin');
        $this->visit_wallet_id  = '1629014';
        //$this->visit_wallet_id  = '1637935';
//        $this->online_wallet_id  = '1637913';
        $this->online_wallet_id  = '1629021';
        date_default_timezone_set("Asia/Tehran");
        $this->request = $request;
        $this->zarrin_merchant = '2f27b240-b085-42d9-815a-04c860d6e39f';
        $this->zarrin_terminal_id = '380236';
        require(base_path('app/jdf.php'));
    }


    public function list()
    {
        $where_array = array();

        $status_list = $this->request->status_list;
        //filter set to query
        $filter_name = trim($this->request->get('filter_user'));
        $filter_dr_mobile = trim($this->request->get('filter_dr_mobile'));

        $array_dr = [];
        if ($filter_name) {

            $users = User::where('approve', '1')
                ->where(function ($query) use ($filter_name) {
                    $query->search($filter_name , false);
                })
                ->orderBy('created_at', 'desc')
                ->select('id')
                ->get();

            if ($users) {
                foreach ($users as $v) {
                    $array_dr[] = $v['id'];
                }
            }

        }
        if ($filter_dr_mobile) {

            $users = User::where('approve', '1')
                ->where('mobile',$filter_dr_mobile)
                ->orderBy('created_at', 'desc')
                ->select('id')
                ->get();

            if ($users) {
                foreach ($users as $v) {
                    $array_dr[] = $v['id'];
                }
            }

        }
        $filter_br = trim($this->request->get('filter_br'));
        $filter_br_mobile = trim($this->request->get('filter_br_mobile'));
        $filter_br_from_ = trim($this->request->get('filter_patient_from_'));
        $filter_br_email = trim($this->request->get('filter_br_email'));
        $array_bi = [];

        if ($filter_br) {

            $users_us = User::where('approve', '2')
                ->where(function ($query) use ($filter_br) {
                    $query->search($filter_br, false);
                })
                ->orderBy('created_at', 'desc')
                ->select('id')
                ->get();

            if ($users_us) {

                foreach ($users_us as $v) {
                    $array_bi[] = $v['id'];
                }


            }

        }
        if ($filter_br_mobile) {

            $users_us = User::where('approve', '2')
                ->where('mobile',$filter_br_mobile)
                ->orderBy('created_at', 'desc')
                ->select('id')
                ->get();

            if ($users_us) {

                foreach ($users_us as $v) {
                    $array_bi[] = $v['id'];
                }


            }

        }

        if ($filter_br_from_) {

            $users_us = User::where('approve', '2')
                ->where('from_',$filter_br_from_)
                ->orderBy('created_at', 'desc')
                ->select('id')
                ->get();

            if ($users_us) {

                foreach ($users_us as $v) {
                    $array_bi[] = $v['id'];
                }


            }

        }
        if ($filter_br_email) {

            $users_us = User::where('approve', '2')
                ->where('email',$filter_br_email)
                ->orderBy('created_at', 'desc')
                ->select('id')
                ->get();

            if ($users_us) {

                foreach ($users_us as $v) {
                    $array_bi[] = $v['id'];
                }


            }

        }

        $filter_visit_status = $this->request->get('filter_visit_status');
        $filter_start_date = $this->request->get('filter_start_date');
        $filter_end_date = $this->request->get('filter_end_date');
        if ($filter_start_date) {

            $date = explode('/', $filter_start_date);
            $date = jalali_to_gregorian($date[0], $date[1], $date[2], '-');
            if ($status_list == 'final' || $filter_visit_status == "end")
                $where_array[] = array(DB::raw('DATE(event_reserves.finish_at)'), '>=', $date);
            else
                $where_array[] = array(DB::raw('DATE(event_reserves.reserve_time)'), '>=', $date);
        }

        if ($filter_end_date) {

            $date = explode('/', $filter_end_date);
            $date = jalali_to_gregorian($date[0], $date[1], $date[2], '-');
            if ($status_list == 'final' || $filter_visit_status == "end")
                $where_array[] = array(DB::raw('DATE(event_reserves.finish_at)'), '<=', $date);
            else
                $where_array[] = array(DB::raw('DATE(event_reserves.reserve_time)'), '<=', $date);
        }

        if ($filter_visit_status) {
            $where_array[] = array('event_reserves.visit_status', $filter_visit_status);
        }
        $filter_status = $this->request->get('filter_status');
        if ($filter_status) {
            $where_array[] = array('transaction_doctors.status', $filter_status);
        }

        $filter_partner = $this->request->get('filter_partner');
        if ($filter_partner) {
            $where_array[] = array('doctor_calenders.partner_id', $filter_partner);
        }

        $filter_status = $this->request->get('filter_status');
        if ($filter_status) {
            $where_array[] = array('transaction_doctors.status', $filter_status);
            $where_array[] = array('transaction_doctors.amount', '>', 0);
        }

//         dd($where_array);

        $request = EventReserves::join('users as dr', 'dr.id', '=', 'event_reserves.doctor_id')
            ->join('users as us', 'us.id', '=', 'event_reserves.user_id')
            ->leftJoin('doctor_calenders', 'doctor_calenders.id', '=', 'event_reserves.calender_id')
            ->leftJoin('transaction_doctors', 'transaction_doctors.event_id', '=', 'event_reserves.id')
            ->where($where_array)
            ->where(function ($query) use ($filter_name ,$array_dr , $array_bi) {
                if ($filter_name || $array_dr) {
                    $query->whereIn('event_reserves.doctor_id', $array_dr);
                }
                if ($array_bi) {
                    $query->whereIn('event_reserves.user_id', $array_bi);
                }
            })
            //->where('transaction_reserves.status' , 'paid')
            ->where(function ($query) use ($status_list) {
                if ($status_list == 'final') {
                    $query->where('event_reserves.visit_status', 'end');
                }
            })
            ->select(
                'event_reserves.id',
                'event_reserves.user_id',
                'event_reserves.calender_id',
                'event_reserves.doctor_id',
                'event_reserves.created_at',
                'event_reserves.finish_at',
                'event_reserves.visit_status',
                'event_reserves.reserve_time',
                'event_reserves.time',
                'event_reserves.calender_id',
                'event_reserves.fa_data',
                'dr.fullname as dr_fullname',
                'dr.account_sheba as dr_sheba',
                'dr.mobile as dr_mobile',
                'us.fullname as us_fullname',
                'us.mobile as us_mobile',
                'us.email as us_email',
                'dr.doctor_nickname',
                'dr.account_sheba as dr_sheba',
                'doctor_calenders.partner_id',
                'doctor_calenders.type',
                'transaction_doctors.status as pay_dr_status',
                'transaction_doctors.amount as pay_amount',
                'transaction_doctors.source as source',
                'transaction_doctors.partner_id as transaction_partner_id',
                'transaction_doctors.message as message',
                'transaction_doctors.updated_at as paid_date'
            )
            ->orderBy('event_reserves.reserve_time', 'DESC');
        //->groupBy('event_reserves.id')
        //->distinct('event_reserves.id')
        if (\request()->input('partner_id') > -1) {
            $request = $request->where('doctor_calenders.partner_id',
                \request()->input('partner_id'));
        }

        if (\request()->has('calendar_type') && \request()->input('calendar_type'))
            $request = $request->whereHas('calendar',function ($query){
                $query->where('type',\request()->input('calendar_type'));
            });


        $request = $request->paginate(35);

        $full_price = 0;
        if ($request) {
            foreach ($request as $item) {
                $full_price += (int)$item['price'];
            }
        }

        $partners = Partner::all();


        $mapping = [
            'no_end' => 'صورت حساب همه نوبت ها',
            'repo' => 'گزارش مالی()',
            'final' => 'پرداختی های پزشکان',
            '' => 'صورت حساب مالی پزشکان'
        ];

        $title = $mapping[$status_list];

        return view('admin/bill/doctor/list', ['request' => $request,
            'full_price' => $full_price,'title' => $title, 'status_list' => $status_list, 'partners' => $partners]);

    }

    public function partners()
    {

        $request = [];

        $filter_partner = $this->request->get('filter_partner' , null);
        $filter_dr_fullname = $this->request->get('filter_doctor' , null);
        $filter_patient_fullname = $this->request->get('filter_patient' , null);
        $partner = Partner::where('id', $filter_partner)->first();

        $filter_from_date = explode('/',$this->request->get('filter_from_date' , null));
        $filter_to_date = explode('/',$this->request->get('filter_to_date' , null));
        $filter_page = $this->request->get('page' , 1);

        $fromDate = count($filter_from_date) == 3 ? jalali_to_gregorian($filter_from_date[0],$filter_from_date[1],$filter_from_date[2],'-').'T00:00:00' : null;
        $toDate =  count($filter_to_date) == 3 ? jalali_to_gregorian($filter_to_date[0],$filter_to_date[1],$filter_to_date[2],'-').'T00:00:00' : null;

        if ($partner) {


            $zibal = new Zibal();

            $data['bankAccount'] = 'IR'.$partner->sheba;
            $data['fromDate'] = $fromDate;
            $data['toDate'] = $toDate;
            $data['page'] = $filter_page;

            $resp = $zibal->report($data);

            $request['prev_page_url'] = null;
            $request['next_page_url'] = null;

            $request['total'] = $resp->total;

                foreach ($resp->data as $data) {
                    $result = NULL;
                    $transaction = TransactionDoctor::join('users as doctor','doctor.id' ,'=','transaction_doctors.doctor_id')
                        ->join('users as patient', 'patient.id','=','transaction_doctors.user_id')
                        ->where('transaction_id',$data->refNumber)
                        ->where(function ($q) use ($filter_dr_fullname,$filter_patient_fullname){
                            if ($filter_dr_fullname) {
                                $q->where('doctor.fullname', 'LIKE', '%' . $filter_dr_fullname . '%');
                            }
                            if ($filter_patient_fullname){
                                $q->where('patient.fullname', 'LIKE', '%' . $filter_patient_fullname . '%');
                            }
                        })
                        ->select('patient.fullname as patient_fullname','doctor.fullname as doctor_fullname')->first();

                    $result = [
                        'id' => $data->refNumber,
                        'dr_fullname' => $transaction['doctor_fullname'],
                        'patient_fullname' => $transaction['patient_fullname'],
                        'status' => $data->status === 0 ? 'موفق' : 'بازگشت به حساب',
                        'created_at' => jdate('Y/m/d',strtotime($data->details[0]->createdAt)),
                        'estimated_deposit_time' =>  $data->persianSettlementDate,
                        'amount' => $data->amount,
                        'receipt_url' => null
                    ];
                    if ($result['dr_fullname']) {
                        $request['data'][] = $result;
                    }
                }

        }
        $partners = Partner::where('sheba','!=' ,NULL)->get();

        return view('admin/bill/doctor/partners', ['request' => $request,'partners' => $partners]);
    }

    public function recent()
    {
        $request = [];
        $dr_sheba = null;
        $filter_dr_fullname = $this->request->get('filter_doctor' , null);
        if ($filter_dr_fullname) {
            $dr = User::where('fullname', 'LIKE', '%' . $filter_dr_fullname . '%')->first();
            $dr_sheba = 'IR' . $dr->account_sheba;
        }
        $filter_patient_fullname = $this->request->get('filter_patient' , null);

        $filter_from_date = explode('/',$this->request->get('filter_from_date' , null));
        $filter_to_date = explode('/',$this->request->get('filter_to_date' , null));
        $filter_page = $this->request->get('page' , 1);
        $size = $this->request->get('per-page' , 30);

        $fromDate = count($filter_from_date) == 3 ? jalali_to_gregorian($filter_from_date[0],$filter_from_date[1],$filter_from_date[2],'-').'T00:00:00' : null;
        $toDate =  count($filter_to_date) == 3 ? jalali_to_gregorian($filter_to_date[0],$filter_to_date[1],$filter_to_date[2],'-').'T00:00:00' : null;


        $zibal = new Zibal();

        $data['bankAccount'] = $dr_sheba;
        $data['fromDate'] = $fromDate;
        $data['toDate'] = $toDate;
        $data['page'] = $filter_page;
        $data['size'] = $size;

        $resp = $zibal->report($data);

        $request['prev_page_url'] = $filter_page == 1 ? null : 'page='.($filter_page - 1);
        $request['next_page_url'] = $resp->total > $size*$filter_page ? 'page='.($filter_page + 1) : null;

        $request['total'] = $resp->total;

        foreach ($resp->data as $data) {

                $result = NULL;
                $transaction = TransactionDoctor::join('users as doctor','doctor.id' ,'=','transaction_doctors.doctor_id')
                    ->join('users as patient', 'patient.id','=','transaction_doctors.user_id')
                    ->where('transaction_id',$data->refNumber)
                    ->where(function ($q) use ($filter_dr_fullname,$filter_patient_fullname){
                        if ($filter_dr_fullname) {
                            $q->where('doctor.fullname', 'LIKE', '%' . $filter_dr_fullname . '%');
                        }
                        if ($filter_patient_fullname){
                            $q->where('patient.fullname', 'LIKE', '%' . $filter_patient_fullname . '%');
                        }
                    })
                    ->select('patient.fullname as patient_fullname','doctor.fullname as doctor_fullname')->first();

                $result = [
                    'id' => $data->refNumber,
                    'dr_fullname' => $transaction['doctor_fullname'],
                    'patient_fullname' => $transaction['patient_fullname'],
                    'status' => $data->status === 0 ? 'موفق' : 'بازگشت به حساب',
                    'created_at' => jdate('Y/m/d',strtotime($data->details[0]->createdAt)),
                    'estimated_deposit_time' =>  $data->persianSettlementDate,
                    'amount' => $data->amount,
                    'receipt_url' => null
                ];
                if ($result['dr_fullname']) {
                    $request['data'][] = $result;
                }
            }



        return view('admin/bill/doctor/transactions', ['request' => $request]);
    }


    public function pay()
    {
        $where_array = array();

        $event_id = $this->request->event_id;


        $request = EventReserves::leftJoin('transaction_doctors', 'transaction_doctors.event_id', '=', 'event_reserves.id')
            ->where('event_reserves.id', $event_id)
            ->select(
                'event_reserves.id',
                'event_reserves.created_at',
                'event_reserves.finish_at',
                'event_reserves.visit_status',
                'event_reserves.reserve_time',
                'event_reserves.time',
                'event_reserves.calender_id',
                'event_reserves.fa_data',
                'event_reserves.doctor_id',
                'event_reserves.user_id',
                'transaction_doctors.id as pay_id',
                'transaction_doctors.status as pay_dr_status',
                'transaction_doctors.amount as pay_amount'
            )
            ->orderBy('event_reserves.reserve_time', 'DESC')
            ->first();
        if ($request instanceof EventReserves && $request->pay_dr_status == 'pending') {
            $amount_visit = 0;
            $transaction = TransactionReserve::where('calender_id', $request->calender_id)
                ->where('doctor_id', $request->doctor_id)
                ->where('user_id', $request->user_id)
                ->first();
            if ($transaction) {
                $amount_visit = $transaction->amount;
            } else {
                $calender = DoctorCalender::where('id', $request->calender_id)->first();
                if ($calender) {
                    $amount_visit = $calender->price;
                }
            }

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
            $calendar = DoctorCalender::find($request->calender_id);
            if ($calendar->type == VisitTypeEnum::type('in-person')){
                $amount = $calendar->price - 49000;
            }else{
                $amount = $calendar->price;
            }
            $dr_trans = TransactionDoctor::where('id', $request->pay_id)->first();
            if ($amount > 0) {
                $user = User::where('id', $request->doctor_id)->first();
                if ($dr_trans && $dr_trans->status !== 'paid' && $user && $user->account_sheba) {
                    $has_partner = $calendar->partner_id > 0 && $calendar->partner->sheba;
                    $sheba = $has_partner ? $calendar->partner->sheba : $user->account_sheba;
                    $account_sheba = str_replace(' ', '', $user->account_sheba);
                    $doctor_paid = $dr_trans->amount/10; //just amount for zibal
                    $use_zibal = true;



                    $patient = User::where('id',$request->user_id)->first();

                    // $zibal = new Zibal();
                    // $zdata = [
                    //     'amount' =>  $doctor_paid*10,
                    //     'bankAccount' =>  'IR' . $sheba,
                    //     'wallet_id' => $this->visit_wallet_id,
                    //     'description' => ' بیمار: ' . $patient->fullname . ' - پزشک: ' . $user->fullname .' - تاریخ ویزیت: ' . $request->fa_data . ' - ساعت ویزیت: ' . $request->time,
                    //     'uniqueCode' => (string)uniqid('', false),
                    // ];

                    
                    
                    if ($doctor_paid > 0)
                    {

                        ###############################################################
                        $result = (new \App\Services\Gateways\src\PayStarWallet())->pay(
                            $request->pay_id,
                            $doctor_paid*10,
                            'IR' . $sheba,
                            '', // firstname
                            '', // lastname
                        );
                        ###############################################################


                        if ($result->ok) {

                            $settlement = $result->data->created_settlements[0];

                            $extend_message = $has_partner ? ' - ' . $calendar->partner->name : NULL;
                            
                            $dr_trans->user_id = $request->user_id;
                            $dr_trans->doctor_id = $user->id;
                            $dr_trans->event_id = $request->id;
                            $dr_trans->amount = $doctor_paid*10;
                            $dr_trans->status = 'paid';
                            $dr_trans->partner_id = $has_partner ? $calendar->partner->id : 0;
                            $dr_trans->transaction_id = $settlement->id;
                            $dr_trans->receipt = null;// $settlement->data->receipt
                            $dr_trans->message = 'پرداخت شده با کد شناسایی ' . $settlement->id . $extend_message;
                            $dr_trans->save();

                            if ($user->mobile) {
                                SendSMS::sendTemplateTwo(
                                    $user->mobile,
                                    $request->user()->first()->fullname ?? jdate('Y-m-d', strtotime($request->reserve_time)),
                                    $settlement->id,
                                    'CheckoutDr');
                            }
                            return back()->with('success', 'صورت حساب پرداخت شد، کد شناسایی ' . $settlement->id)->withInput();
                        
                        } else {
                            return back()->with('error', $result->message)->withInput();
                        }

                    } else {
                        return back()->with('error', 'پرداخت با مشکل مواجه شده است.')->withInput();
                    }

                    



                } else if ($dr_trans && $dr_trans->status == 'paid') {
                    return back()->with('error', 'این پرداخت قبلا انجام شده است.')->withInput();
                } else if ($user && !$user->account_sheba) {
                    return back()->with('error', 'شماره کارت پزشک تعریف نشده است.')->withInput();
                }

            } else {

                if ($dr_trans) {
                    $dr_trans->status = 'paid';
                    $dr_trans->amount = $amount;
                    $dr_trans->updated_at = date('Y-m-d h:i:s');
                    $dr_trans->message = 'حق ویزیت کم تر از کارمزد بود و تمام مبلغ به عنوان کارمزد کم شد.';
                    $dr_trans->save();

                    return back()->with('success', 'حق ویزیت کم تر از کارمزد بود و تمام مبلغ به عنوان کارمزد کم شد.')->withInput();
                }
            }

        }

        return back()->with('error', 'اطلاعات پرداخت معتبر نمی باشد.')->withInput();

    }

    public function zibalpay()
    {
        if($use_zibal){
            $patient = User::where('id',$request->user_id)->first();

            $zibal = new Zibal();
            $zdata = [
                'amount' =>  $doctor_paid*10,
                'bankAccount' =>  'IR' . $sheba,
                'wallet_id' => $this->visit_wallet_id,
                'description' => ' بیمار: ' . $patient->fullname . ' - پزشک: ' . $user->fullname .' - تاریخ ویزیت: ' . $request->fa_data . ' - ساعت ویزیت: ' . $request->time,
                'uniqueCode' => (string)uniqid('', false),
            ];
            if ($doctor_paid > 0) {
                $result = $zibal->checkout($zdata);
            } else {
//                        $result->result = 0;
            }
            //  try {
            // DB::beginTransaction();
            if ($result->result == 1) {

                $extend_message = $has_partner ? ' - ' . $calendar->partner->name : NULL;

                $dr_trans->user_id = $request->user_id;
                $dr_trans->doctor_id = $user->id;
                $dr_trans->event_id = $request->id;
                $dr_trans->amount = $doctor_paid*10;
                $dr_trans->status = 'paid';
                $dr_trans->partner_id = $has_partner ? $calendar->partner->id : 0;
                $dr_trans->transaction_id = $result->data->id;
                $dr_trans->receipt = $result->data->receipt;
                $dr_trans->message = 'پرداخت شده با کد شناسایی ' . $result->data->id . $extend_message;
                $dr_trans->save();
                if ($user->mobile) {
                    SendSMS::sendTemplateTwo($user->mobile, $request->user()->first()->fullname ?? jdate('Y-m-d', strtotime($request->reserve_time)),
                        $result->data->id, 'CheckoutDr');
                }
                return back()->with('success', 'صورت حساب پرداخت شد، کد شناسایی ' .
                    $result->data->id)->withInput();
            } else {
                dd($result);
                return redirect()->back()->withErrors('پرداخت با مشکل مواجه شده است');
            }
            // DB::commit();
            //  }catch (\Exception $exception){
            //  DB::rollBack();
            //    dd($result);
            //  }

            //try {
            // DB::beginTransaction();
            if ($result->result == 1) {

                $extend_message = $has_partner ? ' - ' . $calendar->partner->name : NULL;

                $dr_trans->user_id = $request->user_id;
                $dr_trans->doctor_id = $user->id;
                $dr_trans->event_id = $request->id;
                $dr_trans->amount = $doctor_paid*10;
                $dr_trans->status = 'paid';
                $dr_trans->partner_id = $has_partner ? $calendar->partner->id : 0;
                $dr_trans->transaction_id = $result->data->id;
                $dr_trans->message = 'پرداخت شده با کد شناسایی ' . $result->data->id . $extend_message;
                $dr_trans->save();
                if ($user->mobile) {
                    SendSMS::sendTemplateTwo($user->mobile, $request->user()->first()->fullname ?? jdate('Y-m-d', strtotime($request->reserve_time)),
                        $result->data->id, 'CheckoutDr');
                }
                return back()->with('success', 'صورت حساب پرداخت شد، کد شناسایی ' .
                    $result->data->id)->withInput();
            } else {
                dd($result);
                return redirect()->back()->withErrors('پرداخت با مشکل مواجه شده است');
            }
            // DB::commit();
            //  }catch (Exception $exception){
            //  DB::rollBack();
            //    dd($result);
            // }

        } else {
            if ($doctor_paid > 0) {

//                        $result = $zibal->checkout($zdata);

                $ch_ = curl_init();
                curl_setopt($ch_, CURLOPT_HTTPHEADER, $headers);
                curl_setopt($ch_, CURLOPT_URL, 'https://api.vandar.io/v3/business/Sbm/settlement/store');
                curl_setopt($ch_, CURLOPT_POSTFIELDS, $data);
                curl_setopt($ch_, CURLOPT_RETURNTRANSFER, true);

                $result = curl_exec($ch_);
                curl_close($ch_);
                $result = json_decode($result, true);
            } else {
                $result->result = 0;
            }
            if ($result['status'] == 1) {

                $extend_message = $has_partner ? ' - ' . $calendar->partner->name : NULL;

                $dr_trans->user_id = $request->user_id;
                $dr_trans->doctor_id = $user->id;
                $dr_trans->event_id = $request->id;
                $dr_trans->amount = $amount;
                $dr_trans->status = 'paid';
                $dr_trans->partner_id = $has_partner ? $calendar->partner->id : 0;
                $dr_trans->transaction_id = $result['data']['settlement'][0]['transaction_id'];
                $dr_trans->message = 'پرداخت شده با کد شناسایی ' . $result['data']['settlement'][0]['transaction_id'] . $extend_message;
                $dr_trans->save();

                if ($user->mobile) {
                    SendSMS::sendTemplateTwo($user->mobile, $request->user()->first()->fullname ?? jdate('Y-m-d', strtotime($request->reserve_time)),
                        $result['data']['settlement'][0]['transaction_id'], 'CheckoutDr');
                }
                return back()->with('success', 'صورت حساب پرداخت شد، کد شناسایی ' .
                    $result['data']['settlement'][0]['transaction_id'])->withInput();
            } else {
                dd($result);
                return redirect()->back()->withErrors('پرداخت با مشکل مواجه شده است');
            }
        }
    }

    public function zarrin_pay()
    {
        $cl = new Client(['headers' => ["Accept" => "application/json",]]);
        $bank_account = json_decode($cl->get(route("graphql",['type' => 'bank_account_add','data' => 'IR' . $account_sheba]))->getBody()->getContents(), true);

        if ($bank_account['errors']){
            $bank_accounts = json_decode($cl->get(route("graphql",['type' => 'bank_accounts']))->getBody()->getContents(), true)['data']['BankAccounts'];

            foreach ($bank_accounts as $account){
                if ($account['iban'] == 'IR' . $account_sheba)
                {
                    $bank_account_id = $account['id'];
                }
            }
        }else{
            $bank_account_id = $bank_account['data']['0']['BankAccountAdd']['id'];
        }

        $payout = json_decode($cl->get(route("graphql",['type' => 'payout','data' =>
            $this->zarrin_terminal_id.'&'.$bank_account_id .'&'.abs($dr_trans->amount)
        ]))->getBody()->getContents(), true);

        if (isset($payout['errors'])) {
            dd($payout['errors']);
        }else{

            $result = $payout['data']['PayoutAdd'];
            $extend_message = $has_partner ? ' - ' . $calendar->partner->name : NULL;

            $dr_trans->user_id = $request->user_id;
            $dr_trans->doctor_id = $user->id;
            $dr_trans->event_id = $request->id;
            $dr_trans->status = 'paid';
            $dr_trans->partner_id = $has_partner ? $calendar->partner->id : 0;
            $dr_trans->transaction_id = $result['id'];
            $dr_trans->receipt = 'https://next.zarinpal.com/payout/receipt/'.$result['url_code'];
            $dr_trans->message = 'پرداخت شده با کد شناسایی ' . $result['id'] . $extend_message;
            $dr_trans->save();
            if ($user->mobile) {
                SendSMS::sendTemplateTwo($user->mobile, $request->user()->first()->fullname ?? jdate('Y-m-d', strtotime($request->reserve_time)),
                    $result['id'], 'CheckoutDr');
            }
            return back()->with('success', 'صورت حساب پرداخت شد، کد شناسایی ' .
                $result['id'])->withInput();
        }
    }

    public function export()
    {
        $where_array = array();

        $status_list = $this->request->status_list;

        //filter set to query
        $filter_name = trim($this->request->get('filter_user'));
        $array_dr = [];
        if ($filter_name) {

            $users = User::where('approve', '1')
                ->where(function ($query) use ($filter_name) {
                    $query->search($filter_name, false);
                })
                ->orderBy('created_at', 'desc')
                ->select('id')
                ->get();
            if ($users) {
                foreach ($users as $v) {
                    $array_dr[] = $v['id'];
                }


            }

        }
        $filter_br = trim($this->request->get('filter_br'));
        $array_bi = [];
        if ($filter_br) {

            $users_us = User::where('approve', '2')
                ->where(function ($query) use ($filter_br) {
                    $query->search($filter_br, false);
                })
                ->orderBy('created_at', 'desc')
                ->select('id')
                ->get();

            if ($users_us) {

                foreach ($users_us as $v) {
                    $array_bi[] = $v['id'];
                }


            }

        }

        $filter_visit_status = $this->request->get('filter_visit_status');
        $filter_start_date = $this->request->get('filter_start_date');
        $filter_end_date = $this->request->get('filter_end_date');
        if ($filter_start_date) {

            $date = explode('/', $filter_start_date);
            $date = jalali_to_gregorian($date[0], $date[1], $date[2], '-');
            if ($status_list == 'final' || $filter_visit_status == "end")
                $where_array[] = array(DB::raw('DATE(event_reserves.finish_at)'), '>=', $date);
            else
                $where_array[] = array(DB::raw('DATE(event_reserves.reserve_time)'), '>=', $date);
        }

        if ($filter_end_date) {

            $date = explode('/', $filter_end_date);
            $date = jalali_to_gregorian($date[0], $date[1], $date[2], '-');
            if ($status_list == 'final' || $filter_visit_status == "end")
                $where_array[] = array(DB::raw('DATE(event_reserves.finish_at)'), '<=', $date);
            else
                $where_array[] = array(DB::raw('DATE(event_reserves.reserve_time)'), '<=', $date);
        }

        if ($filter_visit_status) {
            $where_array[] = array('event_reserves.visit_status', $filter_visit_status);
        }
        $filter_status = $this->request->get('filter_status');
        if ($filter_status) {
            $where_array[] = array('transaction_doctors.status', $filter_status);
        }

        $filter_partner = $this->request->get('partner_id');
        if ($filter_partner > 0) {
            $where_array[] = array('doctor_calenders.partner_id', $filter_partner);
        }

        $filter_status = $this->request->get('filter_status');
        if ($filter_status) {
            $where_array[] = array('transaction_doctors.status', $filter_status);
            $where_array[] = array('transaction_doctors.amount', '>', 0);
        }

        $request = EventReserves::join('users as dr', 'dr.id', '=', 'event_reserves.doctor_id')
            ->join('users as us', 'us.id', '=', 'event_reserves.user_id')
            ->leftJoin('transaction_doctors', 'transaction_doctors.event_id', '=', 'event_reserves.id')
            ->leftJoin('doctor_calenders', 'event_reserves.calender_id', '=', 'doctor_calenders.id')
            ->leftJoin('partners', 'doctor_calenders.partner_id', '=', 'partners.id')
            ->where($where_array)
            ->where(function ($query) use ($array_dr, $array_bi) {
                if ($array_dr) {
                    $query->whereIn('event_reserves.doctor_id', $array_dr);
                }
                if ($array_bi) {
                    $query->whereIn('event_reserves.user_id', $array_bi);
                }
            })
//            ->where('transaction_reserves.status' , 'paid')
            ->where(function ($query) use ($status_list) {
                if ($status_list == 'final') {
                    $query->where('event_reserves.visit_status', 'end');
                }
            });


            if (\request()->has('calendar_type') && \request()->input('calendar_type'))
                $request = $request->whereHas('calendar',function ($query){
                    $query->where('type',\request()->input('calendar_type'));
                });
            $request = $request->select(
                'event_reserves.id',
                'event_reserves.user_id',
                'event_reserves.calender_id',
                'event_reserves.doctor_id',
                'event_reserves.created_at',
                'event_reserves.finish_at',
                'event_reserves.visit_status',
                'event_reserves.reserve_time',
                'event_reserves.time',
                'event_reserves.calender_id',
                'event_reserves.fa_data',
                'dr.fullname as dr_fullname',
                'dr.account_sheba as dr_sheba',
                'us.fullname as us_fullname',
                'dr.doctor_nickname',
                'dr.account_sheba as dr_sheba',
                'transaction_doctors.status as pay_dr_status',
                'transaction_doctors.amount as pay_amount',
                'partners.name as partner_name'
            )
            ->orderBy('event_reserves.reserve_time', 'DESC')
//            ->limit(500)
            ->get();

        // Create new PHPExcel object
        $objPHPExcel = new \PHPExcel();

        // Set properties
        $objPHPExcel->getProperties()->setCreator("Maarten Balliauw");
        $objPHPExcel->getProperties()->setLastModifiedBy("Maarten Balliauw");
        $objPHPExcel->getProperties()->setTitle("Office 2007 XLSX Test Document");
        $objPHPExcel->getProperties()->setSubject("Office 2007 XLSX Test Document");
        $objPHPExcel->getProperties()->setDescription("Test document for Office 2007 XLSX, generated using PHP classes.");


        // Add some data
        $objPHPExcel->setActiveSheetIndex(0);
        $objPHPExcel->getActiveSheet()->SetCellValue('A1', 'نام پزشک');
        $objPHPExcel->getActiveSheet()->SetCellValue('B1', 'نام بیمار');
        $objPHPExcel->getActiveSheet()->SetCellValue('C1', 'تاریخ شروع');
        $objPHPExcel->getActiveSheet()->SetCellValue('D1', 'تاریخ پایان');
        $objPHPExcel->getActiveSheet()->SetCellValue('E1', 'مبلغ کل');
        $objPHPExcel->getActiveSheet()->SetCellValue('F1', 'مبلغ پرداختی');
        $objPHPExcel->getActiveSheet()->SetCellValue('G1', 'وضعیت پرداخت');
        $objPHPExcel->getActiveSheet()->SetCellValue('H1', 'وضعیت ویزیت');
        $objPHPExcel->getActiveSheet()->SetCellValue('I1', 'بیمارستان');

        $num = 2;
        if ($request) {
            foreach ($request as $item) {
                $price = $item->UserTransaction('paid')->first() ? $item->UserTransaction('paid')->first()->amount : 0;
                $fullname = ($item['fullname']) ? $item['doctor_nickname'] . ' ' . $item['fullname'] : '-';

                $objPHPExcel->getActiveSheet()->SetCellValue('A' . $num, $item['doctor_nickname'] . ' ' . $item['dr_fullname']);
                $objPHPExcel->getActiveSheet()->SetCellValue('B' . $num, $item['us_fullname']);
                $objPHPExcel->getActiveSheet()->SetCellValue('C' . $num,
                    jdate('d F Y ساعت H:i', strtotime($item['reserve_time'])));
                $objPHPExcel->getActiveSheet()->SetCellValue('D' . $num, ($item['visit_status'] === 'end') ?
                    jdate('d F Y', strtotime($item['finish_at'])) : '-');
                $objPHPExcel->getActiveSheet()->SetCellValue('E' . $num, number_format($price));
                $objPHPExcel->getActiveSheet()->SetCellValue('F' . $num, number_format($item['pay_amount']));
                $objPHPExcel->getActiveSheet()->SetCellValue('G' . $num, $item['pay_dr_status']);
                $objPHPExcel->getActiveSheet()->SetCellValue('H' . $num, $item['visit_status']);
                $objPHPExcel->getActiveSheet()->SetCellValue('I' . $num, $item['partner_name']);

                $num++;
            }
        }


        // Rename sheet
        $objPHPExcel->getActiveSheet()->setTitle('bill list');
//        $objPHPExcel->se

        // Save Excel 2007 f
        $objWriter = new \PHPExcel_Writer_Excel2007($objPHPExcel);

        $path_blank = str_replace(get_ev('path_live'), 'httpdocs/upload', base_path('files'));

        $fileName = time() . '_save.xlsx';

        $objWriter->save("{$path_blank}/{$fileName}");

        return redirect('https://sandbox.sbm24.net/upload/files/' . $fileName);
    }

    public function pdfInvoice()
    {
        $where_array = array();

        $status_list = $this->request->status_list;

        //filter set to query
        $filter_name = trim($this->request->get('filter_user'));
        $array_dr = [];
        if ($filter_name) {

            $users = User::where('approve', '1')
                ->where(function ($query) use ($filter_name) {
                    $query->search($filter_name, false);
                })
                ->orderBy('created_at', 'desc')
                ->select('id')
                ->get();
            if ($users) {
                foreach ($users as $v) {
                    $array_dr[] = $v['id'];
                }


            }

        }
        $filter_br = trim($this->request->get('filter_br'));
        $array_bi = [];
        if ($filter_br) {

            $users_us = User::where('approve', '2')
                ->where(function ($query) use ($filter_br) {
                    $query->search($filter_br, false);
                })
                ->orderBy('created_at', 'desc')
                ->select('id')
                ->get();

            if ($users_us) {

                foreach ($users_us as $v) {
                    $array_bi[] = $v['id'];
                }


            }

        }

        $filter_visit_status = $this->request->get('filter_visit_status');
        $filter_start_date = $this->request->get('filter_start_date');
        $filter_end_date = $this->request->get('filter_end_date');
        if ($filter_start_date) {

            $date = explode('/', $filter_start_date);
            $date = jalali_to_gregorian($date[0], $date[1], $date[2], '-');
            if ($status_list == 'final' || $filter_visit_status == "end")
                $where_array[] = array(DB::raw('DATE(event_reserves.finish_at)'), '>=', $date);
            else
                $where_array[] = array(DB::raw('DATE(event_reserves.reserve_time)'), '>=', $date);
        }

        if ($filter_end_date) {

            $date = explode('/', $filter_end_date);
            $date = jalali_to_gregorian($date[0], $date[1], $date[2], '-');
            if ($status_list == 'final' || $filter_visit_status == "end")
                $where_array[] = array(DB::raw('DATE(event_reserves.finish_at)'), '<=', $date);
            else
                $where_array[] = array(DB::raw('DATE(event_reserves.reserve_time)'), '<=', $date);
        }

        if ($filter_visit_status) {
            $where_array[] = array('event_reserves.visit_status', $filter_visit_status);
        }
        $filter_status = $this->request->get('filter_status');
        if ($filter_status) {
            $where_array[] = array('transaction_doctors.status', $filter_status);
        }

        $filter_partner = $this->request->get('filter_partner');
        if ($filter_partner) {
            $where_array[] = array('doctor_calenders.partner_id', $filter_partner);
        }

        $filter_status = $this->request->get('filter_status');
        if ($filter_status) {
            $where_array[] = array('transaction_doctors.status', $filter_status);
            $where_array[] = array('transaction_doctors.amount', '>', 0);
        }

        $request = EventReserves::join('users as dr', 'dr.id', '=', 'event_reserves.doctor_id')
            ->join('users as us', 'us.id', '=', 'event_reserves.user_id')
            ->leftJoin('transaction_doctors', 'transaction_doctors.event_id', '=', 'event_reserves.id')
            ->leftJoin('doctor_calenders', 'event_reserves.calender_id', '=', 'doctor_calenders.id')
            ->leftJoin('partners', 'doctor_calenders.partner_id', '=', 'partners.id')
            ->where($where_array)
            ->where(function ($query) use ($array_dr, $array_bi) {
                if ($array_dr) {
                    $query->whereIn('event_reserves.doctor_id', $array_dr);
                }
                if ($array_bi) {
                    $query->whereIn('event_reserves.user_id', $array_bi);
                }
            })->where(function ($query) use ($status_list) {
                if ($status_list == 'final') {
                    $query->where('event_reserves.visit_status', 'end');
                }
            })
            ->select(
                'event_reserves.id',
                'event_reserves.user_id',
                'event_reserves.fa_data',
                'event_reserves.doctor_id',
                'us.fullname as us_fullname'
            )
            ->orderBy('event_reserves.reserve_time', 'DESC')
            ->get();

        $data = [
            'invoices' => $request,
            'drname' => $filter_name
        ];
        $pdf = PDF::loadView('in', $data);
//        $pdf->SetProtection(['copy', 'print'], '', 'pass');
        return $pdf->stream('document.pdf');
    }

    public function wallet(Request $request)
    {

            $where_array = array();

            //filter set to query
            $filter_name = trim($this->request->get('filter_name'));
            $filter_dr_mobile = trim($this->request->get('filter_mobile'));
            $userMobile = $request->get('filter_user_mobile');

            $array_dr = [];
            if ($filter_name) {

                $users = User::where('approve', '1')
                    ->where(function ($query) use ($filter_name) {
                        $query->search($filter_name, false);
                    })
                    ->orderBy('created_at', 'desc')
                    ->select('id')
                    ->get();

                if ($users) {
                    foreach ($users as $v) {
                        $array_dr[] = $v['id'];
                    }
                }

            }
            if ($filter_dr_mobile) {

                $users = User::where('approve', '1')
                    ->where('mobile', $filter_dr_mobile)
                    ->orderBy('created_at', 'desc')
                    ->select('id')
                    ->get();

                if ($users) {
                    foreach ($users as $v) {
                        $array_dr[] = $v['id'];
                    }
                }

            }

            $filter_status = $this->request->get('filter_status');
            if ($filter_status) {
                $where_array[] = array('doctor_wallets.status', $filter_status);
            }

            $filter_service = $this->request->get('filter_service');
            if ($filter_service) {
                $where_array[] = array('doctor_wallets.service', $filter_service);
            }
            $filter_transId = $this->request->get('filter_transId');
            if ($filter_transId) {
                $where_array[] = array('doctor_wallets.transId', $filter_transId);
            }
//            else{
//                $where_array[] = array('doctor_wallets.transId', NULL);
//            }

            $filter_settlement_type = $this->request->get('filter_settlement_type');
            if ($filter_settlement_type) {
                $where_array[] = array('doctor_wallets.settlement_type', $filter_settlement_type);
            }

            $filter_start_date = $this->request->get('filter_start_date');
            $filter_end_date = $this->request->get('filter_end_date');
            if ($filter_start_date) {

                $date = explode('/', $filter_start_date);
                $date = jalali_to_gregorian($date[0], $date[1], $date[2], '-');

                $where_array[] = array(DB::raw('DATE(doctor_wallets.paid_at)'), '>=', $date);
            }

            if ($filter_end_date) {

                $date = explode('/', $filter_end_date);
                $date = jalali_to_gregorian($date[0], $date[1], $date[2], '-');

                $where_array[] = array(DB::raw('DATE(doctor_wallets.paid_at)'), '<=', $date);
            }

            $request = DoctorWallet::with(['user:id,fullName,mobile', 'doctor:id,fullName,mobile'])
                ->orderBy('created_at', 'DESC')
                ->where($where_array)
                ->where(function ($query) use ($filter_name, $array_dr) {
                    if ($filter_name || $array_dr) {
                        $query->whereIn('doctor_wallets.doctor_id', $array_dr);
                    }
                })
                ->when($userMobile, function ($query) use ($userMobile) {
                    $query->whereHas('user', function (\Illuminate\Database\Eloquent\Builder $query) use ($userMobile) {
                        $query->where('mobile', 'LIKE', '%' . $userMobile . '%');
                    });
                })
//                ->dump();
                ->paginate(10);

            $balance = NULL;
            if(($filter_dr_mobile || $filter_name) && $request[0]->doctor) {
                $balance = json_decode($this->accountBalance($request[0]->doctor->id)->getContent())->data;
            }

            $title = 'درگاه سلامت';
            $payment_type = 'wallet';

            return view('admin/bill/doctor/wallet', ['request' => $request , 'account_balance' => $balance , 'title' => $title , 'payment_type' => $payment_type]);

    }
    public function cod()
    {


            $where_array = array();

            //filter set to query
            $filter_name = trim($this->request->get('filter_name'));
            $filter_dr_mobile = trim($this->request->get('filter_mobile'));

            $array_dr = [];
            if ($filter_name) {

                $users = User::where('approve', '1')
                    ->where(function ($query) use ($filter_name) {
                        $query->search($filter_name, false);
                    })
                    ->orderBy('created_at', 'desc')
                    ->select('id')
                    ->get();

                if ($users) {
                    foreach ($users as $v) {
                        $array_dr[] = $v['id'];
                    }
                }

            }
            if ($filter_dr_mobile) {

                $users = User::where('approve', '1')
                    ->where('mobile', $filter_dr_mobile)
                    ->orderBy('created_at', 'desc')
                    ->select('id')
                    ->get();

                if ($users) {
                    foreach ($users as $v) {
                        $array_dr[] = $v['id'];
                    }
                }

            }

            $filter_status = $this->request->get('filter_status');
            if ($filter_status) {
                $where_array[] = array('doctor_wallets.status', $filter_status);
            }

            $filter_service = $this->request->get('filter_service');
            if ($filter_service) {
                $where_array[] = array('doctor_wallets.service', $filter_service);
            }
            $filter_transId = $this->request->get('filter_transId');
            if ($filter_transId) {
                $where_array[] = array('doctor_wallets.transId', $filter_transId);
            }
//            else{
//                $where_array[] = array('doctor_wallets.transId', NULL);
//            }

            $filter_settlement_type = $this->request->get('filter_settlement_type');
            if ($filter_settlement_type) {
                $where_array[] = array('doctor_wallets.settlement_type', $filter_settlement_type);
            }

            $filter_start_date = $this->request->get('filter_start_date');
            $filter_end_date = $this->request->get('filter_end_date');
            if ($filter_start_date) {

                $date = explode('/', $filter_start_date);
                $date = jalali_to_gregorian($date[0], $date[1], $date[2], '-');

                $where_array[] = array(DB::raw('DATE(doctor_wallets.paid_at)'), '>=', $date);
            }

            if ($filter_end_date) {

                $date = explode('/', $filter_end_date);
                $date = jalali_to_gregorian($date[0], $date[1], $date[2], '-');

                $where_array[] = array(DB::raw('DATE(doctor_wallets.paid_at)'), '<=', $date);
            }

            $request = DoctorWallet::with(['user:id,fullName,mobile', 'doctor:id,fullName,mobile'])
                ->orderBy('created_at', 'DESC')
                ->where($where_array)
                ->where('payment_type','COD')
                ->where(function ($query) use ($filter_name, $array_dr) {
                    if ($filter_name || $array_dr) {
                        $query->whereIn('doctor_wallets.doctor_id', $array_dr);
                    }
                })
                ->paginate(10);
//dd($request);
            $balance = NULL;
            if(($filter_dr_mobile || $filter_name) && $request[0]->doctor) {
                $balance = json_decode($this->accountBalance($request[0]->doctor->id,'COD')->getContent())->data;
            }
            $title = 'پرداخت در محل';
            $payment_type = 'cod';

            return view('admin/bill/doctor/wallet', ['request' => $request , 'account_balance' => $balance , 'payment_type' => $payment_type, 'title' => $title]);

    }

    public function walletExport()
    {
        $where_array = array();
        $type = $this->request->get('payment_type','wallet');


        //filter set to query
        $filter_name = trim($this->request->get('filter_name'));
        $filter_dr_mobile = trim($this->request->get('filter_mobile'));

        $array_dr = [];
        if ($filter_name) {

            $users = User::where('approve', '1')
                ->where(function ($query) use ($filter_name) {
                    $query->search($filter_name, false);
                })
                ->orderBy('created_at', 'desc')
                ->select('id')
                ->get();

            if ($users) {
                foreach ($users as $v) {
                    $array_dr[] = $v['id'];
                }
            }

        }
        if ($filter_dr_mobile) {

            $users = User::where('approve', '1')
                ->where('mobile', $filter_dr_mobile)
                ->orderBy('created_at', 'desc')
                ->select('id')
                ->get();

            if ($users) {
                foreach ($users as $v) {
                    $array_dr[] = $v['id'];
                }
            }

        }

        $filter_status = $this->request->get('filter_status');
        if ($filter_status) {
            $where_array[] = array('doctor_wallets.status', $filter_status);
        }

        $filter_service = $this->request->get('filter_service');
        if ($filter_service) {
            $where_array[] = array('doctor_wallets.service', $filter_service);
        }
        $filter_transId = $this->request->get('filter_transId');
        if ($filter_transId) {
            $where_array[] = array('doctor_wallets.transId', $filter_transId);
        }
//            else{
//                $where_array[] = array('doctor_wallets.transId', NULL);
//            }

        $filter_settlement_type = $this->request->get('filter_settlement_type');
        if ($filter_settlement_type) {
            $where_array[] = array('doctor_wallets.settlement_type', $filter_settlement_type);
        }

        $filter_start_date = $this->request->get('filter_start_date');
        $filter_end_date = $this->request->get('filter_end_date');
        if ($filter_start_date) {

            $date = explode('/', $filter_start_date);
            $date = jalali_to_gregorian($date[0], $date[1], $date[2], '-');

            $where_array[] = array(DB::raw('DATE(doctor_wallets.paid_at)'), '>=', $date);
        }

        if ($filter_end_date) {

            $date = explode('/', $filter_end_date);
            $date = jalali_to_gregorian($date[0], $date[1], $date[2], '-');

            $where_array[] = array(DB::raw('DATE(doctor_wallets.paid)'), '<=', $date);
        }

        $request = DoctorWallet::with(['user:id,fullName,mobile', 'doctor:id,fullName,mobile'])
            ->orderBy('created_at', 'DESC')
            ->where($where_array)
            ->wherePaymentType($type)
            ->where(function ($query) use ($filter_name, $array_dr) {
                if ($filter_name || $array_dr) {
                    $query->whereIn('doctor_wallets.doctor_id', $array_dr);
                }
            })
            ->get();

        // Create new PHPExcel object
        $objPHPExcel = new \PHPExcel();

        // Set properties
        $objPHPExcel->getProperties()->setCreator("Maarten Balliauw");
        $objPHPExcel->getProperties()->setLastModifiedBy("Maarten Balliauw");
        $objPHPExcel->getProperties()->setTitle("Office 2007 XLSX Test Document");
        $objPHPExcel->getProperties()->setSubject("Office 2007 XLSX Test Document");
        $objPHPExcel->getProperties()->setDescription("Test document for Office 2007 XLSX, generated using PHP classes.");


        // Add some data
        $objPHPExcel->setActiveSheetIndex(0);
        $objPHPExcel->getActiveSheet()->SetCellValue('A1', 'نام پزشک');
        $objPHPExcel->getActiveSheet()->SetCellValue('B1', 'تاریخ درخواست');
        $objPHPExcel->getActiveSheet()->SetCellValue('C1', 'تاریخ پرداخت');
        $objPHPExcel->getActiveSheet()->SetCellValue('D1', 'مبلغ (ریال)');
        $objPHPExcel->getActiveSheet()->SetCellValue('H1', 'کارمزد بانکی (ریال)');
        $objPHPExcel->getActiveSheet()->SetCellValue('I1', 'کارمزد خدمت (ریال)');
        $objPHPExcel->getActiveSheet()->SetCellValue('J1', 'سهم پزشک (ریال)');
        $objPHPExcel->getActiveSheet()->SetCellValue('E1', 'وضعیت');
        $objPHPExcel->getActiveSheet()->SetCellValue('F1', 'شناسه پرداخت');
        $objPHPExcel->getActiveSheet()->SetCellValue('G1', 'نوع تسویه');

        $num = 2;
        if ($request) {
            foreach ($request as $item) {
                $settlement_type_mapping = [
                    'rial' => 'ریالی',
                    'other' => 'ارز دیجیتال',
                    '' => '-',
                ];

                $settlement_type = $settlement_type_mapping[$item->settlement_type];

                $status_mapping = [
                    'pending_decrease' => 'در انتظار تسویه',
                    'paid_increase' => 'واریز شده توسط بیمار',
                    'pending_increase' => 'در انتظار واریز بیمار',
                    'paid_decrease' => 'تسویه شده',
                    'cancel_decrease' => 'لغو شده',
                ];

                $status = $status_mapping[$item->status];

                $objPHPExcel->getActiveSheet()->SetCellValue('A' . $num,  $item->doctor->fullName);
                $objPHPExcel->getActiveSheet()->SetCellValue('B' . $num, jdate('d F Y ساعت H:i', strtotime($item->created_at)));
                $objPHPExcel->getActiveSheet()->SetCellValue('C' . $num, $item->paid_at ? jdate('d F Y ساعت H:i', strtotime($item->paid_at)) : '-');
                $objPHPExcel->getActiveSheet()->SetCellValue('D' . $num, (number_format(abs($item->amount))));
                $objPHPExcel->getActiveSheet()->SetCellValue('H' . $num, (number_format(abs($item->bank_wage))));
                $objPHPExcel->getActiveSheet()->SetCellValue('I' . $num, (number_format(abs($item->service_wage))));
                $objPHPExcel->getActiveSheet()->SetCellValue('J' . $num, $item->status == 'paid_decrease' || $item->status == 'pending_decrease'? '-' : (number_format(abs($item->amount - ($item->service_wage + $item->bank_wage)))));
                $objPHPExcel->getActiveSheet()->SetCellValue('E' . $num, $status);
                $objPHPExcel->getActiveSheet()->SetCellValue('F' . $num, $item->transId);
                $objPHPExcel->getActiveSheet()->SetCellValue('G' . $num, $settlement_type);

                $num++;
            }
        }


        // Rename sheet
        $objPHPExcel->getActiveSheet()->setTitle('bill list');
//        $objPHPExcel->se

        // Save Excel 2007 f
        $objWriter = new \PHPExcel_Writer_Excel2007($objPHPExcel);

        $path_blank = str_replace(get_ev('path_live'), 'httpdocs/upload', base_path('files'));

        $fileName = time() . '_save.xlsx';

        $objWriter->save("{$path_blank}/{$fileName}");

        return redirect('https://sandbox.sbm24.net/upload/files/' . $fileName);
    }

    public function accountBalance($doctor_id,$paymentType = 'Wallet')
    {
        $paid_increase = $this->calculateWallet($doctor_id,['paid_increase'],$paymentType);
        $paid_decrease = $this->calculateWallet($doctor_id,['paid_decrease'],$paymentType);
        $bank_wages = $this->calculateWallet($doctor_id,['paid_increase','paid_decrease','pending_decrease'] ,$paymentType, 'bank_wage');
        $pending_decrease = $this->calculateWallet($doctor_id,['pending_decrease'],$paymentType);
        $pending_increase = $this->calculateWallet($doctor_id,['pending_increase'],$paymentType);
        $net = $paid_increase + $paid_decrease - $bank_wages;
        $contract = DoctorContract::where([
            'user_id' => $doctor_id,
        ])->orderBy('created_at','DESC')->first();

        $percent = $contract ? $contract->percent : 0.03;

        return success_template([
            'sumIncrease' => abs($paid_increase),
            'sumDecrease' => abs($paid_decrease),
            'sumPendingIncrease' => abs($pending_increase),
            'account_balance' => abs($net - abs($paid_increase * $percent) ),
            'account_accessible' => abs($net + $pending_decrease - abs($paid_increase * $percent)),
            'non_rial_account_accessible' => abs($net + $pending_decrease - abs($paid_increase * $percent)),
            'pending_decrease' => abs($pending_decrease)
        ]);
    }

    public function accountTotal($doctor_id,$paymentType = 'Wallet')
    {
        $wallet_paid_increase = $this->calculateWallet($doctor_id,['paid_increase'],$paymentType);
        $wallet_paid_decrease = $this->calculateWallet($doctor_id,['paid_decrease'],$paymentType);
        $wallet_bank_wages = $this->calculateWallet($doctor_id,['paid_increase','paid_decrease','pending_decrease'] ,$paymentType, 'bank_wage');
        $wallet_service_wages = $this->calculateWallet($doctor_id,['paid_increase','paid_decrease'] ,$paymentType, 'service_wage');
        $wallet_net = $wallet_paid_increase + $wallet_paid_decrease - ($wallet_bank_wages + $wallet_service_wages);
        $cod_paid_increase = $this->calculateWallet($doctor_id,['paid_increase'],'COD');
        $cod_paid_decrease = $this->calculateWallet($doctor_id,['paid_decrease'],'COD');
        $cod_bank_wages = $this->calculateWallet($doctor_id,['paid_increase','paid_decrease','pending_decrease'] ,'COD', 'bank_wage');
        $cod_service_wages = $this->calculateWallet($doctor_id,['paid_increase','paid_decrease'] ,'COD', 'service_wage');
        $cod_net = $cod_paid_increase + $cod_paid_decrease - ($cod_bank_wages + $cod_service_wages);
        return success_template([
            'wallet_sum_increase' => abs($wallet_paid_increase),
            'wallet_service_wage' => abs($wallet_service_wages),
            'wallet_bank_wage'    => abs($wallet_bank_wages),
            'wallet_doctor_wage'  => abs($wallet_paid_increase) - abs($wallet_service_wages) - abs($wallet_bank_wages),
            'wallet_account_balance' => abs($wallet_net),
            'cod_sum_increase' => abs($cod_paid_increase),
            'cod_service_wage' => abs($cod_service_wages),
            'cod_bank_wage'    => abs($cod_bank_wages),
            'cod_doctor_wage'  => abs($cod_paid_increase) - abs($cod_service_wages) - abs($cod_bank_wages),
            'cod_account_balance' => abs($cod_net),
        ]);
    }

    public function calculateWallet($doctor_id,$status,$paymentType = 'Wallet' ,$column = 'amount')
    {
        return DoctorWallet::where('doctor_id',$doctor_id)
            ->whereIn('status', $status)->where(['payment_type' => $paymentType])->sum($column);
    }

    public function showWallet($id)
    {
        return view('admin.bill.doctor.walletPaymentConfirm');
    }
    public function updateWallet($id)
    {
        $wallet = DoctorWallet::findOrFail($id);
        $wallet->transId = $this->request->transId;
        $wallet->save();

        return redirect('cp-manager/bill/doctor/wallet')->with('success', 'ثبت شد.')->withInput();

    }

    public function payWallet($id)
    {
        $wallet = DoctorWallet::where('settlement_type','rial')
            ->where('status','pending_decrease')->where('id',$id)->first();

        if (!$wallet){
            return back()->with('error', 'اطلاعات پرداخت معتبر نمی باشد.')->withInput();
        }

//        $cl = new Client(['headers' => ["Accept" => "application/json",]]);
//        $bank_account = json_decode($cl->get(route("graphql",['type' => 'bank_account_add','data' => 'IR' .  $wallet->account_id]))->getBody()->getContents(), true);
//
//        if ($bank_account['errors']){
//            $bank_accounts = json_decode($cl->get(route("graphql",['type' => 'bank_accounts']))->getBody()->getContents(), true)['data']['BankAccounts'];
//
//            foreach ($bank_accounts as $account){
//                if ($account['iban'] == 'IR' . $wallet->account_id){$bank_account_id = $account['id'];}
//            }
//        }else{
//            $bank_account_id = $bank_account['data']['0']['BankAccountAdd']['id'];
//        }
//
//        $payout = json_decode($cl->get(route("graphql",['type' => 'payout','data' =>
//            $this->zarrin_terminal_id.'&'.$bank_account_id .'&'.abs($wallet->amount)
//        ]))->getBody()->getContents(), true);
//
//        if (isset($payout['errors'])) {
//            dd($payout['errors']);
//        }else{
//
//            $result = $payout['data']['PayoutAdd'];
//            $wallet->status = 'paid_decrease';
//            $wallet->paid_at = Carbon::now()->format('Y-m-d h:i:s');
//            $wallet->transId = $result['id'];
//            $wallet->receipt_link = 'https://next.zarinpal.com/payout/receipt/'.$result['url_code'];
//            $wallet->save();
//            return back()->with('success', 'صورت حساب پرداخت شد، کد شناسایی ' . $wallet->transId)->withInput();
//        }


        try {
            $payment = new Zibal();
            $data = [
                'amount' => abs($wallet->amount),
                'bankAccount' => 'IR' . $wallet->account_id,
                'wallet_id' => $this->online_wallet_id,
                'uniqueCode' => (string)uniqid('', false),
            ];

            $result = $payment->checkout($data);

            if ($result->result == 1) {
                $wallet->status = 'paid_decrease';
                $wallet->paid_at = Carbon::now()->format('Y-m-d h:i:s');
                $wallet->transId = $result->data->id;
                $wallet->receipt_link = $result->data->receipt;
                $wallet->save();
                return back()->with('success', 'صورت حساب پرداخت شد، کد شناسایی ' . $wallet->transId)->withInput();
            }
            return back()->with('error', $result->message)->withInput();
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage())->withInput();
        }
    }

    public function cancelWallet($id)
    {
        $wallet = DoctorWallet::where('status','pending_decrease')
            ->where('id',$id)->first();

        if (!$wallet){
            return back()->with('error', 'اطلاعات درخواست معتبر نمی باشد.')->withInput();
        }
        $wallet->status = 'cancel_decrease';
        $wallet->save();
        return back()->with('success', ' درخواست با موفقیت لغو شد.')->withInput();
    }

    public function walletTransactions()
    {
        $where_array = array();

        $filter_transId = $this->request->get('filter_transId');
        if ($filter_transId) {
            $where_array[] = array('doctor_wallet_transactions.transId', $filter_transId);
        }

        $filter_name = $this->request->get('filter_name');
        if ($filter_name) {
            $where_array[] = array('doctor_wallet_transactions.fullname', 'LIKE' , '%'.$filter_name .'%');
        }

        $filter_start_date = $this->request->get('filter_start_date');
        $filter_end_date = $this->request->get('filter_end_date');
        if ($filter_start_date) {

            $date = explode('/', $filter_start_date);
            $date = jalali_to_gregorian($date[0], $date[1], $date[2], '-');

            $where_array[] = array(DB::raw('DATE(doctor_wallet_transactions.created_at)'), '>=', $date);
        }

        if ($filter_end_date) {

            $date = explode('/', $filter_end_date);
            $date = jalali_to_gregorian($date[0], $date[1], $date[2], '-');

            $where_array[] = array(DB::raw('DATE(doctor_wallet_transactions.created_at)'), '<=', $date);
        }

        $request = DoctorWalletTransaction::where($where_array)->orderByDesc('created_at')->paginate(10);

        return view('admin.bill.doctor.walletTransactions' , ['request' => $request] );

    }

    public function walletOverview()
    {
        $where_array = array();
        $where_array[] = array('payment_type' , 'Wallet');


        $filter_start_date = $this->request->get('filter_start_date');
        $filter_end_date = $this->request->get('filter_end_date');
        if ($filter_start_date) {

            $date = explode('/', $filter_start_date);
            $date = jalali_to_gregorian($date[0], $date[1], $date[2], '-');

            $where_array[] = array(DB::raw('DATE(doctor_wallets.paid_at)'), '>=', $date);
        }

        if ($filter_end_date) {

            $date = explode('/', $filter_end_date);
            $date = jalali_to_gregorian($date[0], $date[1], $date[2], '-');

            $where_array[] = array(DB::raw('DATE(doctor_wallets.paid_at)'), '<=', $date);
        }

        $sumIncrease = DoctorWallet::where(['status' => 'paid_increase'])->where($where_array)->sum('amount');
        $sumDecrease = DoctorWallet::where(['status' => 'paid_decrease'])->where($where_array)->sum('amount');
        $sumServiceWage = DoctorWallet::where(['status' => 'paid_increase'])->where($where_array)->sum('service_wage');
        $sumBankWage = DoctorWallet::whereIn('status' ,['paid_increase','paid_decrease'])->where($where_array)->sum('bank_wage');

        $overview['sumIncrease'] = $sumIncrease;
        $overview['sumDecrease'] = $sumDecrease;
        $overview['sumServiceWage'] = $sumServiceWage;
        $overview['sumBankWage'] = $sumBankWage;

        $doctors = DoctorWallet::query()
            ->where('payment_type' , 'Wallet')
            ->with(['doctor' => function ($query) {
                $query->select('id', 'fullname');
            }])
            ->select('doctor_id')
            ->groupBy('doctor_id')->get();
        $account_balance = NULL;
        if($this->request->doctor_id) {
            $account_balance = json_decode($this->accountBalance($this->request->doctor_id)->getContent())->data;
        }

        $title = 'خلاصه وضعیت درگاه سلامت';

        return view('admin.bill.doctor.walletOverview' , compact('overview','account_balance','doctors','title') );

    }
    public function codOverview()
    {
        $where_array = array();
        $where_array[] = array('payment_type' , 'COD');

        $filter_start_date = $this->request->get('filter_start_date');
        $filter_end_date = $this->request->get('filter_end_date');
        if ($filter_start_date) {

            $date = explode('/', $filter_start_date);
            $date = jalali_to_gregorian($date[0], $date[1], $date[2], '-');

            $where_array[] = array(DB::raw('DATE(doctor_wallets.paid_at)'), '>=', $date);
        }

        if ($filter_end_date) {

            $date = explode('/', $filter_end_date);
            $date = jalali_to_gregorian($date[0], $date[1], $date[2], '-');

            $where_array[] = array(DB::raw('DATE(doctor_wallets.paid_at)'), '<=', $date);
        }

        $sumIncrease = DoctorWallet::where(['status' => 'paid_increase'])->where($where_array)->sum('amount');
        $sumDecrease = DoctorWallet::where(['status' => 'paid_decrease'])->where($where_array)->sum('amount');
        $sumServiceWage = DoctorWallet::where(['status' => 'paid_increase'])->where($where_array)->sum('service_wage');
        $sumBankWage = DoctorWallet::whereIn('status' ,['paid_increase','paid_decrease'])->where($where_array)->sum('bank_wage');

        $overview['sumIncrease'] = $sumIncrease;
        $overview['sumDecrease'] = $sumDecrease;
        $overview['sumServiceWage'] = $sumServiceWage;
        $overview['sumBankWage'] = $sumBankWage;

        $doctors = DoctorWallet::query()
            ->where('payment_type' , 'COD')
            ->with(['doctor' => function ($query) {
                $query->select('id', 'fullname');
            }])
            ->select('doctor_id')
            ->groupBy('doctor_id')->get();
        $account_balance = NULL;
        if($this->request->doctor_id) {
            $account_balance = json_decode($this->accountBalance($this->request->doctor_id,'COD')->getContent())->data;
        }

        $title = 'خلاصه وضعیت پرداخت در محل';

        return view('admin.bill.doctor.walletOverview' , compact('overview','account_balance','doctors','title') );

    }

    public function createWalletTransactions()
    {
        return view('admin.bill.doctor.createWalletTransaction');
    }

    public function storeWalletTransactions()
    {
        $ValidData = $this->validate($this->request,[
            'email' => 'required',
            'fullname' => 'required',
            'amount' => 'required',
            'transId' => 'required',
        ]);
        $operator = User::where('email',$this->request->email)->where('approve', 11)->first();
        if (!$operator){
            return back()->with('error','کاربر یافت نشد')->withInput();
        }
        $walletTransaction = new DoctorWalletTransaction();
        $walletTransaction->fullname = $this->request->fullname;
        $walletTransaction->amount = $this->request->amount;
        $walletTransaction->user_id = $operator->id;
        $walletTransaction->transId =$this->request->transId;
        $walletTransaction->save();

        return redirect('cp-manager/bill/doctor/wallet/transactions')->with('success', 'ثبت شد.')->withInput();

    }

    public function total()
    {



            //filter set to query
            $filter_name = trim($this->request->get('filter_name'));
            $filter_dr_mobile = trim($this->request->get('filter_mobile'));

            $array_dr = [];
            if ($filter_name) {

                $users = User::where('approve', '1')
                    ->where(function ($query) use ($filter_name) {
                        $query->search($filter_name, false);
                    })
                    ->orderBy('created_at', 'desc')
                    ->select('id')
                    ->get();

                if ($users) {
                    foreach ($users as $v) {
                        $array_dr[] = $v['id'];
                    }
                }

            }
            if ($filter_dr_mobile) {

                $users = User::where('approve', '1')
                    ->where('mobile', $filter_dr_mobile)
                    ->orderBy('created_at', 'desc')
                    ->select('id')
                    ->get();

                if ($users) {
                    foreach ($users as $v) {
                        $array_dr[] = $v['id'];
                    }
                }

            }

//            $doctors = User::with(['DoctorWallet','DoctorCOD'
////                ,'DoctorTransactions'
//            ])->where(['approve'=>1])->paginate(10);

            $doctors = DoctorWallet::with('doctor:id,fullName')->whereIn('status',['paid_increase','paid_decrease'])->get('doctor_id','doctor.fullName');

            $balance = [];
            foreach ($doctors as $doctor){
                $balance[$doctor->doctor_id] = $this->accountTotal($doctor->doctor_id)->original['data'];

                $balance[$doctor->doctor_id]['fullname'] = $doctor->doctor->fullName;
            }
//dd($balance);
        $result = Collection::make($balance);

        $balance = $result->paginate(\request()->get('per-page',30));

            return view('admin/bill/doctor/total', ['request' => $balance]);

    }

    public function exportTotal()
    {


        $doctors = DoctorWallet::with('doctor:id,fullName')->whereIn('status',['paid_increase','paid_decrease'])->get('doctor_id','doctor.fullName');

        $balance = [];
        foreach ($doctors as $doctor){
            $balance[$doctor->doctor_id] = $this->accountTotal($doctor->doctor_id)->original['data'];

            $balance[$doctor->doctor_id]['fullname'] = $doctor->doctor->fullName;
        }
        // Create new PHPExcel object
        $objPHPExcel = new \PHPExcel();

        // Set properties
        $objPHPExcel->getProperties()->setCreator("Maarten Balliauw");
        $objPHPExcel->getProperties()->setLastModifiedBy("Maarten Balliauw");
        $objPHPExcel->getProperties()->setTitle("Office 2007 XLSX Test Document");
        $objPHPExcel->getProperties()->setSubject("Office 2007 XLSX Test Document");
        $objPHPExcel->getProperties()->setDescription("Test document for Office 2007 XLSX, generated using PHP classes.");


        // Add some data
        $objPHPExcel->setActiveSheetIndex(0);
        $objPHPExcel->getActiveSheet()->SetCellValue('A1', 'نام پزشک');
        $objPHPExcel->getActiveSheet()->SetCellValue('B1', 'مبلغ تراکنش ویزیت');
        $objPHPExcel->getActiveSheet()->SetCellValue('C1', 'سهم سامانه');
        $objPHPExcel->getActiveSheet()->SetCellValue('D1', 'سهم پزشک');
        $objPHPExcel->getActiveSheet()->SetCellValue('E1', 'مانده');
        $objPHPExcel->getActiveSheet()->SetCellValue('F1', 'مبلغ تراکنش درگاه پرداخت');
        $objPHPExcel->getActiveSheet()->SetCellValue('G1', 'سهم سامانه');
        $objPHPExcel->getActiveSheet()->SetCellValue('H1', 'سهم پزشک');
        $objPHPExcel->getActiveSheet()->SetCellValue('I1', 'کارمزد');
        $objPHPExcel->getActiveSheet()->SetCellValue('J1', 'مانده');
        $objPHPExcel->getActiveSheet()->SetCellValue('K1', 'مبلغ تراکنش پوز');
        $objPHPExcel->getActiveSheet()->SetCellValue('L1', 'سهم سامانه');
        $objPHPExcel->getActiveSheet()->SetCellValue('M1', 'سهم پزشک');
        $objPHPExcel->getActiveSheet()->SetCellValue('N1', 'مانده');

        $num = 2;
        if ($balance) {
            foreach ($balance as $item) {
                $objPHPExcel->getActiveSheet()->SetCellValue('A' . $num, $item['fullname']);
                $objPHPExcel->getActiveSheet()->SetCellValue('B' . $num, '-');
                $objPHPExcel->getActiveSheet()->SetCellValue('C' . $num, '-');
                $objPHPExcel->getActiveSheet()->SetCellValue('D' . $num, '-');
                $objPHPExcel->getActiveSheet()->SetCellValue('E' . $num, '-');
                $objPHPExcel->getActiveSheet()->SetCellValue('F' . $num, (string)$item['wallet_sum_increase']);
                $objPHPExcel->getActiveSheet()->SetCellValue('G' . $num, (string)$item['wallet_service_wage']);
                $objPHPExcel->getActiveSheet()->SetCellValue('H' . $num, (string)$item['wallet_doctor_wage']);
                $objPHPExcel->getActiveSheet()->SetCellValue('I' . $num, (string)$item['wallet_bank_wage']);
                $objPHPExcel->getActiveSheet()->SetCellValue('J' . $num, (string)$item['wallet_account_balance']);
                $objPHPExcel->getActiveSheet()->SetCellValue('K' . $num, (string)$item['cod_sum_increase']);
                $objPHPExcel->getActiveSheet()->SetCellValue('L' . $num, (string)$item['cod_service_wage']);
                $objPHPExcel->getActiveSheet()->SetCellValue('M' . $num, (string)$item['cod_doctor_wage']);
                $objPHPExcel->getActiveSheet()->SetCellValue('N' . $num, (string)$item['cod_account_balance']);

                $num++;
            }
        }


        // Rename sheet
        $objPHPExcel->getActiveSheet()->setTitle('total');
//        $objPHPExcel->se

        // Save Excel 2007 f
        $objWriter = new \PHPExcel_Writer_Excel2007($objPHPExcel);

        $path_blank = str_replace(get_ev('path_live'), 'httpdocs/upload', base_path('files'));

        $fileName = time() . '_save.xlsx';

        $objWriter->save("{$path_blank}/{$fileName}");

        return redirect('https://sandbox.sbm24.net/upload/files/' . $fileName);
    }

}
