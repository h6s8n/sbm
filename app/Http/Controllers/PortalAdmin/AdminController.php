<?php

namespace App\Http\Controllers\PortalAdmin;

use App\Model\Partners\Partner;
use App\Model\Platform\City;
use App\Model\Wallet\DoctorWallet;
use App\Model\Wallet\DoctorWalletTransaction;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{

    protected $request;

    public function __construct(Request $request)
    {
        date_default_timezone_set("Asia/Tehran");
        $this->request = $request;

        require(base_path('app/jdf.php'));
    }


    public function home(){

        if(!auth()->user()){
            return redirect("/cp-portal/login");
        }

        return redirect("/cp-portal/dashboard");

    }

    public function login(){

        if(auth()->user()){
            return redirect("/cp-portal/dashboard");
        }

        return view('admin/login');
    }

    public function ActionLogin(){

        if(auth()->user()){
            return redirect("/cp-portal/dashboard");
        }

        $ValidData = $this->validate($this->request,[
            'email' => 'required|string|email|max:255',
            'password' => 'required',
        ]);


        if(!auth()->attempt(['email' => $ValidData['email'], 'password' => $ValidData['password'] , 'status' => 'active' , 'approve' => '11'])) {
            return redirect("/cp-portal/login")->with('error' , 'نام کاربری و یا رمز عبور اشتباه است.');
        }

        return redirect("/cp-portal/dashboard");

    }

    public function logout(){

        auth()->logout();

        return redirect("/cp-portal/login");

    }

    public function dashboard()
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
            $filter_status = $this->request->get('filter_status' , 'pending_decrease');
            if ($filter_status) {
                $where_array[] = array('doctor_wallets.status', $filter_status);
            }

            $filter_transId = $this->request->get('filter_transId');
            if ($filter_transId) {
                $where_array[] = array('doctor_wallets.transId', $filter_transId);
            }


            $filter_start_date = $this->request->get('filter_start_date');
            $filter_end_date = $this->request->get('filter_end_date');
            if ($filter_start_date) {

                $date = explode('/', $filter_start_date);
                $date = jalali_to_gregorian($date[0], $date[1], $date[2], '-');

                $where_array[] = array(DB::raw('DATE(doctor_wallets.created_at)'), '>=', $date);
            }

            if ($filter_end_date) {

                $date = explode('/', $filter_end_date);
                $date = jalali_to_gregorian($date[0], $date[1], $date[2], '-');

                $where_array[] = array(DB::raw('DATE(doctor_wallets.created_at)'), '<=', $date);
            }


            $request = DoctorWallet::with(['user:id,fullName,mobile', 'doctor:id,fullName,mobile'])
                ->orderBy('created_at', 'DESC')
                ->where('settlement_type','other')
                ->where('transId','!=',NUll)
                ->where($where_array)
                ->where(function ($query) use ($filter_name, $array_dr) {
                    if ($filter_name || $array_dr) {
                        $query->whereIn('doctor_wallets.doctor_id', $array_dr);
                    }
                })
                ->paginate(10);

        return view('portalPanel/index' , ['request' => $request]);

    }

    public function transactions()
    {
        $where_array = array();

        $filter_transId = $this->request->get('filter_transId');
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

        if ($filter_transId) {
            $where_array[] = array('doctor_wallet_transactions.transId', $filter_transId);
        }

        $request = DoctorWalletTransaction::where('user_id',auth()->id())
            ->where($where_array)
            ->orderByDesc('created_at')
        ->paginate(10);

        return view('portalPanel/transactions' , ['request' => $request] );
    }
    public function showWallet($id)
    {
        $wallets = DoctorWallet::where('transId',$id)->paginate(100);

        if($wallets->total()>0) {
            return view('portalPanel.walletPaymentConfirm', ['wallets' => $wallets]);
        }else{
            return back()->with('error', 'شناسه پرداخت نامعتبر است')->withInput();
        }
    }

    public function updateWallet($id)
    {
        $ValidData = $this->validate($this->request,[
            'receipt_link' => 'required',
            'tether_current_price' => 'required',
        ]);
        $wallet = DoctorWallet::findOrFail($id);
        $wallet->receipt_link = $this->request->receipt_link;
        $wallet->tether_current_price = $this->request->tether_current_price;
        $wallet->tether_count = (abs($wallet->amount)/$this->request->tether_current_price) - 2;
        $wallet->status = 'paid_decrease';
        $wallet->paid_at = Carbon::now()->format('Y-m-d h:i:s');
        $wallet->save();

        return back()->with('success', 'ثبت شد.')->withInput();

    }
}
