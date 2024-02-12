<?php

namespace App\Http\Controllers\Admin\User;

use App\Model\Platform\State;
use App\Model\Visit\DoctorCalender;
use App\Model\Visit\EventReserves;
use App\Model\Visit\TransactionCredit;
use App\RequestCodesLog;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class UserController extends Controller
{

    protected $request;

    public function __construct(Request $request)
    {
        date_default_timezone_set("Asia/Tehran");
        $this->request = $request;

        require(base_path('app/jdf.php'));

    }

    public function codes()
    {
        $item=null;
        if (\request()->input('mobile'))
            $item = RequestCodesLog::where('mobile',\request()->input('mobile'))
                ->orderBy('created_at','DESC')
                ->take(3)->get();
        return view('admin.Codes.requests',compact('item'));
    }

    public function unblock()
    {
        $mobile = \request()->input('mobile');
        RequestCodesLog::where('mobile',\request()->input('mobile'))
            ->whereDate('created_at',Carbon::now()->format('Y-m-d'))
            ->delete();
        return redirect()->back()->with(['success'=>'کاربر با موفقیت رفع بلاک شد']);
    }

    public function users(){

        $where_array = array();

        //filter set to query
        $filter_name = trim($this->request->get('filter_name'));
        $filter_mobile = trim($this->request->get('filter_mobile'));
        $filter_email = trim($this->request->get('filter_email'));
        $filter_from_ = trim($this->request->get('filter_from_'));
        $filter_to_ = trim($this->request->get('filter_to_'));
        $filter_status = trim($this->request->get('filter_status'));
        $filter_credit= trim($this->request->get('filter_credit'));
        if($filter_mobile){
            $where_array[] = array('mobile', "LIKE" , "%" . $filter_mobile . "%");
        }
        if($filter_email){
            $where_array[] = array('email', "LIKE" , "%" . $filter_email . "%");
        }
        if($filter_status){
            $where_array[] = array('status', $filter_status);
        }
        if($filter_from_){
            $where_array[] = array('from_', $filter_from_);
        }
        if($filter_credit != null){
            if ($filter_credit==1)
                $where_array[] = array('credit', '>',0);
            if ($filter_credit==0)
                $where_array[] = array('credit',0);
        }

        $users = User::where($where_array)->where('users.approve', '2')
                ->where(function ($query) use ($filter_name) {
                    $query->search($filter_name, false);
                })->orderBy('users.created_at', 'desc');
        if ($filter_to_){
            $users = $users->join('user_activity_logs as ual', 'users.id', '=', 'ual.user_id')
            ->where('action_type' , $filter_to_)
            ->groupBy('ual.user_id');
        }

        $users = $users->paginate(35);
        //view data
        return view('admin/users/index', ['request' => $users]);

    }

    public function support(){

        $where_array = array();

        //filter set to query
        $filter_name = trim($this->request->get('filter_name'));
        $filter_mobile = trim($this->request->get('filter_mobile'));
        $filter_email = trim($this->request->get('filter_email'));
        $filter_status = trim($this->request->get('filter_status'));
        if($filter_name){
            $where_array[] = array('fullname', "LIKE" , "%" . $filter_name . "%");
        }
        if($filter_mobile){
            $where_array[] = array('mobile', "LIKE" , "%" . $filter_mobile . "%");
        }
        if($filter_email){
            $where_array[] = array('email', "LIKE" , "%" . $filter_email . "%");
        }
        if($filter_status){
            $where_array[] = array('status', $filter_status);
        }

        $users = User::where($where_array)->where('approve', '10')->orderBy('created_at', 'desc')->paginate(35);

        //view data
        return view('admin/users/index', ['request' => $users]);

    }

    public function userAdd(){

        $province = State::orderBy('state', 'asc')->get();
        return view('admin/users/add' , [ 'province' => $province ]);

    }

    public function ActionUserAdd(){

        // Validation Data
        $ValidData = $this->validate($this->request,[
            'name' => 'required|string|max:255',
            'family' => 'required|string|max:255',
            'status' => 'required|string',
            'gender' => 'required|numeric',
            'nationalcode' => 'required|numeric|unique:users',
            'email' => 'required_without:mobile|string|email|max:255|unique:users',
            'mobile' => 'required_without:email|numeric|unique:users|regex:/(0)[0-9]{10}/',
            'password' => 'required|string|min:6',
            'state'=>'required',
            'city'=>'required'
        ]);

        $picture = null;

        if($this->request->file('picture')){

            $ValidDataImage = $this->validate($this->request,[
                'picture' => 'required|mimes:jpg,jpeg,bmp,png|max:10240',
            ]);

            $picture = $this->uploadImageCt('picture' , 'images');
        }

        User::create([
            'name' => $ValidData['name'],
            'family' => $ValidData['family'],
            'fullname' => trim($ValidData['name'] . ' ' . $ValidData['family']),
            'email' => $ValidData['email'],
            'mobile' => $ValidData['mobile'],
            'nationalcode' => $ValidData['nationalcode'],
            'status' => $ValidData['status'],
            'gender' => $ValidData['gender'],
            'username' => str_random(10),
            'token' => str_random(6),
            'approve' => 2,
            'job_title' => $this->request->get('job_title'),
            'bio' => $this->request->get('bio'),
            'state_id' => ($this->request->get('state')) ? $this->request->get('state') : 0,
            'city_id' => ($this->request->get('city')) ? $this->request->get('city') : 0,
            'address' => $this->request->get('address'),
            'picture' => $picture,
            'password' => bcrypt($ValidData['password']),
        ]);


        return redirect('cp-manager/users')->with('success' ,  'این کاربر به لیست کاربران شما اضافه شد.')->withInput();

    }

    public function userEdit(){

        //get user id
        $id = $this->request->user;

        //check user validate
        $request = User::where('id' , $id)->orderBy('id', 'desc')->first();
        if(!$request) return redirect('cp-manager/users')->with('error' ,  'اطلاعات ارسال شده اشتباه است.');

        $province = State::orderBy('state', 'asc')->get();

        return view('admin/users/edit', ['request' => $request , 'province' => $province]);

    }

    public function ActionUserEdit(){

        //get user id
        $id = $this->request->user;

        //check user validate
        $request = User::where('id' , $id)->orderBy('id', 'desc')->first();
        if(!$request) return redirect('cp-manager/users')->with('error' ,  'اطلاعات ارسال شده اشتباه است.');

        // Validation Data
        $ValidData = $this->validate($this->request,[
            'name' => 'required|string|max:255',
            'family' => 'required|string|max:255',
            //'nationalcode' => 'required',
            'status' => 'required|string',
            'credit' => 'required|numeric',
            'gender' => 'required|numeric',
        ]);

        if ($this->request->has('nationalcode')) {
            if ($this->request->get('nationalcode') != $request->nationalcode) {
                $ValidData_email = $this->validate($this->request, [
                    'nationalcode' => 'required|numeric|unique:users,nationalcode,'.$request->id,
                ]);
                $request->nationalcode = $ValidData_email['nationalcode'];
            }
        }

        if($this->request->get('email') != $request->email){
            $ValidData_email = $this->validate($this->request,[
                'email' => 'required_without:mobile|string|email|max:255|unique:users',
            ]);
            $request->email = $ValidData_email['email'];
        }

        if($this->request->get('mobile') != $request->mobile){
            $ValidData_mobile = $this->validate($this->request,[
                'mobile' => 'required_without:email|numeric|unique:users|regex:/(0)[0-9]{10}/',
            ]);
            $request->mobile = $ValidData_mobile['mobile'];
        }

        if($this->request->get('password')){
            $ValidData_password = $this->validate($this->request,[
                'password' => 'string|min:6',
            ]);
            $request->password = bcrypt($ValidData_password['password']);
        }



        if($this->request->file('picture')){

            $ValidDataImage = $this->validate($this->request,[
                'picture' => 'required|mimes:jpg,jpeg,bmp,png|max:10240',
            ]);

            $picture = $this->uploadAvatar('picture' , 'images');
            $request->picture = $picture;
        }



        if($this->request->get('credit') > $request->credit){

            $new_credit = $this->request->get('credit') - $request->credit;


            $sku = str_random(20);
            $newTransaction = new TransactionCredit();
            $newTransaction->user_id = $id;
            $newTransaction->amount = $new_credit;
            $newTransaction->token = $sku;
            $newTransaction->status = 'paid';
            $newTransaction->message = 'افزایش اعتبار توسط ادمین ('. auth()->user()->fullname .')';
            $newTransaction->save();


        }else if($this->request->get('credit') < $request->credit){

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

        $request->name = $ValidData['name'];
        $request->family = $ValidData['family'];
        $request->fullname = trim($ValidData['name'] . ' ' .$ValidData['family']);
        $request->status = $ValidData['status'];
        $request->gender = $ValidData['gender'];
        $request->job_title = $this->request->get('job_title');
        $request->bio = $this->request->get('bio');
        $request->state_id = ($this->request->get('state')) ? $this->request->get('state') : 0;
        $request->city_id = ($this->request->get('city')) ? $this->request->get('city') : 0;
        $request->address = $this->request->get('address');
        $request->credit = $this->request->get('credit');
        $request->save();


        return redirect('cp-manager/users')->with('success' ,  'اطلاعات این کاربر بروز رسانی شد.')->withInput();

    }

    public function exportUsers(){


        $users = User::where('approve', '2')->orderBy('created_at', 'desc')->get();
        $visit_counter = [];
        if($users){
            foreach ($users as $item){

                $visit = EventReserves::where('user_id', $item['id'])->where('status'  , 'active')
                    ->orderBy('created_at', 'desc')->count();
                $visit_counter[$item['id']] = $visit;

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
        $objPHPExcel->getActiveSheet()->SetCellValue('E1', 'تعداد ویزیت');
        $objPHPExcel->getActiveSheet()->SetCellValue('F1', 'وضعیت');
        $objPHPExcel->getActiveSheet()->SetCellValue('G1', 'Approve');

        $num = 2;

        if($users){
            foreach ($users as $item){
                $fullname = ($item['fullname']);

                $objPHPExcel->getActiveSheet()->SetCellValue('A'.$num, $fullname);
                $objPHPExcel->getActiveSheet()->SetCellValue('B'.$num, $item['email']);
                $objPHPExcel->getActiveSheet()->SetCellValue('C'.$num, $item['mobile']);
                $objPHPExcel->getActiveSheet()->SetCellValue('D'.$num, jdate('Y/m/d', strtotime($item['created_at'])));
                $objPHPExcel->getActiveSheet()->SetCellValue('E'.$num, $visit_counter[$item['id']]);

                switch($item['status']){
                    case "active":
                        $objPHPExcel->getActiveSheet()->SetCellValue('F'.$num, 'فعال');
                        break;
                    case "inactive":
                        $objPHPExcel->getActiveSheet()->SetCellValue('F'.$num, 'غیر فعال');
                        break;
                }
                $objPHPExcel->getActiveSheet()->SetCellValue('G'.$num, $item['approve']);
                $num++;

            }
        }


        // Rename sheet
        $objPHPExcel->getActiveSheet()->setTitle('user list');
        // Save Excel 2007 file

        $objWriter = new \PHPExcel_Writer_Excel2007($objPHPExcel);

        $path_blank = str_replace(get_ev('path_live'), get_ev('path_root'), base_path('files'));
        $fileName = 'users'.Carbon::now()->format('His') . '_save.xlsx';


        $objWriter->save("{$path_blank}/{$fileName}");

        return redirect(get_ev('statics_server') . '/files/' . $fileName);


    }

    public function exportUsersDiActive(){

        $users = User::where('approve', '2')->where('status', 'active')->orderBy('created_at', 'desc')->get();

        $visit_counter = [];
        if($users){
            foreach ($users as $item){

                $visit = EventReserves::where('user_id', $item['id'])->where('status'  , 'active')->orderBy('created_at', 'desc')->count();
                $visit_counter[$item['id']] = $visit;

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
        $objPHPExcel->getActiveSheet()->SetCellValue('E1', 'تعداد ویزیت');
        $objPHPExcel->getActiveSheet()->SetCellValue('F1', 'وضعیت');

        $num = 2;

        if($users){
            foreach ($users as $item){

                if($visit_counter[$item['id']] <= 0){

                    $fullname = ($item['fullname']) ? $item['doctor_nickname'] . ' ' . $item['fullname'] : '-' ;

                    $objPHPExcel->getActiveSheet()->SetCellValue('A'.$num, $fullname);
                    $objPHPExcel->getActiveSheet()->SetCellValue('B'.$num, $item['email']);
                    $objPHPExcel->getActiveSheet()->SetCellValue('C'.$num, $item['mobile']);
                    $objPHPExcel->getActiveSheet()->SetCellValue('D'.$num, jdate('Y/m/d', strtotime($item['created_at'])));
                    $objPHPExcel->getActiveSheet()->SetCellValue('E'.$num, $visit_counter[$item['id']]);

                    switch($item['status']){
                        case "active":
                            $objPHPExcel->getActiveSheet()->SetCellValue('F'.$num, 'فعال');
                            break;
                        case "inactive":
                            $objPHPExcel->getActiveSheet()->SetCellValue('F'.$num, 'غیر فعال');
                            break;
                    }

                    $num++;

                }

            }
        }


        // Rename sheet
        $objPHPExcel->getActiveSheet()->setTitle('user list');


        // Save Excel 2007 file

        $objWriter = new \PHPExcel_Writer_Excel2007($objPHPExcel);

        $path_blank = str_replace(get_ev('path_live'), get_ev('path_root'), base_path('files'));
        $fileName = time() . '_save.xlsx';


        $objWriter->save("{$path_blank}/{$fileName}");

        return redirect(get_ev('statics_server') . '/files/' . $fileName);


    }

    public function ChangingApprove(User $user)
    {
        if ($user->UserEvents()->get()->isEmpty()) {
            $user->approve = 1;
            $user->visit_condition = json_encode(
                [
                    "my_patient_only" =>"false",
                    "consultation_type" => [
                        "videoConsultation" => "true",
                        "voiceConsultation" => "true",
                        "textConsultation" => "true"
                    ],
                ]
            );
            $user->save();
            return redirect()->back()
                ->with(['success' => 'تغییر کاربری با موفقیت انجام شد'])
                ->withInput();
        }
        return redirect()->back()
            ->with(['error' => 'تغییر کاربری امکان پذیر نیست'])
            ->withInput();
    }


}
