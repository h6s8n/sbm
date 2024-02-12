<?php

namespace App\Http\Controllers\Admin\Waiting;

use App\Model\Doctor\Specialization;
use App\Model\Notification\UserDoctorNotification;
use App\SendSMS;
use App\User;
use Carbon\Carbon;
use Hekmatinasser\Verta\Facades\Verta;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;


class WaitingPeopleController extends Controller
{
    public function __construct()
    {
        require(base_path('app/jdf.php'));
    }

    public function index()
    {
        $items = UserDoctorNotification::with('doctor')->select(DB::raw('count(CASE WHEN sent_message = 0 THEN 1 END) as counts'), 'doctor_id');

        if (\request()->has('filter_user') && \request()->input('filter_user')) {
            $items = $items->whereHas('doctor', function ($query) {
                $query->where('fullname', 'LIKE',
                    '%' . \request()->input('filter_user') . '%');

            });
        } else
            $items = $items->where('sent_message', 0);

        if (\request()->has('filter_secretary') && \request()->input('filter_secretary')){
            $items = $items->whereHas('doctor',function ($query) {
                $query->whereHas('secretary', function ($query) {
                    $query->where('full_name', 'LIKE', '%' . \request()->input('filter_secretary') . '%');
                });
            });
        }

        if (\request()->has('specialization_id') && \request()->input('specialization_id')){
            $items = $items->whereHas('doctor',function ($query){
                $query->whereHas('specializations', function ($query) {
                    $query->where('id',\request()->input('specialization_id'));
                });
            });
        }

        if (\request()->has('filter_status') && \request()->input('filter_status')) {
            $items = $items->whereHas('doctor', function ($query) {
                $query->where('status',  \request()->input('filter_status') );
            });
        }

        $items = $items->whereDate('created_at','>=',Carbon::now()->subDays(30)->format('Y-m-d'));

        $items = $items->orderBy('counts', (\request()->has('filter_counts') && \request()->input('filter_counts')) ?  \request()->input('filter_counts') : 'DESC');


        $items = $items->groupBy('doctor_id')->get();

        $filter_login_history = \request()->get('filter_login_history');

        if (isset($filter_login_history)) {
            foreach ($items as $key => $item) {
                $has_login_history = $item->doctor->hasLoginHistory() ? "1" : "0";

                if ($has_login_history !== $filter_login_history) {
                    $items->pull($key);
                }
            }
        }

        $items = $items->paginate(10);

        $specializations = Specialization::all();

        return view('admin.WaitingFor.index', compact('items','specializations'));
    }

    public function details($id)
    {
        /* @var \App\User $doctor */
        $doctor = User::find($id);

        $items = $doctor->Waiting()
            ->select(DB::raw('count(user_id) as counts'),
                'doctor_id',
                'user_id',
                DB::raw('max(created_at) as created_at'),
                DB::raw('max(updated_at) as updated_at'),
                DB::raw('min(sent_message) as sent_message'))
            ->whereDate('created_at','>=',Carbon::now()->subDays(30)
                ->format('Y-m-d'))
            ->orderBy('created_at', 'DESC')
            ->groupBy('user_id');

        if (\request()->has('from') &&
            \request()->input('from') &&
            \request()->has('to') &&
            \request()->input('to')){
            $from = change_number(\request()->input('from'));
            /* @var \Hekmatinasser\Verta\Verta $from_date */
            $from = explode('/',$from);
            $from_date = Verta::create();
            $from_date->year($from[0]);
            $from_date->month($from[1]);
            $from_date->day($from[2]);
            $from_date = $from_date->formatGregorian('Y-m-d');

            $to = change_number(\request()->input('to'));
            /* @var \Hekmatinasser\Verta\Verta $to_date */
            $to = explode('/',$to);
            $to_date = Verta::create();
            $to_date->year($to[0]);
            $to_date->month($to[1]);
            $to_date->day($to[2]);
            $to_date = $to_date->formatGregorian('Y-m-d');

            $items = $items->whereDate('created_at','>=',$from_date)
                ->whereDate('created_at','<=',$to_date);

        }
        $items=$items->paginate(10);
        return view('admin.WaitingFor.details', compact('items', 'doctor'));
    }

