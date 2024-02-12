<?php

namespace App\Http\Controllers\PartnerAdmin;

use App\Events\SMS\SetTimeNotificationEvent;
use App\Http\Controllers\Api\v2\vandar\VandarController;
use App\Model\Partners\Partner;
use App\Model\Visit\DoctorCalender;
use App\Model\Visit\EventReserves;
use App\Model\Visit\TransactionDoctor;
use App\User;
use App\Services\Gateways\src\Zibal;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class CalenderController extends Controller
{
    protected $request;

    public function __construct(Request $request)
    {
        date_default_timezone_set("Asia/Tehran");
        $this->request = $request;

        require(base_path('app/jdf.php'));
    }

    public function info()
    {


        $where_array = array();

        //filter set to query
        $filter_name = trim($this->request->get('filter_name'));
        $filter_date = trim($this->request->get('filter_date'));
        $filter_dr = trim($this->request->get('doctor'));

        $user = auth()->user();
        $partner = Partner::where('support_id', $user->id)->orderBy('id','ASC')->first();

        $filter_partner = $partner->id;
        if ($filter_name) {
            $where_array[] = array('users.fullname', "LIKE", "%" . $filter_name . "%");
        }
        if ($filter_date) {
            $where_array[] = array('doctor_calenders.fa_data', $filter_date);
        }
        if ($filter_dr) {
            $where_array[] = array('doctor_calenders.user_id', $filter_dr);
        }
        if ($filter_partner) {
            $where_array[] = array('doctor_calenders.partner_id', $filter_partner);
        }


        $user = auth()->user();
        $request = DoctorCalender::join('users', 'users.id', '=', 'doctor_calenders.user_id')->where($where_array)->where('doctor_calenders.fa_data', '>=', jdate('Y-m-d'))->orderBy('doctor_calenders.data', 'ASC')
            ->select('doctor_calenders.*', 'users.fullname')->paginate(35);

        return view('partnerPanel/calenders/index', ['request' => $request]);

    }

    public function delete()
    {
        $user = auth()->user();
        $partner = Partner::where('support_id', $user->id)->orderBy('id','ASC')->first();

        $request = DoctorCalender::where('id', $this->request->id)->where('partner_id', $partner->id)->first();

        if ($request) {
            if ($request->reservation > 0) {
                $request->capacity = $request->reservation;
                $request->save();
            } else
                $request->delete();

        }

        return back()->with('success', 'حذف با موفقیت انجام شد.')->withInput();

    }

    public function add()
    {

        $where_array = array();
        $user = auth()->user();
        $partner = Partner::where('support_id', $user->id)->orderBy('id','ASC')->first();

        $filter_partner = $partner->id;
        $filter_id = trim($this->request->get('user_id'));
        if ($filter_id) {
            $where_array[] = array('id', $filter_id);
        }

        $partner = [];
        if ($filter_partner) {
            $partner = Partner::where('id', $filter_partner)->first();
        }

        if ($filter_id) {


            $doctors = User::where('approve', '1')
                ->where($where_array)
                ->where('doctor_status', 'active')
                ->whereIn('status',['imported','active'])
                ->orderBy('fullname', 'ASC')->get();

        }else{

            $partner2 = Partner::where('support_id', $user->id)
                ->with(['doctors' => function ($query) {
                    $query->select('users.id','name','doctor_nickname', 'family', 'fullname', 'email', 'mobile', 'users.created_at','job_title');
                }])->orderBy('id','ASC')->first();

            $doctors = $partner2->doctors;

        }


        return view('partnerPanel/calenders/add', ['doctors' => $doctors, 'partner' => $partner]);

    }

    public function Create()
    {
        $ValidData = $this->validate($this->request, [
            'doctor' => 'required|numeric',
            'price' => 'nullable|numeric',
            'day' => 'required|numeric',
            'month' => 'required|numeric',
            'year' => 'required|numeric',
            'sum_date' => 'required|numeric',
            'date_time' => 'required',
            'time' => 'required',
            'capacity' => 'required|numeric|max:20',
            'partner' => 'nullable',
        ]);

        $user = auth()->user();
        $partner = Partner::where('support_id', $user->id)->orderBy('id','ASC')->first();

        if (\request()->has('price') && \request()->input('price') > 0) {
            if (\request()->input('price') < 80000)
                return error_template("حداقل میلغ ویزیت ۸۰۰۰۰ ریال می باشد");
        }

        if (!$ValidData['price']) $this->request['price'] = 0;

        $date = $ValidData['year'] . '/' . $ValidData['month'] . '/' . $ValidData['day'];
        $dateTime = jalali_to_gregorian($ValidData['year'], $ValidData['month'], $ValidData['day'], '/');

        $user = $ValidData['doctor'];

        $dateTimeFull = $ValidData['date_time'];
        $timeFull = $ValidData['time'];

        $dateTimeFullNew = [];
        if ($dateTimeFull) {
            foreach ($dateTimeFull as $item) {
                $dateTimeFullNew[] = $item;
            }

            $dateTimeFull = $dateTimeFullNew;
        }


        $partner_price = ($this->request->get('partner_price')) ? $this->request->get('partner_price') : 0;

        $off_price = 0;
        $original_price = $this->request->get('price');
        $price = $original_price;
        if ($off_price && ($off_price < $original_price)) {
            $price = $off_price;
        }

        $dollar_priceـoff = 0;
        $original_dollar_price = 0;
        $dollar_price = $original_dollar_price;
        if ($dollar_priceـoff && ($dollar_priceـoff < $original_dollar_price)) {
            $dollar_price = $dollar_priceـoff;
        }



        if ($timeFull) {

            foreach ($timeFull as $time) {

                $i = 0;
                for ($i; $i < $ValidData['sum_date']; $i++) {
                    $dateTimeNew = Carbon::parse($dateTime)->addDays($i);
                    $en_date = date('Y-m-d', strtotime($dateTimeNew));
                    $fa_date = jdate('Y-m-d', strtotime($dateTimeNew));
                    $well_date = jdate('l', strtotime($dateTimeNew));

                    if (in_array($well_date, $dateTimeFull)) {
                        $request = DoctorCalender::where('user_id', $user)
                            ->where('fa_data', $fa_date)
                            ->where('time', $time)
                            ->first();
                        if (!$request) {

                            $newTime = new DoctorCalender();
                            $newTime->user_id = $user;
                            $newTime->fa_data = $fa_date;
                            $newTime->data = $dateTimeNew;
                            $newTime->time = $time;
                            $newTime->capacity = $ValidData['capacity'];
                            $newTime->reservation = 0;
                            $newTime->off_price = ($off_price) ? $off_price : 0;
                            $newTime->original_price = $original_price;
                            $newTime->price = $price;
                            $newTime->type = \request()->input('type');

                            $newTime->off_dollar_price = ($dollar_priceـoff) ? $dollar_priceـoff : 0;
                            $newTime->original_dollar_price = $original_dollar_price;
                            $newTime->dollar_price = $dollar_price;
                            $newTime->partner_price = $partner_price;
                            $newTime->partner_id = $partner->id;

//                            var_dump($newTime);
//
                            $newTime->save();

                        }

                    }

                }


            }
        } else {
            return back()->with('error', 'ساعت را وارد کنید.')->withInput();
        }

        if($ValidData['partner']){
            return redirect('cp-partner/calenders?partner='.$ValidData['partner'].'&doctor='.$user)->with('success', 'ثیت با موفقیت انجام شد.')->withInput();
        }
        return redirect('cp-partner/calenders')->with('success', 'ثیت با موفقیت انجام شد.')->withInput();



    }

    public function DeleteAll(User $user,$partner=null)
    {
        if ($user->approve==1) {

            $userme = auth()->user();
            $partner = Partner::where('support_id', $userme->id)->orderBy('id','ASC')->first();

            $calendars = $user->calenders()
                ->where('reservation', 0)
                ->where('capacity','>',0)
                ->whereDoesntHave('visits')
                ->where('partner_id',$partner->id);
            $counts = $calendars->count();

            $calendars->delete();

            return redirect()->back()->with(['success'=>'تعداد '.$counts.' با موفقیت حذف شد']);
        }
        return  redirect()->back()->withErrors(['error'=>'کاربر وارد شده پزشک نیست']);
    }

    public function test(){
        return 'dsdsd';
    }


    public function bill()
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
                'transaction_doctors.amount as pay_amount',
                'transaction_doctors.updated_at as paid_at'
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


        return view('partnerPanel/bill/doctor/list', ['request' => $request,
            'full_price' => $full_price, 'status_list' => $status_list]);

    }

    public function transactions()
    {

        $request = [];

        $filter_dr_fullname = $this->request->get('filter_doctor' , null);
        $filter_patient_fullname = $this->request->get('filter_patient' , null);

        $user = auth()->user();
        $partner = Partner::where('support_id', $user->id)->orderBy('id','ASC')->first();

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

//            try {

            $request['prev_page_url'] =  null;
            $request['next_page_url'] =  null;
            $request['total'] = $resp->total;

            foreach ($resp->data as $data) {
                foreach ($data->details as $detail){

                    $result = NULL;
                    $transaction = TransactionDoctor::join('users as doctor', 'doctor.id', '=', 'transaction_doctors.doctor_id')
                        ->join('users as patient', 'patient.id', '=', 'transaction_doctors.user_id')
                        ->where('transaction_id', $detail->checkoutRequestId)
                        ->where(function ($q) use ($filter_dr_fullname, $filter_patient_fullname) {
                            if ($filter_dr_fullname) {
                                $q->where('doctor.fullname', 'LIKE', '%' . $filter_dr_fullname . '%');
                            }
                            if ($filter_patient_fullname) {
                                $q->where('patient.fullname', 'LIKE', '%' . $filter_patient_fullname . '%');
                            }
                        })
                        ->select('patient.fullname as patient_fullname', 'doctor.fullname as doctor_fullname')->first();

                    $result = [
                        'id' => $detail->checkoutRequestId,
                        'dr_fullname' => $transaction['doctor_fullname'],
                        'patient_fullname' => $transaction['patient_fullname'],
                        'status' => $detail->type === 2 ? 'موفق' : 'بازگشت به حساب',
                        'created_at' => jdate('Y/m/d', strtotime($detail->createdAt)),
                        'estimated_deposit_time' => $data->persianSettlementDate,
                        'amount' => $detail->amount,
                        'receipt_url' => $transaction['receipt']
                    ];
                    if ($result['dr_fullname']) {
                        $request['data'][] = $result;
                    }
                }
            }

//            } catch (\Exception $e) {
//                $request = [];
//            }
        }

        return view('partnerPanel/bill/doctor/transactions', ['request' => $request]);

    }

    public function export()
    {
        $where_array = array();

        $user = auth()->user();
        $partner = Partner::where('support_id', $user->id)->orderBy('id','ASC')->first();

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
            ->where('doctor_calenders.partner_id', $partner->id)
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

        $path_blank = str_replace(get_ev('path_live'), get_ev('path_root'), base_path('files'));
        $fileName = time() . '_save.xlsx';


        $objWriter->save("{$path_blank}/{$fileName}");
        return redirect(get_ev('statics_server') . '/files/' . $fileName);
    }


}
