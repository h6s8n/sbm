<?php

namespace App\Http\Controllers\Admin\Doctor;

use App\Events\SMS\SetTimeNotificationEvent;
use App\Model\Partners\Partner;
use App\Model\Visit\DoctorCalender;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

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
        $filter_partner = trim($this->request->get('partner'));
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

        return view('admin/calenders/index', ['request' => $request]);

    }

    public function delete()
    {

        $request = DoctorCalender::where('id', $this->request->id)->first();

        if ($request) {
            if ($request->reservation > 0) {
                $request->capacity = $request->reservation;
                $request->save();
            } else
                $request->delete();

        }

        return redirect('cp-manager/calenders')->with('success', 'حذف با موفقیت انجام شد.')->withInput();

    }

    public function add()
    {

        $where_array = array();
        $filter_partner = trim($this->request->get('partner'));
        $filter_id = trim($this->request->get('user_id'));
        if ($filter_id) {
            $where_array[] = array('id', $filter_id);
        }

        $partner = [];
        if ($filter_partner) {
            $partner = Partner::where('id', $filter_partner)->first();
        }
        $doctors = User::where('approve', '1')
            ->where($where_array)
            ->where('doctor_status', 'active')
            ->whereIn('status',['imported','active'])
            ->orderBy('fullname', 'ASC')->get();


        return view('admin/calenders/add', ['doctors' => $doctors, 'partner' => $partner]);

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

        if (\request()->has('price') && \request()->input('price') > 0) {
            if (\request()->input('price') < 198000)
                return error_template("حداقل میلغ ویزیت 198000 ریال می باشد");
        }
        if (\request()->has('type') && \request()->input('type') == 4){
            if (\request()->input('price') > 100000)
                return back()->with('error', 'حداکثر مبلغ تفسیرآزمایش ۱۰۰,۰۰۰ ریال می باشد')->withInput();
        }
        if (!change_number($ValidData['price'])) $this->request['price'] = 0;

        $date = change_number($ValidData['year']) . '/' . $ValidData['month'] . '/' . $ValidData['day'];
        $dateTime = jalali_to_gregorian(change_number($ValidData['year']), $ValidData['month'], $ValidData['day'], '/');

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
        $original_price = change_number($this->request->get('price'));
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
                            $newTime->has_prescription = $this->request->input('has_prescription');
                            $newTime->off_dollar_price = ($dollar_priceـoff) ? $dollar_priceـoff : 0;
                            $newTime->original_dollar_price = $original_dollar_price;
                            $newTime->dollar_price = $dollar_price;
                            $newTime->partner_price = $partner_price;
                            $newTime->partner_id = $ValidData['partner'];
//                            var_dump($newTime);
                            $newTime->save();

                            if ($i == 0)
                                SetTimeNotificationEvent::dispatch($newTime);

                        }

                    }

                }


            }
        } else {
            return back()->with('error', 'ساعت را وارد کنید.')->withInput();
        }

        if($ValidData['partner']){
            return redirect('cp-manager/calenders?partner='.$ValidData['partner'].'&user_id='.$user)->with('success', 'ثیت با موفقیت انجام شد.')->withInput();
        }
        return redirect('cp-manager/calenders')->with('success', 'ثیت با موفقیت انجام شد.')->withInput();



    }

    public function DeleteAll(User $user,$partner=null)
    {
        if ($user->approve==1) {

            $calendars = $user->calenders()
                ->where('reservation', 0)
                ->where('capacity','>',0)
                ->whereDate('data','>=',Carbon::now()->format('Y-m-d'))
                ->whereDoesntHave('visits');
            if ($partner)
                $calendars=$calendars->where('partner_id',$partner);
            else
                $calendars=$calendars->where('partner_id',0);
            $counts = $calendars->count();

            $calendars->delete();

            return redirect()->back()->with(['success'=>'تعداد '.$counts.' با موفقیت حذف شد']);
        }
        return  redirect()->back()->withErrors(['error'=>'کاربر وارد شده پزشک نیست']);
    }

}