    public function export()
    {
        $items = UserDoctorNotification::select(DB::raw('count(CASE WHEN sent_message = 0 THEN 1 END) as counts'), 'doctor_id');

        if (\request()->has('filter_user') && \request()->input('filter_user')) {
            $items = $items->whereHas('doctor', function ($query) {
                $query->where('fullname', 'LIKE',
                    '%' . \request()->input('filter_user') . '%');
            });
        } else
            $items = $items->where('sent_message', 0);

        $items=$items->whereDate('created_at','>=',Carbon::now()->subDays(30)->format('Y-m-d'));

        $items = $items->groupBy('doctor_id')
            ->orderBy('counts', 'DESC')->get();

        // Create new PHPExcel object
        $objPHPExcel = new \PHPExcel();

        // Set properties
//        $objPHPExcel->getProperties()->set
        $objPHPExcel->getProperties()->setCreator("Maarten Balliauw");
        $objPHPExcel->getProperties()->setLastModifiedBy("Maarten Balliauw");
        $objPHPExcel->getProperties()->setTitle("Office 2007 XLSX Test Document");
        $objPHPExcel->getProperties()->setSubject("Office 2007 XLSX Test Document");
        $objPHPExcel->getProperties()->setDescription("Test document for Office 2007 XLSX, generated using PHP classes.");
        // Add some data
        $objPHPExcel->setActiveSheetIndex(0);
        $objPHPExcel->getActiveSheet()->SetCellValue('A1', 'نام پزشک');
        $objPHPExcel->getActiveSheet()->SetCellValue('B1', 'تخصص');
        $objPHPExcel->getActiveSheet()->SetCellValue('C1', 'موبایل');
        $objPHPExcel->getActiveSheet()->SetCellValue('D1', 'منشی');
        $objPHPExcel->getActiveSheet()->SetCellValue('E1', 'تعداد');
        $objPHPExcel->getActiveSheet()->SetCellValue('F1', 'وضعیت');
        $num = 2;
        if ($items) {
            foreach ($items as $item) {
                $objPHPExcel->getActiveSheet()->SetCellValue('A' . $num, $item->doctor->fullname);
                $objPHPExcel->getActiveSheet()->SetCellValue('B' . $num, $item->doctor->allSpecializationsString());
                $objPHPExcel->getActiveSheet()->SetCellValue('C' . $num, $item->doctor->mobile);
                $objPHPExcel->getActiveSheet()->SetCellValue('D' . $num,$item->doctor->secretary ?
                    $item->doctor->secretary->full_name : 'وارد نشده');
                $objPHPExcel->getActiveSheet()->SetCellValue('E' . $num, (string)$item->counts);
                $objPHPExcel->getActiveSheet()->SetCellValue('F' . $num, (string)$item->doctor->status);

                $num++;
            }
        }


        // Rename sheet
        $objPHPExcel->getActiveSheet()->setTitle('Waiting Patients');


        // Save Excel 2007 file

        $objWriter = new \PHPExcel_Writer_Excel2007($objPHPExcel);

        $path_blank = str_replace(get_ev('path_live'), get_ev('path_root'), base_path('files'));
        $fileName = time() . '_save.xlsx';


        $objWriter->save("{$path_blank}/{$fileName}");
        return redirect(get_ev('statics_server') . '/files/' . $fileName);
    }

    public function DetailExport($id)
    {
       // return "Im working on it... so be patient and wait for a breathtaking feature :)";
        /* @var \App\User $doctor */
        $doctor = User::find($id);
        $items = $doctor->Waiting()
            ->select(DB::raw('count(user_id) as counts'),
                'doctor_id',
                'user_id',
                DB::raw('max(created_at) as created_at'),
                DB::raw('max(updated_at) as updated_at'),
                DB::raw('min(sent_message) as sent_message'))
            ->orderBy('sent_message', 'asc')
            ->groupBy('user_id')
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
        $objPHPExcel->getActiveSheet()->SetCellValue('A1', 'نام بیمار');
        $objPHPExcel->getActiveSheet()->SetCellValue('B1', 'موبایل بیمار');
        $objPHPExcel->getActiveSheet()->SetCellValue('C1', 'تعداد درخواست ها');
        $objPHPExcel->getActiveSheet()->SetCellValue('D1', 'وضعیت');
        $objPHPExcel->getActiveSheet()->SetCellValue('E1', 'ویزیت فعال');
        $num = 2;
        if ($items) {
            foreach ($items as $item) {
                $sent = $item->sent_message == 0 ? 'ارسال نشده' :
                    ($item->sent_message == 1 ? 'ارسال شده' : 'درخواست اشتباه');
                $active = $item->user->hasTimeWith($item->doctor_id) ? 'ویزیت فعال دارند' : 'ویزیت فعال ندارند';
                $objPHPExcel->getActiveSheet()->SetCellValue('A' . $num, $item->user->fullname);
                $objPHPExcel->getActiveSheet()->SetCellValue('B' . $num, $item->user->mobile);
                $objPHPExcel->getActiveSheet()->SetCellValue('C' . $num, (string)$item->counts);
                $objPHPExcel->getActiveSheet()->SetCellValue('D' . $num,$sent);
                $objPHPExcel->getActiveSheet()->SetCellValue('E' . $num,$active);

                $num++;
            }
        }

        // Rename sheet
        $objPHPExcel->getActiveSheet()->setTitle('Detail of '.$doctor->fullname);


        // Save Excel 2007 file

        $objWriter = new \PHPExcel_Writer_Excel2007($objPHPExcel);

        $path_blank = str_replace(get_ev('path_live'), get_ev('path_root'), base_path('files'));
        $fileName = time() . '_save.xlsx';


        $objWriter->save("{$path_blank}/{$fileName}");
        return redirect(get_ev('statics_server') . '/files/' . $fileName);


    }

