<?php

namespace App\Http\Controllers\Admin;

use App\Jobs\InsertComment;
use App\Model\Doctor\Specialization;
use App\Model\Partners\Partner;
use App\Model\Partners\PartnerDoctor;
use App\StarRate;
use App\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class ImportController extends Controller
{
    public function index()
    {
        $partners = Partner::get();

        return view('admin.Excel.index', ['partners' => $partners]);
    }

    public function store()
    {

        $array = [];

        // Create new PHPExcel object
        $objPHPExcel = new \PHPExcel();
        $inputFileName = \request()->file('file');
        $objReader = \PHPExcel_IOFactory::createReaderForFile($inputFileName);
        $worksheetData = $objReader->load($inputFileName);
        dd($worksheetData);

        $sheet = $worksheetData->getSheet(0);
        dd($sheet);

        $highestRow = $sheet->getHighestRow();
        $highestColumn = $sheet->getHighestColumn();

        $users = array();

        //  Loop through each row of the worksheet in turn
        for ($row = 2; $row <= $highestRow; $row++) {
            //  Read a row of data into an array
            $result = $sheet->rangeToArray('A' . $row . ':' . $highestColumn . $row, NULL, TRUE, FALSE);
            //  Insert row data array into your database of choice here
            $result = $result[0];
            $index = $row;
            if (trim(@$result[0])) {

                $users[$index]['name']= @$result[0];
                $users[$index]['family']= @$result[1];
                $users[$index]['fullname']= @$result[0].' '.@$result[1];
                $users[$index]['mobile'] = change_number(@$result[3]);
                $users[$index]['address'] = @$result[2];
                $users[$index]['specialcode'] = @$result[4];
                $users[$index]['sp_gp'] = 'شنوایی و تعادل';
                $users[$index]['bio'] = null;
                $users[$index]['username'] = 'دکتر-'.str_replace(' ','-',@$result[0]).'-'.str_replace(' ','-',@$result[1]);
                $users[$index]['token'] = str_random(10);
                $users[$index]['gender'] = @$result[10] == 'آقا' ? 0 : 1;


//                $users[$index]['specialcode'] = @$result[0];
//                $users[$index]['fullname'] = (@$result[1]) ? @$result[1] : @$result[2] . ' ' . @$result[3];
//                $users[$index]['name'] = @$result[2];
//                $users[$index]['family'] = @$result[3];
//                $users[$index]['address'] = @$result[4];
////                $users[$index]['mobile'] = (substr(@$result[5], 0, 1) == '0') ? change_number(@$result[5]) : change_number('0' . @$result[5]);
//                $users[$index]['mobile'] = change_number(@$result[5]);
//                $users[$index]['phone'] = change_number(@$result[6]);
//                $users[$index]['birthday'] = change_number(@$result[7]);
//                $users[$index]['bio'] = change_number(@$result[9]);
//                $users[$index]['sp_gp'] = change_number(@$result[10]);
//                $users[$index]['state_id'] = change_number(@$result[12]);
//                $users[$index]['city_id'] = change_number(@$result[11]);
//                $users[$index]['gender'] = change_number(@$result[13]);
//                $users[$index]['email'] = change_number(@$result[14]);
//                $users[$index]['username'] = (@$result[15]) ? @$result[15] : str_random(15);
//                $users[$index]['approve'] = 1;
//                $users[$index]['token'] = str_random(10);
//                $users[$index]['status'] = 'imported';
//                $users[$index]['doctor_status'] = 'active';
//                $users[$index]['job_title'] = (change_number(@$result[17])) ? change_number(@$result[17]) : change_number(@$result[10]);
            }
        }

        DB::beginTransaction();
        try {
            $count = 0;
            $bio = "دکتر name فوق تخصص sp می باشند. مطب دکتر name در address می باشد. شما میتوانید از طریق سامانه سلامت بدون مرز یا SBM24 در زمینه sp جهت مشاوره و دریافت ویزیت آنلاین به صورت متنی، تماس صوتی و تصویری در روزها و ساعاتی که دکتر name تعیین نموده اند، نوبت آنلاین خود را دریافت کنید. شما می توانید برای دریافت نوبت از دکتر name فوق sp وارد سامانه سلامت بدون مرز شوید و نوبت خود را ثبت نمایید و همچنین از طریق سامانه سلامت بدون مرز آدرس، شماره تلفن، بیوگرافی و نظرات بیماران دکتر name را مشاهده کنید.نوبت دهی آنلاین دکتر name فعال می باشد درصورت فعال نبودن سرویس نوبت دهی آنلاین میتوانید روی گزینه به من اطلاع بده بزنید تا هر زمان دکتر name نوبت ایجاد کردند به شما پیامک اطلاع رسانی ارسال شود .";
            /* @var User $user */
            foreach ($users as $key => $item) {
                $changed_bio = str_replace(array('name', 'sp', 'address'), array($item['fullname'], $item['sp_gp'], $item['address']), $bio);
                $item['username'] = str_replace('آ', 'ا', $item['username']);
                $item['username'] = str_replace('ئی', 'یی', $item['username']);
                if (!$item['bio'])
                    $item['bio'] = $changed_bio;
                $item['visit_condition'] = '{"my_patient_only":"false","consultation_type" :{"videoConsultation":"true","voiceConsultation":"true","textConsultation":"true"}}';
                $temp = User::where('mobile', $item['mobile'])
                    ->orWhere('specialcode', $item['specialcode'])->first();
                if (!$temp) {
                    $user = User::create($item);
                    $user->specializations()->sync(Specialization::where('name', $item['sp_gp'])->pluck('id'));

                    if (\request()->has('partner') && \request()->input('partner')) {
                        PartnerDoctor::create([
                            'user_id' => $user->id,
                            'partner_id' => \request()->input('partner')
                        ]);
                    }

                    $count = $count + 1;
                }else{
                    $temp->status='imported';
                    $temp->doctor_status='active';
                    $temp->approve=1;
                    $temp->save();
                }
            }
            DB::commit();
        } catch (\Exception $exception) {
            DB::rollBack();
            dd($exception);
        }
        return redirect()->back()->with(['success' => 'تعداد ' . $count . ' دکتر با موفقیت ثبت شد']);


    }

    public function commentIndex()
    {
        return view('admin.Excel.commentIndex');
    }

    public function commentStore()
    {
//        $comments = StarRate::select(DB::raw('COUNT(votable_id) as counts'),'votable_id')->where('flag',6)
//            ->groupBy('votable_id')->having('counts','>',20)->get();
//        foreach ($comments as $comment){
//            $limit = random_int(20,$comment->counts);
//            $delete = StarRate::where('votable_id',$comment->votable_id)
//                ->where('flag',6)->inRandomOrder()->limit($limit)->delete();
//        }
//        dd(1);
        $objPHPExcel = new \PHPExcel();
        $inputFileName = \request()->file('file');
        $objReader = \PHPExcel_IOFactory::createReaderForFile($inputFileName);
        $worksheetData = $objReader->load($inputFileName);
        $sheet = $worksheetData->getSheet(0);
        $highestRow = $sheet->getHighestRow();
        $highestColumn = $sheet->getHighestColumn();
        $comments = array();
        //  Loop through each row of the worksheet in turn
        for ($row = 2; $row <= $highestRow; $row++) {
            //  Read a row of data into an array
            $result = $sheet->rangeToArray('A' . $row . ':' . $highestColumn . $row, NULL, TRUE, FALSE);
            //  Insert row data array into your database of choice here
            $result = $result[0];
            $index = $row;
            if (trim(@$result[0])) {
                $comments[$index]['comment'] = @$result[1];
            }
        }
//        $this->dispatch(new InsertComment($comments));

        return redirect()->back()->with(['success' => 'با موفقیت انجام شد']);
    }
}
