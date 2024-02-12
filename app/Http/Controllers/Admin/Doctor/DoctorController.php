<?php

namespace App\Http\Controllers\Admin\Doctor;

use App\Enums\LanguageEnum;
use App\Model\Doctor\DoctorInformation;
use App\Model\Doctor\Skill;
use App\Model\Doctor\DoctorContract;
use App\Model\Doctor\Specialization;
use App\Model\Notification\UserDoctorNotification;
use App\Model\Partners\Partner;
use App\Model\Partners\PartnerDoctor;
use App\Model\Platform\State;
use App\Model\User\Specialties;
use App\Model\Visit\DoctorCalender;
use App\Model\Visit\EventReserves;
use App\Model\Visit\TransactionCredit;
use App\Repositories\v1\Doctor\Specialization\SpecializationInterface;
use App\User;
use Carbon\Carbon;
use Hekmatinasser\Verta\Facades\Verta;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class DoctorController extends Controller
{
    protected $request;
    protected $specialization;

    public function __construct(Request $request, SpecializationInterface $specialization)
    {
        date_default_timezone_set("Asia/Tehran");
        $this->request = $request;

        require(base_path('app/jdf.php'));
        $this->specialization = $specialization;

    }


    public function users()
    {

        $where_array = array();

        //filter set to query
        $filter_name = trim($this->request->get('filter_name'));
        $filter_mobile = trim($this->request->get('filter_mobile'));
        $filter_special_code = trim($this->request->get('filter_special_code'));
        $filter_email = trim($this->request->get('filter_email'));
        $filter_status = trim($this->request->get('filter_status'));
        $filter_visit_counter = $this->request->get('filter_visit_counter');
        $filter_doctor_status = trim($this->request->get('filter_doctor_status'));

        if ($filter_mobile) {
            $where_array[] = array('mobile', "LIKE", "%" . $filter_mobile . "%");
        }
        if ($filter_email) {
            $where_array[] = array('email', "LIKE", "%" . $filter_email . "%");
        }
        if ($filter_status) {
            $where_array[] = array('status', $filter_status);
        }
        if ($filter_special_code) {
            $where_array[] = array('specialcode', $filter_special_code);
        }
        if ($filter_doctor_status) {
            $where_array[] = array('doctor_status', $filter_doctor_status);
        }


        $users = User::withCount(['calenders' => function($query){
            $query->where('fa_data', '>=', jdate('Y-m-d'))->orderBy('created_at', 'desc');
        }])->where($where_array)->where('approve', '1')
            ->where(function ($query) use ($filter_name) {
                $query->search($filter_name, false);
            });

        if ($this->request->has('calendar_type') && $this->request->input('calendar_type')){
            $users = $users->whereHas('calenders',function ($query){
                $query->where('type',$this->request->input('calendar_type'))
                    ->whereDate('data','>=',Carbon::now()->format('Y-m-d'));
            });
        }

        if ($this->request->has('from') &&
            $this->request->input('from') &&
            $this->request->has('to') &&
            $this->request->input('to')){
            $from = change_number($this->request->input('from'));
            /* @var \Hekmatinasser\Verta\Verta $from_date */
            $from = explode('/',$from);
            $from_date = Verta::create();
            $from_date->year($from[0]);
            $from_date->month($from[1]);
            $from_date->day($from[2]);
            $from_date = $from_date->formatGregorian('Y-m-d');

            $to = change_number($this->request->input('to'));
            /* @var \Hekmatinasser\Verta\Verta $to_date */
            $to = explode('/',$to);
            $to_date = Verta::create();
            $to_date->year($to[0]);
            $to_date->month($to[1]);
            $to_date->day($to[2]);
            $to_date = $to_date->formatGregorian('Y-m-d');

            $users = $users->whereDate('created_at','>=',$from_date)
                ->whereDate('created_at','<=',$to_date);

        }

        if ($this->request->has('specialization_id') && $this->request->input('specialization_id')){
            $users = $users->whereHas('specializations',function ($query){
                $query->where('id',$this->request->input('specialization_id'));
            });
        }
        $users = $users->orderBy(
            $filter_visit_counter ? 'calenders_count' : 'created_at',
            $filter_visit_counter ?? 'desc')
            ->paginate(35);

        $visit_seen_counter = [];
        $visit_counter = [];
        if ($users) {
            foreach ($users as $item) {

                $visit_seen = EventReserves::where('doctor_id', $item['id'])->where('visit_status', 'end')->orderBy('created_at', 'desc')->count();
                $visit_seen_counter[$item['id']] = $visit_seen;

                $visit = DoctorCalender::where('user_id', $item['id'])->where('fa_data', '>=', jdate('Y-m-d'))->orderBy('created_at', 'desc')->count();

                $visit_counter[$item['id']] = $visit;
            }
        }
        $specializations = $this->specialization->all();

        //view data
        return view('admin/doctors/index',
            ['request' => $users,
                'visit_seen_counter' => $visit_seen_counter,
                'visit_counter' => $visit_counter,
                'specializations'=>$specializations]);

    }

    public function userAdd()
    {
        $specialties = specialties_array();
        $specializations = $this->specialization->all();
        $province = State::orderBy('state', 'asc')->get();
        return view('admin/doctors/add', ['province' => $province,
            'specialties' => $specialties, 'specializations' => $specializations]);

    }

    public function ActionUserAdd()
    {

        // Validation Data
        $ValidData = $this->validate($this->request, [
            'name' => 'required|string|max:255',
            'family' => 'required|string|max:255',
            'status' => 'required|string',
            'gender' => 'required|numeric',
//            'nationalcode' => 'required|numeric|unique:users',
            'email' => 'required_without:mobile|email|max:255|unique:users',
            'username' => 'required|string|max:255|unique:users',
            'mobile' => 'required_without:email|numeric|unique:users|regex:/(0)[0-9]{10}/',
            'password' => 'nullable|string|min:6',
            'specialcode' => 'required',
            'account_sheba' => 'nullable|digits:24',
            'doctor_status' => 'required|string',
            'job_title' => 'required|string',
            'sp_gp' => 'required',
            'state' => 'required',
            'city' => 'required',
            'code_title'=>'required'
        ]);

      //  dd($this->request->all());
        $picture = null;
        $passport_image = null;
        $national_cart_image = null;
        $special_cart_image = null;
        $education_image = null;

        if ($this->request->file('picture')) {

            $ValidDataImage = $this->validate($this->request, [
                'picture' => 'required|mimes:jpg,jpeg,bmp,png',
            ]);

            $picture = $this->uploadImageCt('picture', 'images');
        }

        if ($this->request->file('passport_image')) {

            $ValidDataImage = $this->validate($this->request, [
                'passport_image' => 'required|mimes:jpg,jpeg,bmp,png',
            ]);

            $passport_image = $this->uploadImageCt('passport_image', 'images');
        }
        if ($this->request->file('national_cart_image')) {

            $ValidDataImage = $this->validate($this->request, [
                'national_cart_image' => 'required|mimes:jpg,jpeg,bmp,png',
            ]);

            $national_cart_image = $this->uploadImageCt('national_cart_image', 'images');
        }
        if ($this->request->file('special_cart_image')) {

            $ValidDataImage = $this->validate($this->request, [
                'special_cart_image' => 'required|mimes:jpg,jpeg,bmp,png',
            ]);

            $special_cart_image = $this->uploadImageCt('special_cart_image', 'images');
        }
        if ($this->request->file('education_image')) {

            $ValidDataImage = $this->validate($this->request, [
                'education_image' => 'required|mimes:jpg,jpeg,bmp,png',
            ]);

            $education_image = $this->uploadImageCt('education_image', 'images');
        }

        $username = $this->slugify($this->request->get('username'));

        $token = str_random(6);
        $request = User::create([
            'name' => $ValidData['name'],
            'family' => $ValidData['family'],
            'fullname' => trim($ValidData['name'] . ' ' . $ValidData['family']),
            'email' => $ValidData['email'],
            'mobile' => $ValidData['mobile'],
            'nationalcode' => $ValidData['nationalcode'] ?? null,
            'status' => $ValidData['status'],
            'doctor_status' => $ValidData['doctor_status'],
            'specialcode' => $ValidData['specialcode'],
            'gender' => $ValidData['gender'],
            'username' => $username,
            'en_url' => $this->request->input('en_url'),
            'token' => $token,
            'approve' => 1,
            'job_title' => $this->request->get('job_title'),
            'doctor_nickname' => ($this->request->get('doctor_nickname')) ? $this->request->get('doctor_nickname') : '',
            'bio' => $this->request->get('bio'),
            'state_id' => ($this->request->get('state')) ? $this->request->get('state') : 0,
            'city_id' => ($this->request->get('city')) ? $this->request->get('city') : 0,
            'address' => $this->request->get('address'),
            'account_number' => $this->request->get('account_number'),
            'account_sheba' => trim(str_replace('IR', '', str_replace('ir', '', $this->request->get('account_sheba')))),
            'special_cart_image' => $special_cart_image,
            'education_image' => $education_image,
            'national_cart_image' => $national_cart_image,
            'passport_image' => $passport_image,
            'picture' => $picture,
            'code_title'=>$this->request->get('code_title'),
            'visit_condition'=>'{"my_patient_only":"false","consultation_type":{"videoConsultation":"true","voiceConsultation":"true","textConsultation":"true"}}',
            'password' => $ValidData['password'] ? bcrypt($ValidData['password']) : bcrypt(str_random(6))
        ]);


        $data = [
            'user_id' => $request->id,
            'specializations_id' => $ValidData['sp_gp']
        ];
        $this->assignSpecialization($data);

        $gstr = '';
        foreach ($request->specializations()->get() as $gp) {
            $gstr = $gstr . $gp->name . " , ";
        }
        $request->sp_gp = $gstr;
        $request->save();


        return redirect('cp-manager/doctors')
            ->with('success', 'این کاربر به لیست کاربران شما اضافه شد.')->withInput();

    }

    public function userEdit()
    {

        //get user id
        $id = $this->request->user;

        //check user validate
        $request = User::where('id', $id)->orderBy('id', 'desc')->first();
        if (!$request) return redirect('cp-manager/doctors')->with('error', 'اطلاعات ارسال شده اشتباه است.');

        $province = State::orderBy('state', 'asc')->get();

        $specializations = Specialization::all();

        $skills = Skill::orderBy('name', 'asc')->select('id as value' , 'name as label')->get();

        $specialties = Specialties::orderBy('name', 'asc')->select('id as value' , 'name as label')->get();

        $partners = Partner::all();
        return view('admin/doctors/edit', ['request' => $request, 'skills' => $skills, 'province' => $province, 'specializations' => $specializations , 'specialties' => $specialties, 'partners' => $partners]);

    }

    public function ActionUserEdit()
    {//get user id

        $id = $this->request->user;

            //check user validate
        $request = User::where('id', $id)->orderBy('id', 'desc')->first();
        if (!$request) return redirect('cp-manager/doctors')->with('error', 'اطلاعات ارسال شده اشتباه است.');

        // Validation Data
        $ValidData = $this->validate($this->request, [
            'name' => 'required|string|max:255',
            'family' => 'required|string|max:255',
            'nationalcode' => 'nullable',
            'status' => 'required|string',
            'doctor_status' => 'required|string',
            'account_sheba' => 'nullable|digits:24',
            'gender' => 'required|numeric',
            'specialcode' => 'required',
            'sp_gp' => 'required_if:doctor_status,active',
            'username' => 'required_if:doctor_status,active',
            'en_url' => 'required_if:doctor_status,active',
            'job_title' => 'required_if:doctor_status,active',
        ]);

        $username = $this->slugify($this->request->get('username'));

        if (strtolower($username) != strtolower($request->username)) {

            $this->request['username'] = strtolower($username);

            $ValidData_username = $this->validate($this->request, [
                'username' => 'required|string|unique:users',
            ]);
            $request->username = $username;

        }
        if ($this->request->has('nationalcode')) {
            if ($this->request->get('nationalcode') != $request->nationalcode) {
                $ValidData_email = $this->validate($this->request, [
                    'nationalcode' => 'required|numeric|unique:users',
                ]);
                $request->nationalcode = $ValidData_email['nationalcode'];
            }
        }

        if ($this->request->get('email') && ($this->request->get('email') != $request->email)) {
            $ValidData_email = $this->validate($this->request, [
                'email' => 'required_without:mobile|email|max:255|unique:users',
            ]);
            $request->email = $ValidData_email['email'];
        }

        if ($this->request->get('mobile') != $request->mobile) {
            $ValidData_mobile = $this->validate($this->request, [
                'mobile' => 'required_without:email|numeric|unique:users|regex:/(0)[0-9]{10}/',
            ]);
            $request->mobile = $ValidData_mobile['mobile'];
        }

        if ($this->request->get('password') && $this->request->get('password') !== $request->password) {
            $ValidData_password = $this->validate($this->request, [
                'password' => 'string|min:6',
            ]);
            $request->password = bcrypt($ValidData_password['password']);
        }


        if ($this->request->get('delete-picture')){
            $request->picture = NULL;
        }

        if ($this->request->file('picture')) {

            $ValidDataImage = $this->validate($this->request, [
                'picture' => 'required|mimes:jpg,jpeg,bmp,png',
            ]);

            $picture = $this->uploadImageCt('picture', 'images');
            $request->picture = $picture;
        }

        if ($this->request->file('passport_image')) {

            $ValidDataImage = $this->validate($this->request, [
                'passport_image' => 'required|mimes:jpg,jpeg,bmp,png',
            ]);

            $passport_image = $this->uploadImageCt('passport_image', 'images');
            $request->passport_image = $passport_image;
        }
        if ($this->request->file('national_cart_image')) {

            $ValidDataImage = $this->validate($this->request, [
                'national_cart_image' => 'required|mimes:jpg,jpeg,bmp,png',
            ]);

            $national_cart_image = $this->uploadImageCt('national_cart_image', 'images');
            $request->national_cart_image = $national_cart_image;
        }
        if ($this->request->file('special_cart_image')) {

            $ValidDataImage = $this->validate($this->request, [
                'special_cart_image' => 'required|mimes:jpg,jpeg,bmp,png',
            ]);

            $special_cart_image = $this->uploadImageCt('special_cart_image', 'images');
            $request->special_cart_image = $special_cart_image;
        }
        if ($this->request->file('education_image')) {

            $ValidDataImage = $this->validate($this->request, [
                'education_image' => 'required|mimes:jpg,jpeg,bmp,png',
            ]);

            $education_image = $this->uploadImageCt('education_image', 'images');
            $request->education_image = $education_image;
        }


        if($this->request->has('office_secretary_mobile') || $this->request->has('office_secretary_name')){
            $ValidData = $this->validate($this->request,[
                'office_secretary_mobile' => 'required_with:office_secretary_name',
                'office_secretary_name' => 'required_with:office_secretary_mobile',
            ]);

            $data['office_secretary_mobile'] = $ValidData['office_secretary_mobile'];
            $data['doctor_id'] = $request->id;
            $data['office_secretary_name'] = $ValidData['office_secretary_name'];
            $this->storeSecretary($data);

        }

        if ($this->request->get('credit') > $request->credit) {

            $new_credit = $this->request->get('credit') - $request->credit;


            $sku = str_random(20);
            $newTransaction = new TransactionCredit();
            $newTransaction->user_id = $id;
            $newTransaction->amount = $new_credit;
            $newTransaction->token = $sku;
            $newTransaction->status = 'paid';
            $newTransaction->message = 'افزایش اعتبار توسط ادمین ('. auth()->user()->fullname .')';
            $newTransaction->save();


        } else if ($this->request->get('credit') < $request->credit) {

            $new_credit = $this->request->get('credit') - $request->credit;


            $sku = str_random(20);
            $newTransaction = new TransactionCredit();
            $newTransaction->user_id = $id;
            $newTransaction->amount = $new_credit;
            $newTransaction->token = $sku;
            $newTransaction->status = 'paid';
            $newTransaction->message = '** کاهش اعتبار توسط ادمین ('. auth()->user()->fullname .')';
            $newTransaction->save();

        }
        $skill_json = '';
        if (\request()->input('skill_json')){
            $skill_json .= '[';
            $skill_json .= implode(',',$this->request->skill_json);
            $skill_json .= ']';
        }
        $request->skill_json = $skill_json;

        $special_json = '';
        if (\request()->input('special_json')){
            $special_json .= '[';
            $special_json .= implode(',',$this->request->special_json);
            $special_json .= ']';
        }
        $request->special_json = $special_json;

        $request->name = $this->request->get('name');
        $request->family = $this->request->get('family');
        $request->specialcode = $this->request->get('specialcode');
//        $request->sp_gp = ($ValidData['sp_gp']) ? $ValidData['sp_gp'] : '';
        $request->fullname = trim($this->request->get('name') . ' ' . $this->request->get('family'));
        $request->status = $this->request->get('status');
        $request->en_url = $this->request->input('en_url');
        $request->special_point = $this->request->input('special_point');
        $request->in_person_special_point = $this->request->input('in_person_special_point');
        $request->doctor_status = $this->request->input('doctor_status');
        $request->doctor_info_status = $this->request->input('doctor_status') == 'active' ? 1 : 0;
        $request->doctor_nickname = ($this->request->get('doctor_nickname')) ? $this->request->get('doctor_nickname') : '';
        $request->gender = $this->request->input('gender');
        $request->job_title = $this->request->get('job_title');
        $request->bio = $this->request->get('bio');
        $request->state_id = ($this->request->get('state')) ? $this->request->get('state') : 0;
        $request->city_id = ($this->request->get('city')) ? $this->request->get('city') : 0;
        $request->address = $this->request->get('address');
        $request->phone = $this->request->get('phone');
        $request->account_number = $this->request->get('account_number');
        $request->code_title = $this->request->get('code_title');
        $request->account_sheba = trim(str_replace('IR', '', str_replace('ir', '', $this->request->get('account_sheba'))));
        $request->save();
        if (\request()->input('sp_gp')) {
            $data = [
                'user_id' => $id,
                'specializations_id' => $this->request->get('sp_gp')
            ];
            $this->assignSpecialization($data);
            $gstr = '';
            foreach ($request->specializations()->get() as $gp) {
                $gstr = $gstr . $gp->name . " , ";
            }
            $request->sp_gp = $gstr;
            $request->save();
        }

        PartnerDoctor::where('user_id', $request->id)->delete();
        if ($this->request->has('partner_id')) {
            foreach (\request()->input('partner_id') as $partner_id)
                PartnerDoctor::create([
                    'user_id' => $request->id,
                    'partner_id' => $partner_id
                ]);
        }
        return redirect()->back()->with('success', 'اطلاعات این کاربر بروز رسانی شد.')->withInput();

    }

    public function storeSecretary($data)
    {
        DoctorInformation::updateOrCreate(
            ['doctor_id' => $data['doctor_id'] , 'office_secretary_mobile' => $data['office_secretary_mobile']],
            ['office_secretary_name' => $data['office_secretary_name']]
        );
//        return success_template(['message'=>'با موفقیت ثبت شد']);
    }

    public function slugify($text)
    {
        // replace non letter or digits by -
        $text = preg_replace('~[^\pL\d]+~u', '-', $text);

        // trim
        $text = trim($text, '-');

        // remove duplicate -
        $text = preg_replace('~-+~', '-', $text);

        // lowercase
        $text = strtolower($text);

        if (empty($text)) {
            return 'n-a';
        }

        return $text;
    }

    public function exportUsers()
    {


        $users = User::where('approve', '1')->orderBy('created_at', 'desc')->get();

        $visit_seen_counter = [];
        $visit_counter = [];
        if ($users) {
            foreach ($users as $item) {

                $visit_seen = EventReserves::where('doctor_id', $item['id'])->where('visit_status', 'end')->orderBy('created_at', 'desc')->count();
                $visit_seen_counter[$item['id']] = (string)$visit_seen;

                $visit = DoctorCalender::where('user_id', $item['id'])->where('fa_data', '>=', jdate('Y-m-d'))->orderBy('created_at', 'desc')->count();
                $visit_counter[$item['id']] = (string)$visit;

                $last_visit = DoctorCalender::where('user_id', $item['id'])->where('fa_data', '>=', jdate('Y-m-d'))->orderBy('created_at', 'desc')->first();
                $lastVisit[$item['id']] = optional($last_visit)->fa_data;

                $has_token = DB::table('oauth_access_tokens')->where('user_id',$item['id'])->first();
                $hasToken[$item['id']] = $has_token ? 'دارد' : 'ندارد';
            }
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
        $objPHPExcel->getActiveSheet()->SetCellValue('A1', 'نام و نام خانوادگی');
        $objPHPExcel->getActiveSheet()->SetCellValue('B1', 'ایمیل');
        $objPHPExcel->getActiveSheet()->SetCellValue('C1', 'شماره موبایل');
        $objPHPExcel->getActiveSheet()->SetCellValue('D1', 'تاریخ عضویت');
        $objPHPExcel->getActiveSheet()->SetCellValue('E1', 'وضعیت پنل');
        $objPHPExcel->getActiveSheet()->SetCellValue('F1', 'تخصص پزشک');
        $objPHPExcel->getActiveSheet()->SetCellValue('G1', 'تعداد ویزیت های موفق');
        $objPHPExcel->getActiveSheet()->SetCellValue('H1', 'تعداد وقتهای تنظیمی آتی');
        $objPHPExcel->getActiveSheet()->SetCellValue('I1', 'وضعیت');
        $objPHPExcel->getActiveSheet()->SetCellValue('J1', 'تاریخ آخرین نوبت');
        $objPHPExcel->getActiveSheet()->SetCellValue('K1', 'سابقه ورود');
        $objPHPExcel->getActiveSheet()->SetCellValue('L1', 'آدرس');


        $num = 2;

        if ($users) {
            foreach ($users as $item) {

                $fullname = ($item['fullname']) ? $item['doctor_nickname'] . ' ' . $item['fullname'] : '-';

                $objPHPExcel->getActiveSheet()->SetCellValue('A' . $num, $fullname);
                $objPHPExcel->getActiveSheet()->SetCellValue('B' . $num, $item['email']);
                $objPHPExcel->getActiveSheet()->SetCellValue('C' . $num, $item['mobile']);
                $objPHPExcel->getActiveSheet()->SetCellValue('D' . $num, jdate('Y/m/d', strtotime($item['created_at'])));
                switch ($item['doctor_status']) {
                    case "active":
                        $objPHPExcel->getActiveSheet()->SetCellValue('E' . $num, 'تایید');
                        break;
                    case "inactive":
                        $objPHPExcel->getActiveSheet()->SetCellValue('E' . $num, 'معلق');
                        break;
                    case "failed":
                        $objPHPExcel->getActiveSheet()->SetCellValue('E' . $num, 'رد شده');
                        break;
                }

//                if ($item->specializations()->first()) {
//                    $specializations = '';
//                    foreach ($item->specializations()->get() as $sp){
//                        $specializations .= $sp->name . ' ';
//                    }
//                    $objPHPExcel->getActiveSheet()->SetCellValue('F' . $num, $specializations);
                if ($item->sp_gp) {
                    $objPHPExcel->getActiveSheet()->SetCellValue('F' . $num, $item['sp_gp']);
                }else {
                    $objPHPExcel->getActiveSheet()->SetCellValue('F' . $num, 'وارد نشده');
                }

                $objPHPExcel->getActiveSheet()->SetCellValue('G' . $num, $visit_seen_counter[$item['id']]);
                $objPHPExcel->getActiveSheet()->SetCellValue('H' . $num, $visit_counter[$item['id']]);

                switch($item['status']) {
                    case 'active':
                        $objPHPExcel->getActiveSheet()->SetCellValue('I' . $num, 'فعال');
                        break;
                    case 'inactive':
                        $objPHPExcel->getActiveSheet()->SetCellValue('I' . $num, 'غیر فعال');
                        break;
                    case 'imported':
                        $objPHPExcel->getActiveSheet()->SetCellValue('I' . $num, 'فعال-ایمپورت شده');
                        break;
                }

                $objPHPExcel->getActiveSheet()->SetCellValue('J' . $num, $lastVisit[$item['id']]);
                $objPHPExcel->getActiveSheet()->SetCellValue('K' . $num, $hasToken[$item['id']]);
                $objPHPExcel->getActiveSheet()->SetCellValue('L' . $num, $item['address']);

                $num++;

            }
        }



        // Rename sheet
        $objPHPExcel->getActiveSheet()->setTitle('doctor list');


        // Save Excel 2007 file

        $objWriter = new \PHPExcel_Writer_Excel2007($objPHPExcel);

        $path_blank = str_replace(get_ev('path_live'), get_ev('path_root'), base_path('files'));
        $fileName = time() . '_save.xlsx';


        $objWriter->save("{$path_blank}/{$fileName}");

        return redirect(get_ev('statics_server') . '/files/' . $fileName);


    }

    public function exportUsersStatus()
    {

        $doctor_status = $this->request->doctor_status;

        $users = User::where('approve', '1')->where('doctor_status', $doctor_status)->orderBy('created_at', 'desc')->get();

        $visit_seen_counter = [];
        $visit_counter = [];
        if ($users) {
            foreach ($users as $item) {

                $visit_seen = EventReserves::where('doctor_id', $item['id'])->where('visit_status', 'end')->orderBy('created_at', 'desc')->count();
                $visit_seen_counter[$item['id']] = (string)$visit_seen;

                $visit = DoctorCalender::where('user_id', $item['id'])->where('fa_data', '>=', jdate('Y-m-d'))->orderBy('created_at', 'desc')->count();
                $visit_counter[$item['id']] = (string)$visit;

            }
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
        $objPHPExcel->getActiveSheet()->SetCellValue('A1', 'نام و نام خانوادگی');
        $objPHPExcel->getActiveSheet()->SetCellValue('B1', 'ایمیل');
        $objPHPExcel->getActiveSheet()->SetCellValue('C1', 'شماره موبایل');
        $objPHPExcel->getActiveSheet()->SetCellValue('D1', 'تاریخ عضویت');
        $objPHPExcel->getActiveSheet()->SetCellValue('E1', 'وضعیت پنل');
        $objPHPExcel->getActiveSheet()->SetCellValue('F1', 'تخصص پزشک');
        $objPHPExcel->getActiveSheet()->SetCellValue('G1', 'تعداد ویزیت های موفق');
        $objPHPExcel->getActiveSheet()->SetCellValue('H1', 'تعداد وقتهای تنظیمی آتی');
        $objPHPExcel->getActiveSheet()->SetCellValue('I1', 'وضعیت');

        $num = 2;

        if ($users) {
            foreach ($users as $item) {

                $fullname = ($item['fullname']) ? $item['doctor_nickname'] . ' ' . $item['fullname'] : '-';

                $objPHPExcel->getActiveSheet()->SetCellValue('A' . $num, $fullname);
                $objPHPExcel->getActiveSheet()->SetCellValue('B' . $num, $item['email']);
                $objPHPExcel->getActiveSheet()->SetCellValue('C' . $num, $item['mobile']);
                $objPHPExcel->getActiveSheet()->SetCellValue('D' . $num, jdate('Y/m/d', strtotime($item['created_at'])));

                switch ($item['doctor_status']) {
                    case "active":
                        $objPHPExcel->getActiveSheet()->SetCellValue('E' . $num, 'تایید');
                        break;
                    case "inactive":
                        $objPHPExcel->getActiveSheet()->SetCellValue('E' . $num, 'معلق');
                        break;
                    case "failed":
                        $objPHPExcel->getActiveSheet()->SetCellValue('E' . $num, 'رد شده');
                        break;
                }

//                if ($item->specializations()->first()) {
//                    $specializations = '';
//                    foreach ($item->specializations()->get() as $sp){
//                        $specializations .= $sp->name . ' ';
//                    }
//                    $objPHPExcel->getActiveSheet()->SetCellValue('F' . $num, $specializations);
                if ($item->sp_gp) {
                    $objPHPExcel->getActiveSheet()->SetCellValue('F' . $num, $item['sp_gp']);
                }else {
                    $objPHPExcel->getActiveSheet()->SetCellValue('F' . $num, 'وارد نشده');
                }


//                dd($visit_counter[$item['id']]);
                //$visit_seen_counter[$item['id']]
                //$visit_counter[$item['id']]
                $objPHPExcel->getActiveSheet()->SetCellValue('G' . $num,  $visit_seen_counter[$item['id']] );
                $objPHPExcel->getActiveSheet()->SetCellValue('H' . $num, $visit_counter[$item['id']]);

                switch($item['status']) {
                    case 'active':
                        $objPHPExcel->getActiveSheet()->SetCellValue('I' . $num, 'فعال');
                        break;
                    case 'inactive':
                        $objPHPExcel->getActiveSheet()->SetCellValue('I' . $num, 'غیر فعال');
                        break;
                    case 'imported':
                        $objPHPExcel->getActiveSheet()->SetCellValue('I' . $num, 'فعال-ایمپورت شده');
                        break;
                }

                $num++;

            }
        }


        // Rename sheet
        $objPHPExcel->getActiveSheet()->setTitle('doctor list');

        // Save Excel 2007 file

        $objWriter = new \PHPExcel_Writer_Excel2007($objPHPExcel);

        $path_blank = str_replace(get_ev('path_live'), get_ev('path_root'), base_path('files'));
        $fileName = time() . '_save.xlsx';


        $objWriter->save("{$path_blank}/{$fileName}");

        return redirect(get_ev('statics_server') . '/files/' . $fileName);


    }

    public function exportUsersDiActive()
    {

        $users = User::where('approve', '1')->where('doctor_status', 'active')->where('status', 'active')
            ->whereNotIn('id',function ($query){
                $query->select('user_id')->from('doctor_calenders')->groupBy('user_id');
            })
            ->orderBy('created_at', 'desc')->get();

        $visit_seen_counter = [];
        $visit_counter = [];
        $visit_all_counter = [];
        if ($users) {
            foreach ($users as $item) {

                $visit_seen = EventReserves::where('doctor_id', $item['id'])->where('visit_status', 'end')->orderBy('created_at', 'desc')->count();
                $visit_seen_counter[$item['id']] = (string)$visit_seen;

                $visit = DoctorCalender::where('user_id', $item['id'])->where('fa_data', '>=', jdate('Y-m-d'))->orderBy('created_at', 'desc')->count();
                $visit_counter[$item['id']] = (string)$visit;

                $waiting = UserDoctorNotification::where('doctor_id', $item['id'])->where('sent_message',0)->orderBy('created_at', 'desc')->count();
                $waiting_counter[$item['id']] = (string)$visit;

            }
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
        $objPHPExcel->getActiveSheet()->SetCellValue('A1', 'نام و نام خانوادگی');
        $objPHPExcel->getActiveSheet()->SetCellValue('B1', 'ایمیل');
        $objPHPExcel->getActiveSheet()->SetCellValue('C1', 'شماره موبایل');
        $objPHPExcel->getActiveSheet()->SetCellValue('D1', 'تاریخ عضویت');
        $objPHPExcel->getActiveSheet()->SetCellValue('E1', 'وضعیت پنل');
        $objPHPExcel->getActiveSheet()->SetCellValue('F1', 'تخصص پزشک');
        $objPHPExcel->getActiveSheet()->SetCellValue('G1', 'تعداد ویزیت های موفق');
        $objPHPExcel->getActiveSheet()->SetCellValue('H1', 'تعداد وقتهای تنظیمی آتی');
        $objPHPExcel->getActiveSheet()->SetCellValue('I1', 'وضعیت');
        $objPHPExcel->getActiveSheet()->SetCellValue('J1', 'لیست انتظار');

        $num = 2;

        if ($users) {
            foreach ($users as $item) {

                $fullname = ($item['fullname']) ? $item['doctor_nickname'] . ' ' . $item['fullname'] : '-';

                $objPHPExcel->getActiveSheet()->SetCellValue('A' . $num, $fullname);
                $objPHPExcel->getActiveSheet()->SetCellValue('B' . $num, $item['email']);
                $objPHPExcel->getActiveSheet()->SetCellValue('C' . $num, $item['mobile']);
                $objPHPExcel->getActiveSheet()->SetCellValue('D' . $num, jdate('Y/m/d', strtotime($item['created_at'])));
                switch ($item['doctor_status']) {
                    case "active":
                        $objPHPExcel->getActiveSheet()->SetCellValue('E' . $num, 'تایید');
                        break;
                    case "inactive":
                        $objPHPExcel->getActiveSheet()->SetCellValue('E' . $num, 'معلق');
                        break;
                    case "failed":
                        $objPHPExcel->getActiveSheet()->SetCellValue('E' . $num, 'رد شده');
                        break;
                }

//                if ($item->specializations()->first()) {
//                    $specializations = '';
//                    foreach ($item->specializations()->get() as $sp){
//                        $specializations .= $sp->name . ' ';
//                    }
//                    $objPHPExcel->getActiveSheet()->SetCellValue('F' . $num, $specializations);
                if ($item->sp_gp) {
                    $objPHPExcel->getActiveSheet()->SetCellValue('F' . $num, $item['sp_gp']);
                }else {
                    $objPHPExcel->getActiveSheet()->SetCellValue('F' . $num, 'وارد نشده');
                }

                $objPHPExcel->getActiveSheet()->SetCellValue('G' . $num, $visit_seen_counter[$item['id']]);
                $objPHPExcel->getActiveSheet()->SetCellValue('H' . $num, $visit_counter[$item['id']]);

                switch($item['status']) {
                    case 'active':
                        $objPHPExcel->getActiveSheet()->SetCellValue('I' . $num, 'فعال');
                        break;
                    case 'inactive':
                        $objPHPExcel->getActiveSheet()->SetCellValue('I' . $num, 'غیر فعال');
                        break;
                    case 'imported':
                        $objPHPExcel->getActiveSheet()->SetCellValue('I' . $num, 'فعال-ایمپورت شده');
                        break;
                }
                $objPHPExcel->getActiveSheet()->SetCellValue('J' . $num, $waiting_counter[$item['id']]);


                $num++;

            }
        }



        // Rename sheet
        $objPHPExcel->getActiveSheet()->setTitle('doctor list');


        // Save Excel 2007 file

        $objWriter = new \PHPExcel_Writer_Excel2007($objPHPExcel);

        $path_blank = str_replace(get_ev('path_live'), 'httpdocs/upload', base_path('files'));
        $fileName = time() . '_save.xlsx';


        $objWriter->save("{$path_blank}/{$fileName}");

        return redirect(get_ev('statics_server') . '/files/' . $fileName);


    }

    public function assignSpecialization($request)
    {
        $response = $this->specialization->assignDoctor($request);
        if ($response['status'])
            return true;
        else
            return false;
    }

    public function ChangingApprove(User $user)
    {
        if ($user->calenders()
                ->whereDate('data','>=',Carbon::now()
                    ->format('Y-m-d'))->get()->isEmpty() &&
            $user->DoctorEvents()->get()->isEmpty() &&
            $user->DoctorTransactions()->get()->isEmpty()) {
            $user->approve = 2;
            $user->save();
            return redirect()->back()
                ->with(['success' => 'تغییر کاربری با موفقیت انجام شد'])
                ->withInput();
        }
        return redirect()->back()
            ->with(['error' => 'تغییر کاربری امکان پذیر نیست'])
            ->withInput();
    }

    public function report(User $doctor)
    {
        return view('admin.doctors.report', compact('doctor'));
    }

    public function underemployed()
    {
        $request = null;

        if ($this->request->has('from') && $this->request->input('from'))
        {
            $from = change_number($this->request->input('from'));
            /* @var \Hekmatinasser\Verta\Verta $from_date */
            $from = explode('/',$from);
            $from_date = Verta::create();
            $from_date->year($from[0]);
            $from_date->month($from[1]);
            $from_date->day($from[2]);
            $from = $from_date->format('Y-m-d');


            DB::connection()->enableQueryLog();
            $request = User::withCount(['DoctorEvents' => function ($query){
                $query->where('visit_status','end');
            }])
                ->where('approve', 1)
                ->where('doctor_status','active')
                ->whereIn('status',['active','imported'])
                ->whereNotIn('users.id',TestAccount())
                ->whereHas('calenders',function ($query) use ($from){
                    $query->groupBy('user_id')
                        ->having(DB::raw('max(fa_data)') ,'=',$from)
                        ->orderByDesc(DB::raw('max(fa_data)'));
                    if ($this->request->input('partner') == 0){
                        $query->where(function ($where){
                            $where->where('partner_id',0)
                                ->orWhereNull('partner_id');
                        });
                    }else{
                        $query->where(function ($where) {
                            $where->where('partner_id', '!=',0)
                                ->orWhereNotNull('partner_id');
                        });
                    }
                })
                ->whereDoesntHave('calenders', function ($query) use ($from) {
                    $query->groupBy('user_id')
                        ->having(DB::raw('max(fa_data)') ,'>',$from)
                        ->orderByDesc(DB::raw('max(fa_data)'));
                    if ($this->request->input('partner') == 0){
                        $query->where(function ($where){
                            $where->where('partner_id',0)
                                ->orWhereNull('partner_id');
                        });
                    }else{
                        $query->where(function ($where) {
                            $where->where('partner_id', '!=',0)
                                ->orWhereNotNull('partner_id');
                        });
                    }
                })
            ;

            $queries = DB::getQueryLog();
//            dd($queries);

        } else{
            $from = jdate('Y-m-d','');

            $request = User::withCount(['DoctorEvents' => function ($query){
                $query->where('visit_status','end');
            }])->
            where('approve', 1)
                ->where('doctor_status','active')
                ->whereIn('status',['active','imported'])
                ->whereNotIn('users.id',TestAccount())
                ->whereHas('calenders',function ($query) use ($from){
                    $query->groupBy('user_id')
                        ->having(DB::raw('max(fa_data)') ,'<=',$from)
                        ->orderByDesc(DB::raw('max(fa_data)'));
                    if ($this->request->input('partner') == 0){
                        $query->where(function ($where){
                            $where->where('partner_id',0)
                                ->orWhereNull('partner_id');
                        });
                    }else{
                        $query->where(function ($where) {
                            $where->where('partner_id', '!=',0)
                                ->orWhereNotNull('partner_id');
                        });
                    }
                })
                ->whereDoesntHave('calenders', function ($query) use ($from) {
                    $query->groupBy('user_id')
                        ->having(DB::raw('max(fa_data)') ,'>',$from)
                        ->orderByDesc(DB::raw('max(fa_data)'));
                    if ($this->request->input('partner') == 0){
                        $query->where(function ($where){
                            $where->where('partner_id',0)
                                ->orWhereNull('partner_id');
                        });
                    }else{
                        $query->where(function ($where) {
                            $where->where('partner_id', '!=',0)
                                ->orWhereNotNull('partner_id');
                        });
                    }
                });

        }

        if (\request()->has('filter_secretary') && \request()->input('filter_secretary')){
            $request = $request->whereHas('secretary', function ($query) {
                $query->where('full_name', 'LIKE', '%' . \request()->input('filter_secretary') . '%');
            });
        }

        if (\request()->has('specialization_id') && \request()->input('specialization_id')){
            $request = $request->whereHas('specializations', function ($query) {
                    $query->where('id',\request()->input('specialization_id'));
            });
        }

        if (\request()->has('filter_status') && \request()->input('filter_status')) {
            $request = $request->where('status',  \request()->input('filter_status') );
        }


        $request = $request->orderBy('doctor_events_count', (\request()->has('filter_counts') && \request()->input('filter_counts')) ?  \request()->input('filter_counts') : 'DESC');

        $request = $request->paginate(10);

        $specializations = Specialization::all();

        return view('admin.doctors.underemployed', compact('request','specializations'));
    }

    public function top(Request $request)
    {
        $request->validate([
            'take' => 'integer'
        ]);
        $data = EventReserves::whereDate('reserve_time', '>=', Carbon::now()->startOfWeek()->subDays(2)->format('Y-m-d'))
            ->whereDate('reserve_time', '<=', Carbon::now()->format('Y-m-d'))
            ->whereNotIn('users.id', TestAccount())
            ->whereNotIn('users.id', [321, 11572, 3334])
            ->join('users', 'event_reserves.doctor_id', 'users.id')
            ->join('user_specializations as us', 'us.user_id', 'users.id')
            ->join('specializations as sp', function ($join) {
                $join->on('sp.id', 'us.specialization_id');
            })
            ->orderBy('counts', 'DESC')
            ->groupBy('users.id');

            $data = $data->select(DB::raw('count(event_reserves.id) as counts'),
                'badge.plan as badges',
                'users.name', 'users.fullname', 'users.job_title', 'users.username', 'users.doctor_nickname',
                'users.family', 'users.picture', 'users.online_status', 'sp.name as sp_name');

        if (\request()->has('take'))
            $data = $data->limit(\request()->has('take') ? \request()->input('take') : '')->get();
        else
            $data = $data->get();
        if ($data)
            if (!$data->isEmpty())
                return view('admin.doctors.top', compact('request'));
            else
                return success_template(['message' => 'اطلاعاتی با این مشخصات موجود نیست']);
        return error_template('دریافت اطلاعات با مشکل مواجه شده است');
    }

    public function underemployed2()
    {
        $date = $this->request->input('date',1);
        $request = null;
        if ($date) {
            $since = Carbon::now()->subWeeks($date);
            $from = Carbon::now()->subWeeks($date)->subMonths(1);

            $request = User::
            where('approve', 1)
                ->where('doctor_status','active')
                ->whereIn('status',['active','imported'])
                ->whereNotIn('id',TestAccount())
                ->whereDoesntHave('calenders',function ($query){
                    $query->whereDate('data','>',Carbon::now()->format('Y-m-d'));
                })
                ->whereHas('calenders',function ($query) use ($since,$from){
                    $query->whereDate('data','<=',$since)
                        ->whereDate('data','>=',$from);
                    if ($this->request->input('partner') == 0){
                        $query->where(function ($where){
                            $where->where('partner_id',0)
                                ->orWhereNull('partner_id');
                        });
                    }else{
                        $query->where(function ($where) {
                            $where->where('partner_id', '!=',0)
                                ->orWhereNotNull('partner_id');
                        });
                    }
                })
                ->whereDoesntHave('calenders', function ($query) use ($since) {
                    $query->whereDate('data', '>', $since->format('Y-m-d'));
                    if ($this->request->input('partner') == 0){
                        $query->where(function ($where){
                            $where->where('partner_id',0)
                                ->orWhereNull('partner_id');
                        });
                    }else{
                        $query->where(function ($where) {
                            $where->where('partner_id', '!=',0)
                                ->orWhereNotNull('partner_id');
                        });
                    }
                })->paginate(10);
        }
        return view('admin.doctors.underemployed', compact('request'));
    }

    public function exportUnderemployed()
    {
        $request = null;

        if ($this->request->has('from') && $this->request->input('from'))
        {
            $from = change_number($this->request->input('from'));
            /* @var \Hekmatinasser\Verta\Verta $from_date */
            $from = explode('/',$from);
            $from_date = Verta::create();
            $from_date->year($from[0]);
            $from_date->month($from[1]);
            $from_date->day($from[2]);
            $from = $from_date->format('Y-m-d');


            DB::connection()->enableQueryLog();
            $request = User::withCount(['DoctorEvents' => function ($query){
                $query->where('visit_status','end');
            }])
                ->where('approve', 1)
                ->where('doctor_status','active')
                ->whereIn('status',['active','imported'])
                ->whereNotIn('users.id',TestAccount())
                ->whereHas('calenders',function ($query) use ($from){
                    $query->groupBy('user_id')
                        ->having(DB::raw('max(fa_data)') ,'=',$from)
                        ->orderByDesc(DB::raw('max(fa_data)'));
                    if ($this->request->input('partner') == 0){
                        $query->where(function ($where){
                            $where->where('partner_id',0)
                                ->orWhereNull('partner_id');
                        });
                    }else{
                        $query->where(function ($where) {
                            $where->where('partner_id', '!=',0)
                                ->orWhereNotNull('partner_id');
                        });
                    }
                })
                ->whereDoesntHave('calenders', function ($query) use ($from) {
                    $query->groupBy('user_id')
                        ->having(DB::raw('max(fa_data)') ,'>',$from)
                        ->orderByDesc(DB::raw('max(fa_data)'));
                    if ($this->request->input('partner') == 0){
                        $query->where(function ($where){
                            $where->where('partner_id',0)
                                ->orWhereNull('partner_id');
                        });
                    }else{
                        $query->where(function ($where) {
                            $where->where('partner_id', '!=',0)
                                ->orWhereNotNull('partner_id');
                        });
                    }
                })
            ;

            $queries = DB::getQueryLog();
//            dd($queries);

        } else{
            $from = jdate('Y-m-d','');

            $request = User::withCount(['DoctorEvents' => function ($query){
                $query->where('visit_status','end');
            }])->
            where('approve', 1)
                ->where('doctor_status','active')
                ->whereIn('status',['active','imported'])
                ->whereNotIn('users.id',TestAccount())
                ->whereHas('calenders',function ($query) use ($from){
                    $query->groupBy('user_id')
                        ->having(DB::raw('max(fa_data)') ,'<=',$from)
                        ->orderByDesc(DB::raw('max(fa_data)'));
                    if ($this->request->input('partner') == 0){
                        $query->where(function ($where){
                            $where->where('partner_id',0)
                                ->orWhereNull('partner_id');
                        });
                    }else{
                        $query->where(function ($where) {
                            $where->where('partner_id', '!=',0)
                                ->orWhereNotNull('partner_id');
                        });
                    }
                })
                ->whereDoesntHave('calenders', function ($query) use ($from) {
                    $query->groupBy('user_id')
                        ->having(DB::raw('max(fa_data)') ,'>',$from)
                        ->orderByDesc(DB::raw('max(fa_data)'));
                    if ($this->request->input('partner') == 0){
                        $query->where(function ($where){
                            $where->where('partner_id',0)
                                ->orWhereNull('partner_id');
                        });
                    }else{
                        $query->where(function ($where) {
                            $where->where('partner_id', '!=',0)
                                ->orWhereNotNull('partner_id');
                        });
                    }
                });

        }

        if (\request()->has('filter_secretary') && \request()->input('filter_secretary')){
            $request = $request->whereHas('secretary', function ($query) {
                $query->where('full_name', 'LIKE', '%' . \request()->input('filter_secretary') . '%');
            });
        }

        if (\request()->has('specialization_id') && \request()->input('specialization_id')){
            $request = $request->whereHas('specializations', function ($query) {
                $query->where('id',\request()->input('specialization_id'));
            });
        }

        if (\request()->has('filter_status') && \request()->input('filter_status')) {
            $request = $request->where('status',  \request()->input('filter_status') );
        }


        $request = $request->orderBy('doctor_events_count', (\request()->has('filter_counts') && \request()->input('filter_counts')) ?  \request()->input('filter_counts') : 'DESC');

        $request = $request->get();

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
        $objPHPExcel->getActiveSheet()->SetCellValue('B1', 'تخصص ها');
        $objPHPExcel->getActiveSheet()->SetCellValue('C1', 'شماره موبایل');
        $objPHPExcel->getActiveSheet()->SetCellValue('D1', 'تاریخ عضویت');
        $objPHPExcel->getActiveSheet()->SetCellValue('E1', 'نوع');
        $objPHPExcel->getActiveSheet()->SetCellValue('F1', 'موفق');
        $objPHPExcel->getActiveSheet()->SetCellValue('G1', 'کل وقت ها');
        $objPHPExcel->getActiveSheet()->SetCellValue('H1', 'آخرین وقت');
        $objPHPExcel->getActiveSheet()->SetCellValue('I1', 'در انتظار');

        $num = 2;

        if ($request) {
            foreach ($request as $item) {

                $times = \request()->input('partner') == 0 ?
                    $item->calenders()->where(function ($query){
                        $query->whereNull('partner_id')->orWhere('partner_id',0);})->count() :
                    $item->calenders()->where(function ($query){
                        $query->whereNotNull('partner_id')->orWhere('partner_id','!=',0);})->count();

                $fullname = ($item['fullname']) ? $item['doctor_nickname'] . ' ' . $item['fullname'] : '-';
                $specializations='وارد نشده';
                if ($item->specializations()->first()){
                    $counter =0;
                    foreach ($item->specializations()->get() as $sp){
                        if ($counter==0)
                        {
                            $specializations = $sp->name;
                        }
                        else
                        {
                            $specializations .= ' - ' . $sp->name;
                        }
                    }
                }
                $objPHPExcel->getActiveSheet()->SetCellValue('A' . $num, $fullname);
                $objPHPExcel->getActiveSheet()->SetCellValue('B' . $num, $item['sp_gp']);
                $objPHPExcel->getActiveSheet()->SetCellValue('C' . $num, $item['mobile']);
                $objPHPExcel->getActiveSheet()->SetCellValue('D' . $num, jdate('Y/m/d', strtotime($item['created_at'])));
                $objPHPExcel->getActiveSheet()->SetCellValue('E' . $num, $item['status'] == 'active' ? 'ثبت نامی' : 'ایمپورت شده');
                $objPHPExcel->getActiveSheet()->SetCellValue('F' . $num, (string)$item->DoctorEvents('end')->count());
                $objPHPExcel->getActiveSheet()->SetCellValue('G' . $num, (string)$times);
                $objPHPExcel->getActiveSheet()->SetCellValue('H' . $num, jdate('Y-m-d',strtotime($item->calenders()->max('data'))));
                $objPHPExcel->getActiveSheet()->SetCellValue('I' . $num, (string)$item->Waiting()->where('sent_message',0)->count());
                $num++;

            }
        }


        // Rename sheet
        $objPHPExcel->getActiveSheet()->setTitle('underemployed doctor list');


        // Save Excel 2007 file

        $objWriter = new \PHPExcel_Writer_Excel2007($objPHPExcel);

        $path_blank = str_replace(get_ev('path_live'), get_ev('path_root'), base_path('files'));
        $fileName = time() . '_save.xlsx';


        $objWriter->save("{$path_blank}/{$fileName}");

        return redirect(get_ev('statics_server') . '/files/' . $fileName);
    }

    public function contracts(Request $request)
    {
        $doctor_id = $request->get('doctor_id');
        $request = DoctorContract::with('doctor')->when($doctor_id,function ($q) use ($doctor_id){
            $q->where('user_id',$doctor_id);
        })->paginate(15);

        return view('admin.doctors.contracts',compact('request'));

    }

    public function contract($doctor_id)
    {
        $doctor = User::where(['approve'=>1,'id'=>$doctor_id])->firstOrFail();

        return view('admin.doctors.contract',compact('doctor'));
    }

    public function storeContract(Request $request, $doctor_id)
    {
        $contract = new DoctorContract();
        $contract->registration_id = $request->registration_id;
        $contract->user_id = $doctor_id;
        $contract->percent = $request->percent;
        $contract->terminal_id = $request->terminal_id ?? null;
        $contract->category = $request->category ?? 'wallet';
        $contract->contract_type = $request->contract_type;
        if ($request->file('picture')) {
            $picture = $this->uploadImageCt('picture');
            $contract->picture = $picture;
        }
        if ($request->file('sign_picture')) {
            $sign_picture = $this->uploadImageCt('sign_picture');
            $contract->sign_picture = $sign_picture;
        }
        if (\request()->has('start_at')) {
            $start_at = str_replace('/', '-',
                change_number(\request()->input('start_at')));
            $start_at = explode('-', $start_at);
            $start_at = \Hekmatinasser\Verta\Verta::getGregorian($start_at[0], $start_at[1], $start_at[2]);
            $start_at = Carbon::create($start_at[0], $start_at[1], $start_at[2])->format('Y-m-d');
        }
        if (\request()->has('expire_at')) {
            $expire_at = str_replace('/', '-',
                change_number(\request()->input('expire_at')));
            $expire_at = explode('-', $expire_at);
            $expire_at = Verta::getGregorian($expire_at[0], $expire_at[1], $expire_at[2]);
            $expire_at = Carbon::create($expire_at[0], $expire_at[1], $expire_at[2])->format('Y-m-d');
        }
        $contract->start_at = $start_at;
        $contract->expire_at = $expire_at;
        $contract->status = 'active';
        $contract->save();

        return redirect('cp-manager/doctors/contracts?doctor_id='.$contract->user_id)
            ->with('success', 'قرارداد با موفقیت ثبت شد.')->withInput();
    }
}