    public function SpecializationDetailExport()
    {
       // return "Im working on it... so be patient and wait for a breathtaking feature :)";
        /* @var \App\User $doctor */
//        $doctor = User::find($id);
//
//        $items = $doctor->Waiting()
//            ->select(DB::raw('count(user_id) as counts'),
//                'doctor_id',
//                'user_id',
//                DB::raw('max(created_at) as created_at'),
//                DB::raw('max(updated_at) as updated_at'),
//                DB::raw('min(sent_message) as sent_message'))
//            ->orderBy('sent_message', 'asc')
//            ->groupBy('user_id')
//            ->get();

        if (\request()->input('specialization_id') == null) {
            return redirect()->back()->with(['error'=>'انتخاب گروه پزشکی الزامی است']);
        }
        $items = UserDoctorNotification::with('doctor')->select(DB::raw('count(user_id) as counts'),
                 'doctor_id',
                  'user_id',
                 DB::raw('max(created_at) as created_at'),
                 DB::raw('max(updated_at) as updated_at'),
                 DB::raw('min(sent_message) as sent_message'))
            ->orderBy('sent_message', 'asc')
            ->groupBy('user_id');

        $items = $items->whereHas('doctor',function ($query){
            $query->whereHas('specializations', function ($query) {
                $query->where('id',\request()->input('specialization_id'));
            });
        });

        $items = $items->get();

        $specialization = Specialization::find(\request()->input('specialization_id'));
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
        $objPHPExcel->getActiveSheet()->SetCellValue('A1', 'نام بیمار');
        $objPHPExcel->getActiveSheet()->SetCellValue('B1', 'موبایل بیمار');
        $objPHPExcel->getActiveSheet()->SetCellValue('C1', 'تعداد درخواست ها');
        $objPHPExcel->getActiveSheet()->SetCellValue('D1', 'وضعیت');
        $objPHPExcel->getActiveSheet()->SetCellValue('E1', 'ویزیت فعال');
        $num = 2;
        if ($items) {
            foreach ($items as $item) {
                $sent = $item->sent_message == 0 ? 'ارسال نشده' :
                    ($item->sent_message == 1 ? 'ارسال شده' : 'درخواست اشتباه');
                $active = $item->user->hasTimeWith($item->doctor_id) ? 'ویزیت فعال دارند' : 'ویزیت فعال ندارند';
                $objPHPExcel->getActiveSheet()->SetCellValue('A' . $num, $item->user->fullname);
                $objPHPExcel->getActiveSheet()->SetCellValue('B' . $num, $item->user->mobile);
                $objPHPExcel->getActiveSheet()->SetCellValue('C' . $num, (string)$item->counts);
                $objPHPExcel->getActiveSheet()->SetCellValue('D' . $num,$sent);
                $objPHPExcel->getActiveSheet()->SetCellValue('E' . $num,$active);

                $num++;
            }
        }

        // Rename sheet
        $objPHPExcel->getActiveSheet()->setTitle('Detail of '.$specialization->slug);


        // Save Excel 2007 file

        $objWriter = new \PHPExcel_Writer_Excel2007($objPHPExcel);

        $path_blank = str_replace(get_ev('path_live'), get_ev('path_root'), base_path('files'));
        $fileName = time() . '_save.xlsx';


        $objWriter->save("{$path_blank}/{$fileName}");
        return redirect(get_ev('statics_server') . '/files/' . $fileName);


    }

    public function ManualSendSms(User $doctor)
    {
        $data = UserDoctorNotification::where('doctor_id',$doctor->id)
            ->whereDate('created_at','>=',Carbon::now()->subDays(30)
                ->format('Y-m-d'))
            ->where('sent_message',0)->get();

        foreach ($data as $record) {
            $user = $record->user()->firstOrFail();
            if ($user) {
                if (!$user->hasTimeWith($record->doctor_id)) {
                       SendSMS::send($user->mobile, "ManualNewTime", [
                            "token" =>  $user->fullname==" " ? 'کاربر' : $user->fullname,
                            "token2" => $doctor->fullname,
                            "token3" => $doctor->username,
                        ]);

                    $record->sent_message = 1;
                } else
                    $record->sent_message = 2;
                $record->save();
            }
        }
        return redirect()->back()->with(['success'=>'با موفقیت انجام شد']);
    }
}
