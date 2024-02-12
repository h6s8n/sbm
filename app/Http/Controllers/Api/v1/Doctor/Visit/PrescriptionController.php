<?php

namespace App\Http\Controllers\Api\v1\Doctor\Visit;

use App\Model\Visit\EventReserves;
use App\Model\Visit\Prescription;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class PrescriptionController extends Controller
{

    protected $request;

    public function __construct(Request $request)
    {
        date_default_timezone_set("Asia/Tehran");
        $this->request = $request;

        require(base_path('app/jdf.php'));

    }

    public function fetchFiles()
    {
        $ValidData = $this->validate($this->request,[
            'audience' => 'required|numeric',
            'visit' => 'required',
        ]);


        $user = auth()->user();


        $event = EventReserves::where('token_room', $ValidData['visit'])->where('status', 'active')->first();
        if(!$event) return error_template('خطا ، شما نمیتوانید برای این ویزیت اطلاعات ارسال کنید.');

        //$request = Prescription::where('status', 'active')->where('event_id', $event->id)->whereIn('user_id', [$user->id, $ValidData['audience']])->whereIn('audience_id', [$user->id, $ValidData['audience']])->get();
        $request = Prescription::where('status', 'active')
            ->whereIn('user_id', [$user->id, $ValidData['audience']])
            ->whereIn('audience_id', [$user->id, $ValidData['audience']])
            ->get('*','type_message','type');

        $messages = [];
        if($request){
            foreach ($request as $item){

                $dateTime = Carbon::parse($item['created_at']);
                $fa_date = jdate('l - d F Y ساعت: H:i', strtotime($dateTime));

                $messages[] = [
                    'key' => $item['id'],
                    'user_id' => $item['user_id'],
                    'audience' => $item['audience_id'],
                    'message' => $item['message'],
                    'file' => $item['file'],
                    'created_at' => $fa_date,
                    'type'=>$item['type'],
                    'message_type'=>$item['message_type'],
                ];
            }
        }

        return success_template($messages);

    }

    public function AddNew(){

        $ValidData = $this->validate($this->request,[
            'file' => 'nullable',
            'text' => 'nullable',
            'audience' => 'required|numeric',
            'event' => 'required',
        ]);

        if(!$ValidData['text'] && !$ValidData['file']){
            return error_template('در حال حاضر شما هیچ چیزی به سامانه ارسال نکردید، لطفا فایل یا توضیحات را وارد کنید.');
        }

        $user = auth()->user();

        $event = EventReserves::where('token_room', $ValidData['event'])->where('status', 'active')->first();
        if(!$event) return error_template('خطا ، شما نمیتوانید برای این ویزیت اطلاعات ارسال کنید.');


        $new = new Prescription();

        if($ValidData['file']){
            $file = $this->uploadImageCt('file', 'images');
            $new->file = $file;
        }

        $new->user_id = $user->id;
        $new->audience_id = $ValidData['audience'];
        $new->event_id = $event->id;
        $new->message = $ValidData['text'];
        $new->seen_audience = 0;
        $new->save();



        $dateTime = Carbon::parse($new->created_at);
        $fa_date = jdate('Y/m/d H:i:s', strtotime($dateTime));

        return success_template(['prescription' => [
            'event' => $ValidData['event'],
            'key' => $new->id,
            'user_id' => $new->user_id,
            'audience' => $new->audience_id,
            'message' => $new->message,
            'file' => $new->file,
            'created_at' => $fa_date,
        ], 'status' => 'send']);

    }

    public function delete()
    {

        $ValidData = $this->validate($this->request,[
            'key' => 'required|numeric',
            'audience' => 'required|numeric',
            'visit' => 'required',
        ]);

        $event = EventReserves::where('token_room', $ValidData['visit'])->where('status', 'active')->first();
        if(!$event) return error_template('خطا ، شما نمیتوانید برای این ویزیت اطلاعات ارسال کنید.');


        $user = auth()->user();

        $request = Prescription::where('status', 'active')->where('user_id', $user->id)->where('id', $ValidData['key'])->first();
        if($request){
            $request->status = 'delete';
            $request->save();
        }else{
            return error_template('امکان حذف این مستند وجود ندارد.');
        }



        $request = Prescription::where('status', 'active')
            ->whereIn('user_id', [$user->id, $ValidData['audience']])
            ->whereIn('audience_id', [$user->id, $ValidData['audience']])
            ->get('*','type_message','type');

        $messages = [];
        if($request){
            foreach ($request as $item){

                $dateTime = Carbon::parse($item['created_at']);
                $fa_date = jdate('l - d F Y ساعت: H:i', strtotime($dateTime));

                $messages[] = [
                    'key' => $item['id'],
                    'user_id' => $item['user_id'],
                    'audience' => $item['audience_id'],
                    'message' => $item['message'],
                    'file' => $item['file'],
                    'created_at' => $fa_date,
                    'type'=>$item['type'],
                    'message_type'=>$item['message_type'],
                ];
            }
        }


        return success_template($messages);

    }
}
