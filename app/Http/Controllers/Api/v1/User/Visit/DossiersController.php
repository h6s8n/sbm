<?php

namespace App\Http\Controllers\Api\v1\User\Visit;

use App\Events\MessageSent;
use App\Model\Visit\Dossiers;
use App\Model\Visit\EventReserves;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class DossiersController extends Controller
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
        return error_template('Disabled');
        $ValidData = $this->validate($this->request,[
            'audience' => 'required|numeric',
            'visit' => 'required',
        ]);


        $user = auth()->user();


        $event = EventReserves::where('token_room', $ValidData['visit'])->where('status', 'active')->first();
        if(!$event) return error_template('خطا ، شما نمیتوانید برای این ویزیت اطلاعات ارسال کنید.');

        //$request = Dossiers::where('status', 'active')->where('event_id', $event->id)->whereIn('user_id', [$user->id, $ValidData['audience']])->whereIn('audience_id', [$user->id, $ValidData['audience']])->get();
        $request = Dossiers::where('status', 'active')
            ->where('user_id', $user->id)
            ->where('audience_id', $ValidData['audience'])->get();

        $messages = [];
        if($request){
            foreach ($request as $item){

                $dateTime = Carbon::parse($item['created_at']);
                $fa_date = jdate('l - d F Y ساعت: H:i', strtotime($dateTime));

                $seen = ($item['seen_audience']) ? true : false;

                if($item['audience_id'] == $user->id && !$seen){
                    $item->seen_audience = 1;
                    $item->save();
                    $seen = true;
                }

                $messages[] = [
                    'key' => $item['id'],
                    'user_id' => $item['user_id'],
                    'audience' => $item['audience_id'],
                    'message' => $item['message'],
                    'file' => $item['file'],
                    'seen' => $seen,
                    'created_at' => $fa_date,
                    'type_message'=>'dossiers',
                    'type'=> $item['file'] ? 'file' : 'text'
                ];

            }
        }

        return success_template($messages);

    }

    public function AddNew(){

        $ValidData = $this->validate($this->request,[
            'file0' => 'nullable',
            'text' => 'nullable',
            'audience' => 'required|numeric',
            'event' => 'required',
        ]);

        if(!$ValidData['text'] && !$ValidData['file0']){
            return error_template('در حال حاضر شما هیچ چیزی به سامانه ارسال نکردید، لطفا فایل یا توضیحات را وارد کنید.');
        }

        $user = auth()->user();

        $event = EventReserves::where('token_room', $ValidData['event'])->where('status', 'active')->first();
        if(!$event) return error_template('خطا ، شما نمیتوانید برای این ویزیت اطلاعات ارسال کنید.');
        $j = \request()->input('length');
        if ($j>0) {
            for ($i = 0; $i < $j; $i++) {
                $new = new Dossiers();
              //  $file="";
                if (\request()->hasFile('file' . $i)) {
                    $file = $this->uploadImageCt('file' . $i);
                    $new->file = $file;
                }
                $new->file = $file;
                $new->user_id = $user->id;
                $new->audience_id = $ValidData['audience'];
                $new->event_id = $event->id;
                $new->message = $ValidData['text'];
                $new->save();
                $dateTime = Carbon::parse($new->created_at);
                $fa_date = jdate('Y/m/d H:i:s', strtotime($dateTime));
                $dossier[$i] = [
                    'event' => $ValidData['event'],
                    'key' => $new->id,
                    'user_id' => $user->id,
                    'audience' => $ValidData['audience'],
                    'message' => $ValidData['text'],
                    'file' => $new->file,
                    'seen' => false,
                    'created_at' => $fa_date,
                    'type_message'=>'dossiers',
                    'type'=> $new->file ? 'file' : 'text'
                ];
            }
        }
        else{
            $new = new Dossiers();
            $new->user_id = $user->id;
            $new->audience_id = $ValidData['audience'];
            $new->event_id = $event->id;
            $new->message = $ValidData['text'];
            $new->save();
            $dateTime = Carbon::parse($new->created_at);
            $fa_date = jdate('Y/m/d H:i:s', strtotime($dateTime));
            $dossier[] = [
                'event' => $ValidData['event'],
                'key' => $new->id,
                'user_id' => $user->id,
                'audience' => $ValidData['audience'],
                'message' => $ValidData['text'],
                'file' => $new->file,
                'seen' => false,
                'created_at' => $fa_date,
                'type_message'=>'dossiers',
                'type'=> $new->file ? 'file' : 'text'
            ];
        }
        $audience = User::find($ValidData['audience']);
        if ($user->approve != 1){
        broadcast(new  MessageSent($event->token_room,$dossier))->toOthers();
        }

        return success_template(
            ['dossier' => $dossier,
                'status' => 'send']
        );

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

        $request = Dossiers::where('status', 'active')->where('user_id', $user->id)->where('id', $ValidData['key'])->first();
        if($request){
            $request->status = 'delete';
            $request->save();
        }else{
            return error_template('امکان حذف این مستند وجود ندارد.');
        }
        if ($user->approve == 1) {
            $request = Dossiers::where('status', 'active')
                ->where('user_id', $user->id)
                ->where('audience_id',$ValidData['audience'])
                ->get();
        }else
        {
            $request = null;
        }

        $messages = [];
        if($request){
            foreach ($request as $item){

                $dateTime = Carbon::parse($item['created_at']);
                $fa_date = jdate('Y/m/d H:i:s', strtotime($dateTime));


                $seen = ($item['seen_audience']) ? true : false;

                if($item['audience_id'] == $user->id && !$seen){
                    $item->seen_audience = 1;
                    $item->save();
                    $seen = true;
                }

                $messages[] = [
                    'key' => $item['id'],
                    'user_id' => $item['user_id'],
                    'audience' => $item['audience_id'],
                    'message' => $item['message'],
                    'file' => $item['file'],
                    'seen' => $seen,
                    'created_at' => $fa_date,
                ];
            }
        }

        return success_template($messages);

    }



}
