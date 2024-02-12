<?php

namespace App\Http\Controllers\Admin\Bill;

use App\Model\Visit\EventReserves;
use App\Model\Visit\TransactionCredit;
use App\Model\Visit\TransactionDoctor;
use App\Model\Visit\TransactionReserve;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class BillController extends Controller
{
    protected $request;

    public function __construct(Request $request)
    {
        $this->middleware('admin');

        date_default_timezone_set("Asia/Tehran");
        $this->request = $request;

        require(base_path('app/jdf.php'));
    }

    private function DoneAPayment(EventReserves $event)
    {
        $transaction = TransactionReserve::where('user_id', $event->user_id)
            ->where('doctor_id', $event->doctor_id)
            ->where('calender_id', $event->calender_id)
            ->where('status', 'paid')->first();
        if ($transaction) {
            $transactionDr = TransactionDoctor::where('user_id', $event->user_id)
                ->where('doctor_id', $event->doctor_id)->where('event_id', $event->id)
                ->where('status', 'pending')->first();
            if ($transactionDr) {
                $transactionDr->status = 'paid';
                $transactionDr->message = 'پرداخت هفتگی توسط ادمین';
                $transactionDr->save();
                return true;
            }
        }
        return false;
    }

    public function done(EventReserves $event = null)
    {
        if ($event) {
            if ($this->DoneAPayment($event))
                return redirect()->back()->with(['success' => 'با موفقیت انجام شد'])->withInput();
            return redirect()->back()->with(['error' => 'اطلاعات پرداخت بیمار یافت نشد'])->withInput();
        } elseif (\request()->has('event_id_done')) {
            foreach (\request()->input('event_id_done') as $item) {
                $event = EventReserves::find($item);
                $response = $this->DoneAPayment($event);
                if ($response)
                    continue;
                else
                    return redirect()->back()->with(['error' => 'اطلاعات پرداخت بیمار یافت نشد'])->withInput();

            }
            return redirect()->back()->with(['success' => 'با موفقیت انجام شد'])->withInput();
        }
        return redirect()->back()->with(['error' => 'اطلاعات پرداخت بیمار یافت نشد'])->withInput();
    }


    public function charge()
    {

        $where_array = array();

        //filter set to query
        $filter_name = trim($this->request->get('filter_user'));
        if ($filter_name) {
            $where_array[] = array('users.fullname', "LIKE", "%" . $filter_name . "%");
        }

        $filter_start_date = $this->request->get('filter_start_date');
        if ($filter_start_date) {

            $date = explode('/', $filter_start_date);
            $date = jalali_to_gregorian($date[0], $date[1], $date[2], '-');

            $where_array[] = array('transaction_credits.created_at', '>=', $date);
        }

        $filter_end_date = $this->request->get('filter_end_date');
        if ($filter_end_date) {

            $date = explode('/', $filter_end_date);
            $date = jalali_to_gregorian($date[0], $date[1], $date[2], '-');

            $where_array[] = array('transaction_credits.created_at', '<=', $date);
        }

        $filter_code = $this->request->get('filter_code');
        if ($filter_code) {

            $where_array[] = array('transaction_credits.id', str_replace('SC', '', $filter_code));
        }


        $request = TransactionCredit::join('users', 'users.id', '=', 'transaction_credits.user_id')
            ->where($where_array)
            ->where('transaction_credits.status', 'paid')
            ->orderBy('transaction_credits.created_at', 'DESC')
            ->select('transaction_credits.*', 'users.fullname')->paginate(35);


        return view('admin/bill/charge', ['request' => $request]);

    }


    public function doctors()
    {

        $where_array = array();

        //filter set to query
        $filter_name = trim($this->request->get('filter_user'));
        if ($filter_name) {
            $where_array[] = array('users.fullname', "LIKE", "%" . $filter_name . "%");
        }

        $filter_start_date = $this->request->get('filter_start_date');
        if ($filter_start_date) {

            $date = explode('/', $filter_start_date);
            $date = jalali_to_gregorian($date[0], $date[1], $date[2], '-');

            $where_array[] = array('transaction_doctors.created_at', '>=', $date);
        }

        $filter_end_date = $this->request->get('filter_end_date');
        if ($filter_end_date) {

            $date = explode('/', $filter_end_date);
            $date = jalali_to_gregorian($date[0], $date[1], $date[2], '-');

            $where_array[] = array('transaction_doctors.created_at', '<=', $date);
        }

        $filter_code = $this->request->get('filter_code');
        if ($filter_code) {

            $where_array[] = array('transaction_doctors.id', str_replace('SD', '', $filter_code));
        }

        $filter_status = $this->request->get('filter_status');
        if ($filter_status) {

            $where_array[] = array('transaction_doctors.status', $filter_status);
        }


        $request = TransactionDoctor::join('users', 'users.id', '=', 'transaction_doctors.doctor_id')
            ->where($where_array)
            ->orderBy('transaction_doctors.created_at', 'DESC')
            ->select('transaction_doctors.*', 'users.fullname')->paginate(35);


        return view('admin/bill/doctors', ['request' => $request]);

    }

    public function ActionDoctor()
    {


        $ValidData = $this->validate($this->request, [
            'doctors' => 'required',
            'status' => 'required',
        ]);

        if ($ValidData['doctors']) {

            foreach ($ValidData['doctors'] as $doctor) {

                $request = TransactionDoctor::where('id', $doctor)->first();
                if ($request) {

                    $request->status = $ValidData['status'];
                    $request->save();

                }

            }

        } else {
            return back()->with('error', 'یک تراکنش را انتخاب کنید.')->withInput();
        }

        return redirect('cp-manager/bill/doctors')->with('success', 'وضعیت جدید با موفقیت ثبت شد.')->withInput();

    }


    public function reserves()
    {

        $where_array = array();

        //filter set to query
        $filter_name = trim($this->request->get('filter_user'));
        if ($filter_name) {
            $where_array[] = array('users.fullname', "LIKE", "%" . $filter_name . "%");
        }

        $filter_pay_status = trim($this->request->get('filter_pay_status','pending'));
        if ($filter_pay_status) {
            $where_array[] = array('transaction_reserves.status',  $filter_pay_status );
        }


        $filter_user_username = trim($this->request->get('filter_user_username'));
        if ($filter_user_username) {

            $user = User::where('fullname', "LIKE", "%" . $filter_user_username . "%")->first();
            if ($user) {
                $where_array[] = array('transaction_reserves.user_id', $user->id);
            } else {
                $where_array[] = array('transaction_reserves.user_id', 0);
            }

        }

        $filter_start_date = $this->request->get('filter_start_date');

        if ($filter_start_date) {

            $date = explode('/', $filter_start_date);
            $date = jalali_to_gregorian($date[0], $date[1], $date[2], '-');

            $where_array[] = array('transaction_reserves.created_at', '>=',$date);
        }else{
            $where_array[] = array('transaction_reserves.created_at', '>=', Carbon::now()->format('Y-m-d'));
        }

        $filter_end_date = $this->request->get('filter_end_date');
        if ($filter_end_date) {

            $date = explode('/', $filter_end_date);
            $date = jalali_to_gregorian($date[0], $date[1], $date[2], '-');

            $where_array[] = array('transaction_reserves.created_at', '<=', $date);
        }

        $filter_code = $this->request->get('filter_code');
        if ($filter_code) {

            $where_array[] = array('transaction_doctors.id', str_replace('SD', '', $filter_code));
        }

        $filter_status = $this->request->get('filter_status');
        if ($filter_status) {

            $where_array[] = array('transaction_doctors.status', $filter_status);
        }

        $request = TransactionReserve::join('users as doctors', 'doctors.id', '=', 'transaction_reserves.doctor_id')
            ->join('users','users.id','transaction_reserves.user_id')
            ->join('doctor_calenders', 'doctor_calenders.id', '=', 'transaction_reserves.calender_id')
//            ->where('transaction_reserves.status', 'paid')
            ->where($where_array);
        if ($filter_pay_status && $filter_pay_status == 'pending'){
            $request = $request->whereNotIn('transaction_reserves.user_id',function ($q){
                $q->select('tr.user_id')->from('transaction_reserves as tr')
                    ->where('tr.status', 'paid')
                    ->whereDate('tr.created_at', '=', Carbon::now()->format('Y-m-d') );
            });
        }

//        $GLOBALS['failTransactionsCount'] = TransactionReserve::where('transaction_reserves.status','pending')
//            ->whereDate('transaction_reserves.created_at', '=', Carbon::now()->format('Y-m-d') )
//            ->whereNotIn('transaction_reserves.user_id',function ($q){
//            $q->select('tr.user_id')->from('transaction_reserves as tr')
//                ->where('tr.status', 'paid')
//                ->whereDate('tr.created_at', '=', Carbon::now()->format('Y-m-d') );
//        })->count();


        $request = $request->select(
                \DB::raw('TIME(transaction_reserves.created_at) as created_time'),
                'transaction_reserves.status as pay_status',
                'transaction_reserves.transId as transId',
                'transaction_reserves.created_at',
                'transaction_reserves.id',
                'transaction_reserves.amount',
                'transaction_reserves.amount_paid',
                'transaction_reserves.message',
                'doctor_calenders.fa_data as date',
                'doctor_calenders.time',
                'transaction_reserves.user_id',
                'doctors.fullname as dr_name',
                'users.mobile',
                'users.email',
                'users.fullname as user_name'
            )
            ->orderBy('transaction_reserves.created_at', 'desc')
            ->groupBy('transaction_reserves.user_id')
            ->paginate(35);


        $RequestFull = [];
//        if ($request) {
//            foreach ($request as $item) {
//
//                $user = User::where('id', $item['user_id'])->first();
//
//                $RequestFull[] = [
//                    'id' => $item['key'],
//                    'amount' => number_format($item['amount']),
//                    'amount_paid' => number_format($item['amount_paid']),
//                    'credit' => number_format(($item['amount'] - $item['amount_paid'])),
//                    'dr_name' => $item['user_name'],
//                    'pay_status' => $item['pay_status'],
//                    'transId' => $item['transId'],
//                    'user_name' => $user->fullname,
//                    'mobile' => $user->mobile,
//                    'message' => $item['message'],
//                    'date' => $item['fa_data'],
//                    'time' => $item['time'] . ' الی ' . ($item['time'] + 1),
//                ];
//            }
//        }


        return view('admin/bill/reserves', ['RequestFull' => $RequestFull, 'request' => $request]);

    }

    public function editTransactionReserve($id)
    {
        $request = TransactionReserve::find($id);
        return view('admin.bill.reservesEdit',compact('request'));
    }

    public function updateTransactionReserve(TransactionReserve $transactionReserve)
    {
        $transactionReserve->message = $this->request->description;
        $transactionReserve->save();
        return redirect()->route('transactionReserve.index')->with(['success'=>'توضیحات با موفقیت ثبت شد']);
    }

}
