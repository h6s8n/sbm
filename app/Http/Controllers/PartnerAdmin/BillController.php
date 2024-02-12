<?php

namespace App\Http\Controllers\PartnerAdmin;

use App\Model\Partners\Partner;
use App\Model\Visit\DoctorCalender;
use App\Model\Visit\EventReserves;
use App\Model\Visit\TransactionCredit;
use App\Model\Visit\TransactionDoctor;
use App\Model\Visit\TransactionReserve;
use App\SendSMS;
use App\User;
use Carbon\Carbon;
use Hekmatinasser\Verta\Verta;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use niklasravnsborg\LaravelPdf\Facades\Pdf as PDF;
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


    public function list()
    {

        $where_array = array();

        $status_list = $this->request->status_list;
        $user = auth()->user();
        $partner = Partner::where('support_id', $user->id)->orderBy('id','ASC')->first();

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

        $filter_partner = $partner->id;
        if ($filter_partner) {
            $where_array[] = array('doctor_calenders.partner_id', $filter_partner);
        }

        $filter_status = $this->request->get('filter_status');
        if ($filter_status) {
            $where_array[] = array('transaction_doctors.status', $filter_status);
            $where_array[] = array('transaction_doctors.amount', '>', 0);
        }

        // dd($where_array);

        $request = EventReserves::join('users as dr', 'dr.id', '=', 'event_reserves.doctor_id')
            ->join('users as us', 'us.id', '=', 'event_reserves.user_id')
            ->leftJoin('doctor_calenders', 'doctor_calenders.id', '=', 'event_reserves.calender_id')
            ->leftJoin('transaction_doctors', 'transaction_doctors.event_id', '=', 'event_reserves.id')
            ->where($where_array)
            ->where(function ($query) use ($array_dr, $array_bi) {
                if ($array_dr) {
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
                'dr.doctor_nickname',
                'dr.account_sheba as dr_sheba',
                'doctor_calenders.partner_id',
                'transaction_doctors.status as pay_dr_status',
                'transaction_doctors.amount as pay_amount'
            )
            ->orderBy('event_reserves.reserve_time', 'DESC')
            //->groupBy('event_reserves.id')
            //->distinct('event_reserves.id')
            ->paginate(35);

        $full_price = 0;
        if ($request) {
            foreach ($request as $item) {
                $full_price += (int)$item['price'];
            }
        }


        return view('admin/bill/doctor/list', ['request' => $request,
            'full_price' => $full_price, 'status_list' => $status_list]);

    }

}
