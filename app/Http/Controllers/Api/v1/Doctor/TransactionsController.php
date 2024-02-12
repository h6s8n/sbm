<?php

namespace App\Http\Controllers\Api\v1\Doctor;

use App\Http\BlackList;
use App\Http\Controllers\Api\v2\vandar\VandarController;
use App\Http\SystemInfo;
use App\Model\User\UserCodes;
use App\Model\Visit\EventReserves;
use App\Model\Wallet\DoctorWallet;
use App\Model\Doctor\DoctorContract;
use App\Model\Visit\TransactionDoctor;
use App\RequestCodesLog;
use App\Services\Gateways\src\Zibal;
use App\Repositories\v2\User\UserInterface;
use App\Enums\VisitTypeEnum;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Hekmatinasser\Verta\Verta;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class TransactionsController extends Controller
{
    protected $request;

    public function __construct(Request $request)
    {
        date_default_timezone_set("Asia/Tehran");
        $this->request = $request;

        require_once(base_path('app/jdf.php'));

    }


    public function reserve()
    {


        $user = auth()->user();
        $status = $this->request->get('status');
        $week = $this->request->get('week');
        $request = TransactionDoctor::join('users', 'users.id', '=', 'transaction_doctors.user_id')
            ->where('transaction_doctors.doctor_id', $user->id)
            ->select(
                'transaction_doctors.id as key',
                'transaction_doctors.created_at',
                'transaction_doctors.amount',
                'transaction_doctors.status',
                'transaction_doctors.message',
                'users.fullname as user_name'
            )->orderBy('transaction_doctors.created_at', 'DESC');
        if ($status)
            $request = $request->where('transaction_doctors.status', $status);
        if ($week) {
            switch ($week){
                case 1 : {
                    if (Carbon::now()->dayOfWeek != 6)
                        $date = Carbon::parse(Carbon::now()->startOfWeek())
                            ->subDays(2)->format('Y-m-d');
                    else
                        $date = Carbon::now()->format('Y-m-d');
                    $request = $request->whereDate('transaction_doctors.created_at', '>=', $date)
                        ->whereDate('transaction_doctors.created_at', '<=', Carbon::now()->format('Y-m-d'));
                    break;
                }
                case 2: {
                    $date = Verta::now()->startMonth()
                        ->formatGregorian('Y-m-d');
                    $request = $request->whereDate('transaction_doctors.created_at', '>=', $date)
                        ->whereDate('transaction_doctors.created_at', '<=', Carbon::now()->format('Y-m-d'));
                    break;
                }
                case 3:{
                    $date = Verta::now()->subMonth(1)
                        ->startMonth()->formatGregorian('Y-m-d');
                    $till = Verta::now()->startMonth()->subDays(1)->formatGregorian('Y-m-d');
                    $request = $request->whereDate('transaction_doctors.created_at', '>=', $date)
                        ->whereDate('transaction_doctors.created_at', '<=', $till);
                }
            }
        }
        $request = $request->get();


        $RequestFull = [];
        if ($request) {
            foreach ($request as $item) {

                $dateTime = Carbon::parse($item['created_at']);
                $date = jdate('Y/m/d', strtotime($dateTime));
                $time = jdate('H:i', strtotime($dateTime));

                $RequestFull[] = [
                    'key' => $item['key'],
                    'price' => number_format($item['amount']),
                    'user_name' => $item['user_name'],
                    'date' => $date,
                    'time' => $time,
                    'status' => $item['status'],
                    'message' => $item['message'],
                ];
            }
        }


        return success_template($RequestFull);


    }
    public function reserve2()
    {
        $search = $this->request->get('search' , NULL);
        $order_by = $this->request->get('order_by' , 'transaction_doctors.created_at');
        $sort = $this->request->get('sort','DESC');

        $filter_status = $this->request->get('filter_status');
        $filter_type = $this->request->get('filter_type');
        $filter_start_created_at = $this->request->get('filter_start_created_at');
        $filter_end_created_at = $this->request->get('filter_end_created_at');

        $page = $this->request->get('page');
        $per_page = $this->request->get('per_page',7);

        if($page == null){
            $this->request->merge(['page'=>1]);
        }


        $user = auth()->user();
        $week = $this->request->get('week');
        $request = TransactionDoctor::join('users', 'users.id', '=', 'transaction_doctors.user_id')
            ->where('transaction_doctors.doctor_id', $user->id)
            ->join('event_reserves as es' , 'transaction_doctors.event_id','=','es.id')
            ->join('doctor_calenders as dc' ,'es.calender_id', '=' , 'dc.id')
            ->select(
                'transaction_doctors.id as key',
                'dc.type',
                'transaction_doctors.created_at',
                'transaction_doctors.amount',
                'transaction_doctors.status',
                'transaction_doctors.transaction_id',
                'transaction_doctors.message',
                'users.fullname as user_name',
                'receipt as receipt_link'
            )->orderBy($order_by !== 'time' ? $order_by : 'transaction_doctors.created_at', $sort)
            ->when($search,function ($query) use ($search){
                $query->where('users.fullname', 'LIKE', '%' . $search . '%')
                ->orWhere('transaction_doctors.transaction_id', '%' . $search . '%')
//                ->orWhere('transaction_doctors.amount', '%' . $search . '%')
                ;
            })->when($filter_status,function ($query) use ($filter_status){
                $query->where('transaction_doctors.status',$filter_status);
            })
            ->when($filter_start_created_at,function ($query) use ($filter_start_created_at){
                $query->whereDate('transaction_doctors.created_at', '>=',$filter_start_created_at);
            })
            ->when($filter_end_created_at,function ($query) use ($filter_end_created_at){
                $query->whereDate('transaction_doctors.created_at', '<=',$filter_end_created_at);
            })
            ->when($filter_type,function ($query) use ($filter_type){
                $query->where('dc.type',$filter_type);
            })
        ;

        if ($filter_status)
            $request = $request->where('transaction_doctors.status', $filter_status);
        if ($week) {
            switch ($week){
                case 1 : {
                    if (Carbon::now()->dayOfWeek != 6)
                        $date = Carbon::parse(Carbon::now()->startOfWeek())
                            ->subDays(2)->format('Y-m-d');
                    else
                        $date = Carbon::now()->format('Y-m-d');
                    $request = $request->whereDate('transaction_doctors.created_at', '>=', $date)
                        ->whereDate('transaction_doctors.created_at', '<=', Carbon::now()->format('Y-m-d'));
                    break;
                }
                case 2: {
                    $date = Verta::now()->startMonth()
                        ->formatGregorian('Y-m-d');
                    $request = $request->whereDate('transaction_doctors.created_at', '>=', $date)
                        ->whereDate('transaction_doctors.created_at', '<=', Carbon::now()->format('Y-m-d'));
                    break;
                }
                case 3:{
                    $date = Verta::now()->subMonth(1)
                        ->startMonth()->formatGregorian('Y-m-d');
                    $till = Verta::now()->startMonth()->subDays(1)->formatGregorian('Y-m-d');
                    $request = $request->whereDate('transaction_doctors.created_at', '>=', $date)
                        ->whereDate('transaction_doctors.created_at', '<=', $till);
                }
            }
        }
        $request = $request->paginate($per_page);


        $RequestFull = [];
        if ($request) {
            foreach ($request as $item) {

                $dateTime = Carbon::parse($item['created_at']);
                $date = jdate('Y/m/d', strtotime($dateTime));
                $time = jdate('H:i', strtotime($dateTime));

                $RequestFull[] = [
                    'key' => $item['key'],
                    'amount' => ($item['amount']),
                    'fullname' => $item['user_name'],
                    'date' => $date,
                    'type' => $item['type'],
                    //'visit_type' => VisitTypeEnum::name($item['type']),
                    'time' => $time,
                    'status' => $item['status'],
                    'transaction_id' => $item['transaction_id'],
                    'message' => $item['message'],
                    'receipt_link' => $item['receipt_link'],
                ];
            }
        }

//        $result = Collection::make($RequestFull);
//
//        $result = $result->paginate($per_page);

        $data = [
            'data' => $RequestFull,
            'last_page' => $request->lastPage(),
            'total' => $request->total(),
        ];

        return success_template($data);


    }

    public function overview()
    {
        $user_id = auth()->id();
        $data = EventReserves::select(
            DB::raw('count(event_reserves.id) as allOfThem'),
            DB::raw('SUM(CASE WHEN visit_status="end" THEN 1 ELSE 0 END) as success'),
            DB::raw('SUM(CASE WHEN visit_status="not_end" THEN 1 ELSE 0 END) as not_end'),
            DB::raw('SUM(CASE WHEN visit_status="cancel" THEN 1 ELSE 0 END) as cancel'),
            DB::raw('SUM(CASE WHEN visit_status="refunded" THEN 1 ELSE 0 END) as refunded'),
            DB::raw('SUM(transaction_doctors.amount) as amount'),
            DB::raw('SUM(tr_doctors.amount) as amount_pending')
        )->where('event_reserves.doctor_id',$user_id)
//            ->whereHas('UserTransaction',function ($query){
//                $query->where('status','paid');
//            })
            ->leftJoin('transaction_doctors',function ($left){
                $left->on('event_reserves.id','=', 'transaction_doctors.event_id')
                    ->where('transaction_doctors.status','paid');
            })->leftJoin('transaction_doctors as tr_doctors',function ($left_){
                $left_->on('event_reserves.id','=', 'tr_doctors.event_id')
                    ->where('tr_doctors.status','pending');
//                    ->whereNotExists(function ($query){
//                        $query->select('tr_doctors_.id')
//                            ->from('transaction_doctors as tr_doctors_')
//                            ->where('event_reserves.id','tr_doctors_.event_id')
//                            ->where('tr_doctors_.status','paid');
//                    });
            })->get();
        return success_template($data);
    }
    public function overview2()
    {
        $user_id = auth()->id();
        $data = EventReserves::select(
            DB::raw('count(event_reserves.id) as allOfThem'),
            DB::raw('SUM(CASE WHEN visit_status="end" THEN 1 ELSE 0 END) as success'),
            DB::raw('SUM(CASE WHEN visit_status="not_end" THEN 1 ELSE 0 END) as not_end'),
            DB::raw('SUM(CASE WHEN visit_status="cancel" THEN 1 ELSE 0 END) as cancel'),
            DB::raw('SUM(CASE WHEN visit_status="refunded" THEN 1 ELSE 0 END) as refunded'),
            DB::raw('SUM(transaction_doctors.amount) as amount'),
            DB::raw('SUM(tr_doctors.amount) as amount_pending')
        )->where('event_reserves.doctor_id',$user_id)
//            ->whereHas('UserTransaction',function ($query){
//                $query->where('status','paid');
//            })
            ->leftJoin('transaction_doctors',function ($left){
                $left->on('event_reserves.id','=', 'transaction_doctors.event_id')
                    ->where('transaction_doctors.status','paid');
            })->leftJoin('transaction_doctors as tr_doctors',function ($left_){
                $left_->on('event_reserves.id','=', 'tr_doctors.event_id')
                    ->where('tr_doctors.status','pending');
//                    ->whereNotExists(function ($query){
//                        $query->select('tr_doctors_.id')
//                            ->from('transaction_doctors as tr_doctors_')
//                            ->where('event_reserves.id','tr_doctors_.event_id')
//                            ->where('tr_doctors_.status','paid');
//                    });
            })->get();
        return success_template($data);
    }

    public function chart()
    {
     $user_id = auth()->id();
//     $user_id = 321;

        $data = EventReserves::select(
            DB::raw('count(id) as allOfThem'),
            DB::raw('SUM(CASE WHEN visit_status="end" THEN 1 ELSE 0 END) as success'),
            DB::raw('SUM(CASE WHEN visit_status="cancel" or visit_status="refunded" THEN 1 ELSE 0 END) as cancel'),
            DB::raw('SUBSTRING(fa_data,-5,2) as month')
        )->where('doctor_id',$user_id)
            ->whereHas('UserTransaction',function ($query){
                $query->where('status','paid');
            })->groupBy(DB::raw('SUBSTRING(fa_data,-5,2)'))->get();
        return success_template($data);
    }
    public function chart2()
    {
     $user_id = auth()->id();
//     $user_id = 321;

        $data = EventReserves::select(
            DB::raw('count(id) as allOfThem'),
            DB::raw('SUM(CASE WHEN visit_status="end" THEN 1 ELSE 0 END) as success'),
            DB::raw('SUM(CASE WHEN visit_status="cancel" or visit_status="refunded" THEN 1 ELSE 0 END) as cancel'),
            DB::raw('SUBSTRING(fa_data,-5,2) as month')
        )->where('doctor_id',$user_id)
            ->whereHas('UserTransaction',function ($query){
                $query->where('status','paid');
            })->groupBy(DB::raw('SUBSTRING(fa_data,-5,2)'))->get();
        return success_template($data);
    }

    public function recentOld()
    {
        $vandar = new VandarController();
        $token = $vandar->return_token();

        $headers = array(
            "Accept: application/json",
            "Authorization: Bearer " . $token,
        );

        try {
            $sheba = auth()->user()->account_sheba;
            $url = 'https://api.vandar.io/v2/business/Sbm/transaction?per_page=30&page=1&statusKind=settlements&q=' . $sheba;

            $cl = new Client([
                'headers' => [
                    "Accept" => "application/json",
                    "Authorization" => "Bearer " . $token,
                ]
            ]);
            $resp = json_decode($cl->get($url)->getBody()->getContents(), true);

            $results = [];
            foreach ($resp['data']['data'] as $data) {

                $result = [
                    'id' => $data['id'],
                    'status' => $data['status'] === -2 ? 'در صف بانک' : ($data['status'] == 2 ? 'موفق' : 'برگشتی'),
                    'description' => $data['receiver']['name'] . ' - ' . $data['receiver']['bank_name'],
                    'created_at' => $data['created_at'],
                    'estimated_deposit_time' => $data['time_prediction']['settlement_done_time_prediction'],
                    'amount' => ($data['amount'] - $data['wage']) * 10,
                    'receipt_url' => $data['receipt_url']

                ];
                $results[] = $result;
            }

            if (count($results) > 0) {
                return success_template($results);
            }
            return success_template(['message' => 'تراکنشی وجود ندارد.']);
        }catch (\Exception $e){
            return success_template(['message' => 'تراکنشی وجود ندارد.']);
        }

       return success_template(['message' => 'تراکنشی وجود ندارد.']);
    }
    public function recent()
    {
        $zibal = new Zibal();

        try {
            $sheba = auth()->user()->account_sheba;

            $data['bankAccount'] = 'IR'.$sheba;

            $resp = $zibal->report($data)->data;

            $results = [];
            foreach ($resp as $data) {

                $result = [
                    'id' => $data->refNumber,
                    'status' => $data->status === 0 ? 'موفق' : 'بازگشت به حساب',
                    'description' => '',
                    'created_at' => jdate('Y/m/d',strtotime($data->details[0]->createdAt)),
                    'estimated_deposit_time' => $data->persianSettlementDate,
                    'amount' => $data->amount,
                    'receipt_url' => ''
                ];
                $results[] = $result;
            }

            if (count($results) > 0) {
                return success_template($results);
            }
            return success_template(['message' => 'تراکنشی وجود ندارد.']);
        }catch (\Exception $e){
            return success_template(['message' => 'تراکنشی وجود ندارد.']);
        }

       return success_template(['message' => 'تراکنشی وجود ندارد.']);
    }

    public function wallet()
    {
        $doctor = auth()->user();
        $walletTransactions = DoctorWallet::with(['user:id,fullName,mobile'])->where('doctor_id',$doctor->id)
            ->orderBy('created_at','DESC')
            ->paginate(7);
        return success_template($walletTransactions);
    }

    public function wallet2()
    {
        $search = $this->request->get('search' , NULL);
        $order_by = $this->request->get('order_by' , 'doctor_wallets.created_at');
        $sort = $this->request->get('sort','DESC');
        $page = $this->request->get('page');
        $per_page = $this->request->get('per_page',7);

        $filter_status = $this->request->get('filter_status');
        $filter_settlement_type = $this->request->get('filter_settlement_type');
        $filter_service = $this->request->get('filter_service');
        $filter_type = $this->request->get('filter_type');
        $filter_mobile = $this->request->get('filter_mobile');
        $filter_start_created_at = $this->request->get('filter_start_created_at');
        $filter_end_created_at = $this->request->get('filter_end_created_at');
        $filter_start_paid_at = $this->request->get('filter_start_paid_at');
        $filter_end_paid_at = $this->request->get('filter_end_paid_at');
        $filter_start_amount = $this->request->get('filter_start_amount');
        $filter_end_amount = $this->request->get('filter_end_amount');
        $filter_transId = $this->request->get('filter_transId');


        if($page == null){
            $this->request->merge(['page'=>1]);
        }

        $doctor = auth()->user();

        $contract = DoctorContract::where([
            'user_id' => $doctor->id,
        ])->orderBy('created_at','DESC')->first();

        $percent = $contract ? $contract->percent : 0.03;

        $walletTransactions = DoctorWallet::
            leftJoin('users', function ($query) {
                $query->on('users.id','=','doctor_wallets.user_id');
            })
            ->orderBy($order_by, $sort)
            ->select(
                'doctor_wallets.id',
                'doctor_wallets.account_id as wallet_address',
                'amount',
                'doctor_wallets.created_at',
                'description',
                'paid_at',
                'service',
                'settlement_type',
                'doctor_wallets.status',
                DB::raw('IF(doctor_wallets.status = "paid_increase" || doctor_wallets.status = "paid_decrease", bank_wage , null ) as wage'),
                'service_wage',
                DB::raw('IF(doctor_wallets.status = "paid_increase" || doctor_wallets.status= "pending_increase", amount - (service_wage + bank_wage) , null ) as doctor_wage'),
                'transId',
                'type',
                'fullName',
                'mobile',
                'tether_count',
                'receipt_link'
            )
            ->where(['doctor_id'=>$doctor->id,'payment_type' => 'Wallet'])
            ->when($filter_status,function ($query) use ($filter_status){
                $query->where('doctor_wallets.status',$filter_status);
            })
            ->when($filter_service,function ($query) use ($filter_service){
                $query->where('service',$filter_service);
            })
            ->when($filter_mobile,function ($query) use ($filter_mobile){
                $query->where('users.mobile',$filter_mobile);
            })
            ->when($filter_type,function ($query) use ($filter_type){
                $query->where('doctor_wallets.type',$filter_type);
            })
            ->when($filter_settlement_type,function ($query) use ($filter_settlement_type){
                $query->where('settlement_type',$filter_settlement_type);
            })
            ->when($filter_start_created_at,function ($query) use ($filter_start_created_at){
                $query->whereDate('doctor_wallets.created_at', '>=',$filter_start_created_at);
            })
            ->when($filter_end_created_at,function ($query) use ($filter_end_created_at){
                $query->whereDate('doctor_wallets.created_at', '<=',$filter_end_created_at);
            })
            ->when($filter_start_paid_at,function ($query) use ($filter_start_paid_at){
                $query->whereDate('doctor_wallets.paid_at', '>=',$filter_start_paid_at);
            })
            ->when($filter_start_amount,function ($query) use ($filter_start_amount){
                $query->where('doctor_wallets.amount', '>=',$filter_start_amount);
            })
            ->when($filter_end_amount,function ($query) use ($filter_end_amount){
                $query->where('doctor_wallets.amount', '<=',$filter_end_amount);
            })
            ->when($filter_transId,function ($query) use ($filter_transId){
                $query->where('doctor_wallets.transId',$filter_transId);
            })
            ->when($filter_end_paid_at,function ($query) use ($filter_end_paid_at){
                $query->whereDate('doctor_wallets.paid_at', '<=',$filter_end_paid_at);
            })
            ->when($search,function ($query) use ($search){
                $query->whereHas('user',function ($query) use ($search){
                    $query->where('users.fullname', 'LIKE', '%' . $search . '%')
                        ->orWhere('users.mobile', 'LIKE', '%' . $search . '%');
                })
                ->orWhere('doctor_wallets.description' , 'LIKE' , '%'. $search .'%')
                ->orWhere('doctor_wallets.transId' , 'LIKE' , '%'. $search .'%');

            })
            ->paginate($per_page);

        return success_template($walletTransactions);
    }

    public function cod()
    {
//        $cod = new CODController($this->request);
//        $temp = $cod->report();

        $search = $this->request->get('search' , NULL);
        $order_by = $this->request->get('order_by' , 'doctor_wallets.created_at');
        $sort = $this->request->get('sort','DESC');
        $page = $this->request->get('page');
        $per_page = $this->request->get('per_page',7);

        $filter_status = $this->request->get('filter_status');
        $filter_settlement_type = $this->request->get('filter_settlement_type');
        $filter_type = $this->request->get('filter_type');
        $filter_service = $this->request->get('filter_service');
        $filter_transId = $this->request->get('filter_transId');
        $filter_mobile = $this->request->get('filter_mobile');
        $filter_start_created_at = $this->request->get('filter_start_created_at');
        $filter_end_created_at = $this->request->get('filter_end_created_at');
        $filter_start_amount = $this->request->get('filter_start_amount');
        $filter_end_amount = $this->request->get('filter_end_amount');
        $filter_start_paid_at = $this->request->get('filter_start_paid_at');
        $filter_end_paid_at = $this->request->get('filter_end_paid_at');

        if($page == null){
            $this->request->merge(['page'=>1]);
        }

        $doctor = auth()->user();

        $contract = DoctorContract::where([
            'user_id' => $doctor->id,
        ])->orderBy('created_at','DESC')->first();

        $percent = $contract ? $contract->percent : 0.03;

        $walletTransactions = DoctorWallet::
            leftJoin('users', function ($query) {
                $query->on('users.id','=','doctor_wallets.user_id');
            })
            ->orderBy($order_by, $sort)
            ->select(
                'doctor_wallets.id',
                'doctor_wallets.account_id as wallet_address',
                'amount',
                'doctor_wallets.created_at',
                'description',
                'paid_at',
                'service',
                'settlement_type',
                'doctor_wallets.status',
                DB::raw('IF(doctor_wallets.status = "paid_increase" || doctor_wallets.status = "paid_decrease", bank_wage , null ) as wage'),
                'service_wage',
                DB::raw('IF(doctor_wallets.status = "paid_increase" || doctor_wallets.status= "pending_increase", amount - (service_wage + bank_wage) , null ) as doctor_wage'),
                'transId',
                'transaction_date',
                'type',
                'fullName',
                'mobile',
                'tether_count',
                'receipt_link'
            )
            ->where(['doctor_id'=>$doctor->id,'payment_type' => 'COD'])
            ->when($filter_status,function ($query) use ($filter_status){
                $query->where('doctor_wallets.status',$filter_status);
            })
            ->when($filter_service,function ($query) use ($filter_service){
                $query->where('service',$filter_service);
            })
            ->when($filter_mobile,function ($query) use ($filter_mobile){
                $query->where('users.mobile',$filter_mobile);
            })
            ->when($filter_type,function ($query) use ($filter_type){
                $query->where('doctor_wallets.type',$filter_type);
            })
            ->when($filter_transId,function ($query) use ($filter_transId){
                $query->where('doctor_wallets.transId',$filter_transId);
            })
            ->when($filter_settlement_type,function ($query) use ($filter_settlement_type){
                $query->where('settlement_type',$filter_settlement_type);
            })
            ->when($filter_start_created_at,function ($query) use ($filter_start_created_at){
                $query->whereDate('doctor_wallets.created_at', '>=',$filter_start_created_at);
            })
            ->when($filter_end_created_at,function ($query) use ($filter_end_created_at){
                $query->whereDate('doctor_wallets.created_at', '<=',$filter_end_created_at);
            })
            ->when($filter_start_paid_at,function ($query) use ($filter_start_paid_at){
                $query->whereDate('doctor_wallets.paid_at', '>=',$filter_start_paid_at);
            })
            ->when($filter_end_paid_at,function ($query) use ($filter_end_paid_at){
                $query->whereDate('doctor_wallets.paid_at', '<=',$filter_end_paid_at);
            })
            ->when($filter_start_amount,function ($query) use ($filter_start_amount){
                $query->where('doctor_wallets.amount', '>=',$filter_start_amount);
            })
            ->when($filter_end_amount,function ($query) use ($filter_end_amount){
                $query->where('doctor_wallets.amount', '<=',$filter_end_amount);
            })
            ->when($search,function ($query) use ($search){
                $query->whereHas('user',function ($query) use ($search){
                    $query->where('users.fullname', 'LIKE', '%' . $search . '%')
                        ->orWhere('users.mobile', 'LIKE', '%' . $search . '%');
                })
                ->orWhere('doctor_wallets.description' , 'LIKE' , '%'. $search .'%')
                ->orWhere('doctor_wallets.transId' , 'LIKE' , '%'. $search .'%');

            })
            ->paginate($per_page);

        return success_template($walletTransactions);
    }

    public function walletList()
    {
        $wallet = new WalletController($this->request);
        $cod = new CODController($this->request);

        $result['wallet'] = json_decode($wallet->accountBalance()->getContent())->data;
        $result['cod'] = json_decode($cod->accountBalance()->getContent())->data;

        return success_template($result);
    }

    public function export_wallet()
    {
        $search = $this->request->get('search' , NULL);
        $order_by = $this->request->get('order_by' , 'doctor_wallets.paid_at');
        $sort = $this->request->get('sort','ASC');
        $page = $this->request->get('page');
        $per_page = $this->request->get('per_page',7);

        $filter_status = $this->request->get('filter_status');
        $filter_settlement_type = $this->request->get('filter_settlement_type');
        $filter_service = $this->request->get('filter_service');
        $filter_mobile = $this->request->get('filter_mobile');
        $filter_start_created_at = $this->request->get('filter_start_created_at');
        $filter_end_created_at = $this->request->get('filter_end_created_at');
        $filter_start_paid_at = $this->request->get('filter_start_paid_at');
        $filter_end_paid_at = $this->request->get('filter_end_paid_at');

        if($page == null){
            $this->request->merge(['page'=>1]);
        }

        $doctor = auth()->user();

        $walletTransactions = DoctorWallet::
            leftJoin('users', function ($query) {
                $query->on('users.id','=','doctor_wallets.user_id');
            })
            ->orderBy($order_by, $sort)
            ->select(
                'amount',
                'doctor_wallets.created_at',
                'description',
                'paid_at',
                'transaction_date',
                'service',
                'settlement_type',
                'doctor_wallets.status',
                DB::raw('IF(doctor_wallets.status = "paid_increase" || doctor_wallets.status = "paid_decrease", bank_wage , null ) as wage'),
                'service_wage',
                DB::raw('IF(doctor_wallets.status = "paid_increase" || doctor_wallets.status= "pending_increase", amount - (service_wage + bank_wage) , null ) as doctor_wage'),
                'transId',
                'type',
                'payment_type',
                'fullName',
                'mobile'
            )
            ->where(['payment_type'=>'Wallet','doctor_id'=>$doctor->id])
            ->whereIn('doctor_wallets.status',['paid_increase','paid_decrease'])
            ->when($filter_status,function ($query) use ($filter_status){
                $query->where('doctor_wallets.status',$filter_status);
            })
            ->when($filter_service,function ($query) use ($filter_service){
                $query->where('service',$filter_service);
            })
            ->when($filter_mobile,function ($query) use ($filter_mobile){
                $query->where('users.mobile',$filter_mobile);
            })
            ->when($filter_settlement_type,function ($query) use ($filter_settlement_type){
                $query->where('settlement_type',$filter_settlement_type);
            })
            ->when($filter_start_created_at,function ($query) use ($filter_start_created_at){
                $query->whereDate('doctor_wallets.created_at', '>=',$filter_start_created_at);
            })
            ->when($filter_end_created_at,function ($query) use ($filter_end_created_at){
                $query->whereDate('doctor_wallets.created_at', '<=',$filter_end_created_at);
            })
            ->when($filter_start_paid_at,function ($query) use ($filter_start_paid_at){
                $query->whereDate('doctor_wallets.paid_at', '>=',$filter_start_paid_at);
            })
            ->when($filter_end_paid_at,function ($query) use ($filter_end_paid_at){
                $query->whereDate('doctor_wallets.paid_at', '<=',$filter_end_paid_at);
            })
            ->when($search,function ($query) use ($search){
                $query->whereHas('user',function ($query) use ($search){
                    $query->where('users.fullname', 'LIKE', '%' . $search . '%')
                        ->orWhere('users.mobile', 'LIKE', '%' . $search . '%');
                })
                ->orWhere('doctor_wallets.description' , 'LIKE' , '%'. $search .'%')
                ->orWhere('doctor_wallets.transId' , 'LIKE' , '%'. $search .'%');

            })->get();


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
        $objPHPExcel->getActiveSheet()->SetCellValue('A1', 'تاریخ کارکرد');
        $objPHPExcel->getActiveSheet()->SetCellValue('B1', 'کانال');
        $objPHPExcel->getActiveSheet()->SetCellValue('C1', 'عملیات');
        $objPHPExcel->getActiveSheet()->SetCellValue('D1', 'مبلغ');
        $objPHPExcel->getActiveSheet()->SetCellValue('E1', 'واحد');
        $objPHPExcel->getActiveSheet()->SetCellValue('F1', 'کارمزد بانکی و سیستمی');
        $objPHPExcel->getActiveSheet()->SetCellValue('G1', 'کارمزد SBM24');
        $objPHPExcel->getActiveSheet()->SetCellValue('H1', 'مبلغ پس از کسورات');
        $objPHPExcel->getActiveSheet()->SetCellValue('I1', 'اعتبار پس از تراکنش');
        $objPHPExcel->getActiveSheet()->SetCellValue('J1', 'توضیحات');
        $objPHPExcel->getActiveSheet()->SetCellValue('K1', 'نام و نام خانوادگی مشتری');
        $objPHPExcel->getActiveSheet()->SetCellValue('L1', 'موبایل مشتری');
        $objPHPExcel->getActiveSheet()->SetCellValue('M1', 'تاریخ ایجاد لینک');
        $objPHPExcel->getActiveSheet()->SetCellValue('N1', 'وضعیت');

        $num = 2;

        $data = [
          'doctor_id' => $doctor->id,
          'payment_type' => 'Wallet',
          'start_paid_at' => $filter_start_paid_at,
          'end_paid_at' => $filter_end_paid_at,
        ];

        $account_balance = json_decode($this->accountBalance($data)->getContent())->data;

        $walletBalance = 0;
        if ($filter_start_paid_at || $filter_end_paid_at) {
            $walletBalance = $account_balance->account_balance;
        }
        if ($walletTransactions) {
            foreach ($walletTransactions as $item) {

                if ($item['status'] == 'paid_increase'){
                    $walletBalance += $item['doctor_wage'];
                }elseif ( $item['status'] == 'paid_decrease'){
                    $walletBalance += $item['amount'];
                }

                $objPHPExcel->getActiveSheet()->SetCellValue('A' . $num, jdate('Ymd', strtotime($item['paid_at'])));

                switch($item['payment_type']) {
                    case 'COD':
                        $objPHPExcel->getActiveSheet()->SetCellValue('B' . $num, 'پرداخت در محل');
                        break;
                    case 'Wallet':
                        $objPHPExcel->getActiveSheet()->SetCellValue('B' . $num, 'درگاه پرداخت آنلاین');
                        break;
                }

                switch($item['type']) {
                    case 'increase':
                        $objPHPExcel->getActiveSheet()->SetCellValue('C' . $num, 'واریز');
                        break;
                    case 'decrease':
                        $objPHPExcel->getActiveSheet()->SetCellValue('C' . $num, 'برداشت');
                        break;
                }

                $objPHPExcel->getActiveSheet()->SetCellValue('D' . $num, (string)$item['amount']);
                switch($item['settlement_type']) {
                    case 'tether':
                    case 'other':
                        $objPHPExcel->getActiveSheet()->SetCellValue('E' . $num, 'تتر');
                        break;
                    case 'rial':
                    case '':
                        $objPHPExcel->getActiveSheet()->SetCellValue('E' . $num, 'ریال');
                        break;
                }

                $objPHPExcel->getActiveSheet()->SetCellValue('F' . $num, (string)$item['wage']);
                $objPHPExcel->getActiveSheet()->SetCellValue('G' . $num, (string)$item['service_wage']);
                $objPHPExcel->getActiveSheet()->SetCellValue('H' . $num, (string)$item['doctor_wage']);

                $objPHPExcel->getActiveSheet()->SetCellValue('I' . $num, (string)$walletBalance);
                $objPHPExcel->getActiveSheet()->SetCellValue('J' . $num, $item['description'] . ' - '  );

                $objPHPExcel->getActiveSheet()->SetCellValue('K' . $num, $item['fullName']);
                $objPHPExcel->getActiveSheet()->SetCellValue('L' . $num, $item['mobile']);
                switch($item['type']) {
                    case 'increase':
                        $objPHPExcel->getActiveSheet()->SetCellValue('M' . $num, jdate('Y/m/d', strtotime($item['created_at'])));
                        break;
                    case 'decrease':
                        $objPHPExcel->getActiveSheet()->SetCellValue('M' . $num, ' ');
                        break;
                }

                switch ($item['status']) {
                    case "paid_increase":
                        $objPHPExcel->getActiveSheet()->SetCellValue('N' . $num, 'واریز شده توسط مشتری');
                        break;
                    case "pending_increase":
                        $objPHPExcel->getActiveSheet()->SetCellValue('N' . $num, 'در انتظار واریز مشتری');
                        break;
                }

                $num++;

            }
        }


        // Rename sheet
        $objPHPExcel->getActiveSheet()->setTitle('Wallet');

        // Save Excel 2007 file

        $objWriter = new \PHPExcel_Writer_Excel2007($objPHPExcel);

        $path_blank = str_replace(get_ev('path_live'), 'httpdocs/upload', base_path('files'));

        $fileName = time() . '_save.xlsx';


        $objWriter->save("{$path_blank}/{$fileName}");

        return success_template(['link'=>'https://sandbox.sbm24.net/upload' . '/files/' . $fileName]);

    }
    public function export_cod()
    {
        $search = $this->request->get('search' , NULL);
        $order_by = $this->request->get('order_by' , 'doctor_wallets.paid_at');
        $sort = $this->request->get('sort','ASC');
        $page = $this->request->get('page');
        $per_page = $this->request->get('per_page',7);

        $filter_status = $this->request->get('filter_status');
        $filter_settlement_type = $this->request->get('filter_settlement_type');
        $filter_service = $this->request->get('filter_service');
        $filter_mobile = $this->request->get('filter_mobile');
        $filter_start_created_at = $this->request->get('filter_start_created_at');
        $filter_end_created_at = $this->request->get('filter_end_created_at');
        $filter_start_paid_at = $this->request->get('filter_start_paid_at');
        $filter_end_paid_at = $this->request->get('filter_end_paid_at');

        if($page == null){
            $this->request->merge(['page'=>1]);
        }

        $doctor = auth()->user();

        $walletTransactions = DoctorWallet::
            leftJoin('users', function ($query) {
                $query->on('users.id','=','doctor_wallets.user_id');
            })
            ->orderBy($order_by, $sort)
            ->select(
                'amount',
                'doctor_wallets.created_at',
                'description',
                'paid_at',
                'transaction_date',
                'service',
                'settlement_type',
                'doctor_wallets.status',
                DB::raw('IF(doctor_wallets.status = "paid_increase" || doctor_wallets.status = "paid_decrease", bank_wage , null ) as wage'),
                'service_wage',
                DB::raw('IF(doctor_wallets.status = "paid_increase" || doctor_wallets.status= "pending_increase", amount - (service_wage + bank_wage) , null ) as doctor_wage'),
                'transId',
                'type',
                'payment_type',
                'fullName',
                'mobile'
            )
            ->where(['payment_type'=>'COD','doctor_id'=> $doctor->id])
            ->whereIn('doctor_wallets.status',['paid_increase','paid_decrease'])
            ->when($filter_status,function ($query) use ($filter_status){
                $query->where('doctor_wallets.status',$filter_status);
            })
            ->when($filter_service,function ($query) use ($filter_service){
                $query->where('service',$filter_service);
            })
            ->when($filter_mobile,function ($query) use ($filter_mobile){
                $query->where('users.mobile',$filter_mobile);
            })
            ->when($filter_settlement_type,function ($query) use ($filter_settlement_type){
                $query->where('settlement_type',$filter_settlement_type);
            })
            ->when($filter_start_created_at,function ($query) use ($filter_start_created_at){
                $query->whereDate('doctor_wallets.created_at', '>=',$filter_start_created_at);
            })
            ->when($filter_end_created_at,function ($query) use ($filter_end_created_at){
                $query->whereDate('doctor_wallets.created_at', '<=',$filter_end_created_at);
            })
            ->when($filter_start_paid_at,function ($query) use ($filter_start_paid_at){
                $query->whereDate('doctor_wallets.paid_at', '>=',$filter_start_paid_at);
            })
            ->when($filter_end_paid_at,function ($query) use ($filter_end_paid_at){
                $query->whereDate('doctor_wallets.paid_at', '<=',$filter_end_paid_at);
            })
            ->when($search,function ($query) use ($search){
                $query->whereHas('user',function ($query) use ($search){
                    $query->where('users.fullname', 'LIKE', '%' . $search . '%')
                        ->orWhere('users.mobile', 'LIKE', '%' . $search . '%');
                })
                ->orWhere('doctor_wallets.description' , 'LIKE' , '%'. $search .'%')
                ->orWhere('doctor_wallets.transId' , 'LIKE' , '%'. $search .'%');

            })->get();


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
        $objPHPExcel->getActiveSheet()->SetCellValue('A1', 'تاریخ کارکرد');
        $objPHPExcel->getActiveSheet()->SetCellValue('B1', 'کانال');
        $objPHPExcel->getActiveSheet()->SetCellValue('C1', 'عملیات');
        $objPHPExcel->getActiveSheet()->SetCellValue('D1', 'مبلغ');
        $objPHPExcel->getActiveSheet()->SetCellValue('E1', 'واحد');
        $objPHPExcel->getActiveSheet()->SetCellValue('F1', 'کارمزد بانکی و سیستمی');
        $objPHPExcel->getActiveSheet()->SetCellValue('G1', 'کارمزد SBM24');
        $objPHPExcel->getActiveSheet()->SetCellValue('H1', 'مبلغ پس از کسورات');
        $objPHPExcel->getActiveSheet()->SetCellValue('I1', 'اعتبار پس از تراکنش');
        $objPHPExcel->getActiveSheet()->SetCellValue('J1', 'توضیحات');
        $objPHPExcel->getActiveSheet()->SetCellValue('K1', 'نام و نام خانوادگی مشتری');
        $objPHPExcel->getActiveSheet()->SetCellValue('L1', 'موبایل مشتری');
        $objPHPExcel->getActiveSheet()->SetCellValue('M1', 'تاریخ ایجاد لینک');
        $objPHPExcel->getActiveSheet()->SetCellValue('N1', 'وضعیت');

        $num = 2;

        $data = [
          'doctor_id' => $doctor->id,
          'payment_type' => 'COD',
          'start_paid_at' => $filter_start_paid_at,
          'end_paid_at' => $filter_end_paid_at,
        ];

        $account_balance = json_decode($this->accountBalance($data)->getContent())->data;

        $walletBalance = 0;
        if ($filter_start_paid_at || $filter_end_paid_at) {
            $walletBalance = $account_balance->account_balance;
        }
        if ($walletTransactions) {
            foreach ($walletTransactions as $item) {

                if ($item['status'] == 'paid_increase'){
                    $walletBalance += $item['doctor_wage'];
                }elseif ( $item['status'] == 'paid_decrease'){
                    $walletBalance += $item['amount'];
                }

                $objPHPExcel->getActiveSheet()->SetCellValue('A' . $num, jdate('Ymd', strtotime($item['paid_at'])));

                switch($item['payment_type']) {
                    case 'COD':
                        $objPHPExcel->getActiveSheet()->SetCellValue('B' . $num, 'پرداخت در محل');
                        break;
                    case 'Wallet':
                        $objPHPExcel->getActiveSheet()->SetCellValue('B' . $num, 'درگاه پرداخت آنلاین');
                        break;
                }

                switch($item['type']) {
                    case 'increase':
                        $objPHPExcel->getActiveSheet()->SetCellValue('C' . $num, 'واریز');
                        break;
                    case 'decrease':
                        $objPHPExcel->getActiveSheet()->SetCellValue('C' . $num, 'برداشت');
                        break;
                }

                $objPHPExcel->getActiveSheet()->SetCellValue('D' . $num, (string)$item['amount']);
                switch($item['settlement_type']) {
                    case 'tether':
                    case 'other':
                        $objPHPExcel->getActiveSheet()->SetCellValue('E' . $num, 'تتر');
                        break;
                    case 'rial':
                    case '':
                        $objPHPExcel->getActiveSheet()->SetCellValue('E' . $num, 'ریال');
                        break;
                }

                $objPHPExcel->getActiveSheet()->SetCellValue('F' . $num, (string)$item['wage']);
                $objPHPExcel->getActiveSheet()->SetCellValue('G' . $num, (string)$item['service_wage']);
                $objPHPExcel->getActiveSheet()->SetCellValue('H' . $num, (string)$item['doctor_wage']);

                $objPHPExcel->getActiveSheet()->SetCellValue('I' . $num, (string)$walletBalance);
                $objPHPExcel->getActiveSheet()->SetCellValue('J' . $num, $item['description'] . ' - '  );

                $objPHPExcel->getActiveSheet()->SetCellValue('K' . $num, $item['fullName']);
                $objPHPExcel->getActiveSheet()->SetCellValue('L' . $num, $item['mobile']);
                switch($item['type']) {
                    case 'increase':
                        $objPHPExcel->getActiveSheet()->SetCellValue('M' . $num, jdate('Y/m/d', strtotime($item['created_at'])));
                        break;
                    case 'decrease':
                        $objPHPExcel->getActiveSheet()->SetCellValue('M' . $num, ' ');
                        break;
                }

                switch ($item['status']) {
                    case "paid_increase":
                        $objPHPExcel->getActiveSheet()->SetCellValue('N' . $num, 'واریز شده توسط مشتری');
                        break;
                    case "pending_increase":
                        $objPHPExcel->getActiveSheet()->SetCellValue('N' . $num, 'در انتظار واریز مشتری');
                        break;
                }

                $num++;

            }
        }


        // Rename sheet
        $objPHPExcel->getActiveSheet()->setTitle('Wallet');

        // Save Excel 2007 file

        $objWriter = new \PHPExcel_Writer_Excel2007($objPHPExcel);

        $path_blank = str_replace(get_ev('path_live'), 'httpdocs/upload', base_path('files'));

        $fileName = time() . '_save.xlsx';


        $objWriter->save("{$path_blank}/{$fileName}");

        return success_template(['link'=>'https://sandbox.sbm24.net/upload' . '/files/' . $fileName]);

    }
    public function export_cod_old()
    {
        $search = $this->request->get('search' , NULL);
        $order_by = $this->request->get('order_by' , 'doctor_wallets.created_at');
        $sort = $this->request->get('sort','ASC');
        $page = $this->request->get('page');
        $per_page = $this->request->get('per_page',7);

        $filter_status = $this->request->get('filter_status');
        $filter_settlement_type = $this->request->get('filter_settlement_type');
        $filter_service = $this->request->get('filter_service');
        $filter_mobile = $this->request->get('filter_mobile');
        $filter_start_created_at = $this->request->get('filter_start_created_at');
        $filter_end_created_at = $this->request->get('filter_end_created_at');
        $filter_start_paid_at = $this->request->get('$filter_start_paid_at');
        $filter_end_paid_at = $this->request->get('filter_end_paid_at');

        if($page == null){
            $this->request->merge(['page'=>1]);
        }

        $doctor = auth()->user();

        $walletTransactions = DoctorWallet::
            leftJoin('users', function ($query) {
                $query->on('users.id','=','doctor_wallets.user_id');
            })
            ->orderBy($order_by, $sort)
            ->select(
                'amount',
                'doctor_wallets.created_at',
                'description',
                'paid_at',
                'service',
                'settlement_type',
                'doctor_wallets.status',
                DB::raw('IF(doctor_wallets.status = "paid_increase" || doctor_wallets.status = "paid_decrease", bank_wage , null ) as wage'),
                'service_wage',
                DB::raw('IF(doctor_wallets.status = "paid_increase" || doctor_wallets.status= "pending_increase", amount - (service_wage + bank_wage) , null ) as doctor_wage'),
                'transId',
                'transaction_date',
                'type',
                'fullName',
                'mobile'
            )
            ->where(['doctor_id'=>$doctor->id,'payment_type' => 'COD'])
            ->when($filter_status,function ($query) use ($filter_status){
                $query->where('doctor_wallets.status',$filter_status);
            })
            ->when($filter_service,function ($query) use ($filter_service){
                $query->where('service',$filter_service);
            })
            ->when($filter_mobile,function ($query) use ($filter_mobile){
                $query->where('users.mobile',$filter_mobile);
            })
            ->when($filter_settlement_type,function ($query) use ($filter_settlement_type){
                $query->where('settlement_type',$filter_settlement_type);
            })
            ->when($filter_start_created_at,function ($query) use ($filter_start_created_at){
                $query->whereDate('doctor_wallets.created_at', '>=',$filter_start_created_at);
            })
            ->when($filter_end_created_at,function ($query) use ($filter_end_created_at){
                $query->whereDate('doctor_wallets.created_at', '<=',$filter_end_created_at);
            })
            ->when($filter_start_paid_at,function ($query) use ($filter_start_paid_at){
                $query->whereDate('doctor_wallets.paid_at', '>=',$filter_start_paid_at);
            })
            ->when($filter_end_paid_at,function ($query) use ($filter_end_paid_at){
                $query->whereDate('doctor_wallets.paid_at', '<=',$filter_end_paid_at);
            })
            ->when($search,function ($query) use ($search){
                $query->whereHas('user',function ($query) use ($search){
                    $query->where('users.fullname', 'LIKE', '%' . $search . '%')
                        ->orWhere('users.mobile', 'LIKE', '%' . $search . '%');
                })
                ->orWhere('doctor_wallets.description' , 'LIKE' , '%'. $search .'%')
                ->orWhere('doctor_wallets.transId' , 'LIKE' , '%'. $search .'%');

            })->get();


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
        $objPHPExcel->getActiveSheet()->SetCellValue('A1', 'نام و نام خانوادگی');
        $objPHPExcel->getActiveSheet()->SetCellValue('B1', 'موبایل');
        $objPHPExcel->getActiveSheet()->SetCellValue('C1', 'نوع خدمت');
        $objPHPExcel->getActiveSheet()->SetCellValue('D1', 'مبلغ(ریال)');
        $objPHPExcel->getActiveSheet()->SetCellValue('E1', 'کارمزد بانکی (ریال)');
        $objPHPExcel->getActiveSheet()->SetCellValue('F1', 'وضعیت');
        $objPHPExcel->getActiveSheet()->SetCellValue('G1', 'تاریخ فراخوان');
        $objPHPExcel->getActiveSheet()->SetCellValue('H1', 'تاریخ واریز به کیف پول');
        $objPHPExcel->getActiveSheet()->SetCellValue('M1', 'تاریخ کارکرد');
        $objPHPExcel->getActiveSheet()->SetCellValue('I1', 'شناسه پرداخت');
        $objPHPExcel->getActiveSheet()->SetCellValue('J1', 'توضیحات');
        $objPHPExcel->getActiveSheet()->SetCellValue('K1', 'کارمزد خدمت');
        $objPHPExcel->getActiveSheet()->SetCellValue('L1', 'سهم پزشک');

        $num = 2;

        if ($walletTransactions) {
            foreach ($walletTransactions as $item) {

                $objPHPExcel->getActiveSheet()->SetCellValue('A' . $num, $item['fullName']);
                $objPHPExcel->getActiveSheet()->SetCellValue('B' . $num, $item['mobile']);

                switch($item['service']) {
                    case 'surgery':
                        $objPHPExcel->getActiveSheet()->SetCellValue('C' . $num, 'عمل جراحی');
                        break;
                    case 'other':
                        $objPHPExcel->getActiveSheet()->SetCellValue('C' . $num, 'سایر خدمات');
                        break;
                    case 'visit':
                        $objPHPExcel->getActiveSheet()->SetCellValue('C' . $num, 'ویزیت');
                        break;
                }

                $objPHPExcel->getActiveSheet()->SetCellValue('D' . $num, (string)$item['amount']);
                $objPHPExcel->getActiveSheet()->SetCellValue('E' . $num, (string)$item['wage']);

                switch ($item['status']) {
                    case "paid_increase":
                        $objPHPExcel->getActiveSheet()->SetCellValue('F' . $num, 'واریز شده توسط بیمار');
                        break;
                    case "pending_increase":
                        $objPHPExcel->getActiveSheet()->SetCellValue('F' . $num, 'در انتظار واریز بیمار');
                        break;
                    case "pending_decrease":
                        $objPHPExcel->getActiveSheet()->SetCellValue('F' . $num, 'در انتظار تسویه');
                        break;
                    case "paid_decrease":
                        $objPHPExcel->getActiveSheet()->SetCellValue('F' . $num, 'تسویه شده');
                        break;
                }

                $objPHPExcel->getActiveSheet()->SetCellValue('G' . $num, jdate('Y/m/d', strtotime($item['created_at'])));
                $objPHPExcel->getActiveSheet()->SetCellValue('H' . $num, jdate('Y/m/d', strtotime($item['paid_at'])));
                $objPHPExcel->getActiveSheet()->SetCellValue('M' . $num, (string)$item['transaction_date']);
                $objPHPExcel->getActiveSheet()->SetCellValue('I' . $num, (string)$item['transId']);
                $objPHPExcel->getActiveSheet()->SetCellValue('J' . $num, $item['description']);
                $objPHPExcel->getActiveSheet()->SetCellValue('K' . $num, (string)$item['service_wage']);
                $objPHPExcel->getActiveSheet()->SetCellValue('L' . $num, (string)$item['doctor_wage']);


                $num++;

            }
        }


        // Rename sheet
        $objPHPExcel->getActiveSheet()->setTitle('COD');

        // Save Excel 2007 file

        $objWriter = new \PHPExcel_Writer_Excel2007($objPHPExcel);

        $path_blank = str_replace(get_ev('path_live'), 'httpdocs/upload', base_path('files'));

        $fileName = time() . '_save.xlsx';


        $objWriter->save("{$path_blank}/{$fileName}");

        return success_template(['link'=>'https://sandbox.sbm24.net/upload' . '/files/' . $fileName]);

    }

    public function export_all()
    {
        $search = $this->request->get('search' , NULL);
        $order_by = $this->request->get('order_by' , 'doctor_wallets.created_at');
        $sort = $this->request->get('sort','ASC');
        $page = $this->request->get('page');
        $per_page = $this->request->get('per_page',7);

        $filter_status = $this->request->get('filter_status');
        $filter_settlement_type = $this->request->get('filter_settlement_type');
        $filter_service = $this->request->get('filter_service');
        $filter_mobile = $this->request->get('filter_mobile');
        $filter_start_created_at = $this->request->get('filter_start_created_at');
        $filter_end_created_at = $this->request->get('filter_end_created_at');
        $filter_start_paid_at = $this->request->get('$filter_start_paid_at');
        $filter_end_paid_at = $this->request->get('filter_end_paid_at');

        if($page == null){
            $this->request->merge(['page'=>1]);
        }

        $doctor = auth()->user();

        $walletTransactions = DoctorWallet::
        leftJoin('users', function ($query) {
            $query->on('users.id','=','doctor_wallets.user_id');
        })
            ->orderBy($order_by, $sort)
            ->select(
                'amount',
                'doctor_wallets.created_at',
                'description',
                'paid_at',
                'transaction_date',
                'service',
                'settlement_type',
                'doctor_wallets.status',
                DB::raw('IF(doctor_wallets.status = "paid_increase" || doctor_wallets.status = "paid_decrease", bank_wage , null ) as wage'),
                'service_wage',
                DB::raw('IF(doctor_wallets.status = "paid_increase" || doctor_wallets.status= "pending_increase", amount - (service_wage + bank_wage) , null ) as doctor_wage'),
                'transId',
                'type',
                'payment_type',
                'fullName',
                'mobile'
            )
            ->where('doctor_id',$doctor->id)
            ->when($filter_status,function ($query) use ($filter_status){
                $query->where('doctor_wallets.status',$filter_status);
            })
            ->when($filter_service,function ($query) use ($filter_service){
                $query->where('service',$filter_service);
            })
            ->when($filter_mobile,function ($query) use ($filter_mobile){
                $query->where('users.mobile',$filter_mobile);
            })
            ->when($filter_settlement_type,function ($query) use ($filter_settlement_type){
                $query->where('settlement_type',$filter_settlement_type);
            })
            ->when($filter_start_created_at,function ($query) use ($filter_start_created_at){
                $query->whereDate('doctor_wallets.created_at', '>=',$filter_start_created_at);
            })
            ->when($filter_end_created_at,function ($query) use ($filter_end_created_at){
                $query->whereDate('doctor_wallets.created_at', '<=',$filter_end_created_at);
            })
            ->when($filter_start_paid_at,function ($query) use ($filter_start_paid_at){
                $query->whereDate('doctor_wallets.paid_at', '>=',$filter_start_paid_at);
            })
            ->when($filter_end_paid_at,function ($query) use ($filter_end_paid_at){
                $query->whereDate('doctor_wallets.paid_at', '<=',$filter_end_paid_at);
            })
            ->when($search,function ($query) use ($search){
                $query->whereHas('user',function ($query) use ($search){
                    $query->where('users.fullname', 'LIKE', '%' . $search . '%')
                        ->orWhere('users.mobile', 'LIKE', '%' . $search . '%');
                })
                    ->orWhere('doctor_wallets.description' , 'LIKE' , '%'. $search .'%')
                    ->orWhere('doctor_wallets.transId' , 'LIKE' , '%'. $search .'%');

            })->get();


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

        $objPHPExcel->getActiveSheet()->SetCellValue('A1', 'تاریخ کارکرد');
        $objPHPExcel->getActiveSheet()->SetCellValue('B1', 'کانال');
        $objPHPExcel->getActiveSheet()->SetCellValue('C1', 'عملیات');
        $objPHPExcel->getActiveSheet()->SetCellValue('D1', 'مبلغ');
        $objPHPExcel->getActiveSheet()->SetCellValue('E1', 'واحد');
        $objPHPExcel->getActiveSheet()->SetCellValue('F1', 'کارمزد بانکی و سیستمی');
        $objPHPExcel->getActiveSheet()->SetCellValue('G1', 'کارمزد SBM24');
        $objPHPExcel->getActiveSheet()->SetCellValue('H1', 'مبلغ پس از کسورات');
        $objPHPExcel->getActiveSheet()->SetCellValue('I1', 'اعتبار پس از تراکنش');
        $objPHPExcel->getActiveSheet()->SetCellValue('J1', 'توضیحات');
        $objPHPExcel->getActiveSheet()->SetCellValue('K1', 'نام و نام خانوادگی مشتری');
        $objPHPExcel->getActiveSheet()->SetCellValue('L1', 'موبایل مشتری');
        $objPHPExcel->getActiveSheet()->SetCellValue('M1', 'تاریخ ایجاد لینک');
        $objPHPExcel->getActiveSheet()->SetCellValue('N1', 'وضعیت');


        $num = 2;
        $codBalance = 0;
        $walletBalance = 0;
        if ($walletTransactions) {
            foreach ($walletTransactions as $item) {

                if ($item['status'] == 'paid_increase' || $item['status'] == 'paid_decrease'){
                    $codBalances = $item['payment_type'] == 'COD' ? $codBalance + $item['doctor_wage'] : $codBalance;
                    $walletBalances = $item['payment_type'] == 'Wallet' ? $walletBalance + $item['doctor_wage'] : $walletBalance;
                }

                $objPHPExcel->getActiveSheet()->SetCellValue('A' . $num, $item['transaction_date']);

                switch($item['payment_type']) {
                    case 'COD':
                        $objPHPExcel->getActiveSheet()->SetCellValue('B' . $num, 'پرداخت در محل');
                        break;
                    case 'Wallet':
                        $objPHPExcel->getActiveSheet()->SetCellValue('B' . $num, 'درگاه پرداخت آنلاین');
                        break;
                }

                switch($item['type']) {
                    case 'increase':
                        $objPHPExcel->getActiveSheet()->SetCellValue('C' . $num, 'واریز');
                        break;
                    case 'decrease':
                        $objPHPExcel->getActiveSheet()->SetCellValue('C' . $num, 'برداشت');
                        break;
                }

                $objPHPExcel->getActiveSheet()->SetCellValue('D' . $num, (string)$item['amount']);
                switch($item['settlement_type']) {
                    case 'tether':
                    case 'other':
                        $objPHPExcel->getActiveSheet()->SetCellValue('E' . $num, 'تتر');
                        break;
                    case 'rial':
                    case '':
                        $objPHPExcel->getActiveSheet()->SetCellValue('E' . $num, 'ریال');
                        break;
                }

                $objPHPExcel->getActiveSheet()->SetCellValue('F' . $num, (string)$item['wage']);
                $objPHPExcel->getActiveSheet()->SetCellValue('G' . $num, (string)$item['service_wage']);
                $objPHPExcel->getActiveSheet()->SetCellValue('H' . $num, (string)$item['doctor_wage']);
                switch($item['payment_type']) {
                    case 'COD':
                        $objPHPExcel->getActiveSheet()->SetCellValue('I' . $num, (string)$codBalances);
                        break;
                    case 'Wallet':
                        $objPHPExcel->getActiveSheet()->SetCellValue('I' . $num, (string)$walletBalances);
                        break;
                }                $objPHPExcel->getActiveSheet()->SetCellValue('J' . $num, $item['description'] . ' - '  );

                $objPHPExcel->getActiveSheet()->SetCellValue('K' . $num, $item['fullName']);
                $objPHPExcel->getActiveSheet()->SetCellValue('L' . $num, $item['mobile']);
                switch($item['type']) {
                    case 'increase':
                        $objPHPExcel->getActiveSheet()->SetCellValue('M' . $num, jdate('Y/m/d', strtotime($item['created_at'])));
                        break;
                    case 'decrease':
                        $objPHPExcel->getActiveSheet()->SetCellValue('M' . $num, ' ');
                        break;
                }

                switch ($item['status']) {
                    case "paid_increase":
                        $objPHPExcel->getActiveSheet()->SetCellValue('N' . $num, 'واریز شده توسط مشتری');
                        break;
                    case "pending_increase":
                        $objPHPExcel->getActiveSheet()->SetCellValue('N' . $num, 'در انتظار واریز مشتری');
                        break;
                }

                $num++;

            }
        }


        // Rename sheet
        $objPHPExcel->getActiveSheet()->setTitle('Wallet');

        // Save Excel 2007 file

        $objWriter = new \PHPExcel_Writer_Excel2007($objPHPExcel);

        $path_blank = str_replace(get_ev('path_live'), 'httpdocs/upload', base_path('files'));

        $fileName = time() . '_save.xlsx';


        $objWriter->save("{$path_blank}/{$fileName}");

        return success_template(['link'=>'https://sandbox.sbm24.net/upload' . '/files/' . $fileName]);

    }

    public function accountBalance($data)
    {
        $data['status'] = [['paid_increase']];
        $paid_increase = $this->calculateWallet($data);

        $data['status'] = [['paid_decrease']];
        $paid_decrease = $this->calculateWallet($data);

        $data['status'] = [['paid_increase','paid_decrease','pending_decrease']];
        $data['column'] = 'bank_wage';
        $bank_wages = $this->calculateWallet($data);

        $data['status'] = [['paid_increase']];
        $data['column'] = 'service_wage';
        $service_wages = $this->calculateWallet($data);

        $data['status'] = [['pending_decrease']];
        $pending_decrease = $this->calculateWallet($data);

        $net = $paid_increase + $paid_decrease - $bank_wages;

        $account_accessible = $net + $pending_decrease - abs($service_wages);
        $bank_settlement_wage = $this->calculateBankSettlementWage($account_accessible);

        return success_template([
            'account_balance' => abs($net - abs($service_wages) ),
            'account_accessible' => abs($account_accessible - $bank_settlement_wage),
            'non_rial_account_accessible' => abs($account_accessible - $bank_settlement_wage),
            'pending_decrease' => abs($pending_decrease)
        ]);
    }

    public function calculateWallet($data)
    {
        $doctor_id = $data['doctor_id'];
        $status = $data['status'];
        $payment_type = $data['payment_type'];
        $start_paid_at = $data['start_paid_at'] ?? null;
        $end_paid_at = $data['end_paid_at'] ?? null;
        $column = $data['column'] ?? 'amount';
        return DoctorWallet::where(['doctor_id'=>$doctor_id , 'payment_type' => $payment_type])
            ->when($start_paid_at,function ($query) use ($start_paid_at){
                $query->whereDate('doctor_wallets.paid_at', '>=',$start_paid_at);
            })
            ->when($end_paid_at,function ($query) use ($end_paid_at){
                $query->whereDate('doctor_wallets.paid_at', '<=',$end_paid_at);
            })
            ->whereIn('status',$status)->sum($column);
    }

    public function calculateBankSettlementWage($amount, $checkoutDelay = 0)
    {
        if($checkoutDelay == 0){
            $bankWage = $amount <= 50000000 ? 10000 : $amount * 0.002;
            $bankWage = $bankWage < 50000 ? $bankWage : 50000;
        }elseif($checkoutDelay == -1){
            $bankWage = $amount <= 50000000 ? 10000 : $amount * 0.003;
            $bankWage = $bankWage < 50000 ? $bankWage : 50000;
        }
        return $bankWage;
    }

}
