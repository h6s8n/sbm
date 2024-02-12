<?php

namespace App\Http\Controllers\Api\v2\Message;

use App\Enums\VisitLogEnum;
use App\Events\MessageSent;
use App\Events\SeenMessage;
use App\Model\Visit\Dossiers;
use App\Model\Visit\EventReserves;
use App\Model\Visit\Message;
use App\Model\Visit\Prescription;
use App\Notifications\NewMessage;
use App\Notifications\UserNotification;
use App\Repositories\v2\Visit\VisitLogInterface;
use App\SendSMS;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Pusher\Pusher;
use Pusher\PusherException;
use Twilio\Jwt\AccessToken;
use Twilio\Jwt\Grants\VideoGrant;

class MessageController extends Controller
{
    protected $request;

    private $log;

    public function __construct(Request $request, VisitLogInterface $visitLog)
    {
        date_default_timezone_set("Asia/Tehran");
        $this->request = $request;
        $this->log = $visitLog;
        require(base_path('app/jdf.php'));

    }

    public function Authenticate(Request $request)
    {

        $socketId = $request->socket_id;
        $channelName = $request->channel_name;
        try {
            $pusher = new Pusher(
                '369ebf78f3bf55623472',
                'e54f4d5a719c604cf267',
                '840652',
                [
                    'cluster' => 'ap2',
                    'encrypted' => true
                ]
            );
            $presence_data = ['name' => auth()->user()->fullname];
            $key = $pusher->presence_auth($channelName, $socketId, auth()->id(), $presence_data);
            return response($key);

        } catch (PusherException $e) {

            return $e;
        }

    }

    public function fetchMessages()
    {

        $ValidData = $this->validate($this->request, [
            'audience' => 'required|numeric',
        ]);
        $tone = false;
        /* @var EventReserves $event */
        $event = EventReserves::where('token_room', \request()->input('token'))->first();
        if ($event) {
            $date = Carbon::parse($event->data);
            if (Carbon::now() >= $date)
                $tone = true;
        }

        $user = auth()->user();

        /* @var User $notif */
        $notif = User::find(request()->input('audience'));
        if ($event->visit_status == 'not_end') {
            if ($notif->approve == 2) {
                $message = 'دکتر ' . $user->fullname . ' در اتاق ویزیت منتظر شماست';
                $this->log->createLog($event, auth()->id(), VisitLogEnum::DoctorEnter);
            } else {
                $message = 'بیمار شما ' . $user->fullname . ' در اتاق ویزیت منتظر شماست';
                $this->log->createLog($event, auth()->id(), VisitLogEnum::PatientEnter);
            }
            try {
                $notif->notify(new UserNotification($message, $tone));
            } catch (PusherException $exception) {
            }
        }

        $request = Message::where('status', 'active')->whereIn('user_id', [$user->id, $ValidData['audience']])
            ->whereIn('audience_id', [$user->id, $ValidData['audience']])
            ->orderBy('created_at', 'asc')->get();

        $dossiers = Dossiers::where('user_id', $event->user_id)
            ->where('audience_id', $ValidData['audience'])
            ->orderBy('created_at', 'asc')->get('*', 'type_message', 'type');

        $prescriptions = Prescription::where('status', 'active')
            ->whereIn('user_id', [$user->id, $ValidData['audience']])
            ->whereIn('audience_id', [$user->id, $ValidData['audience']])
            ->get('*', 'type_message', 'type');

        $request = $request->merge($dossiers);
        $request = $request->merge($prescriptions);
        $request = $request->sortBy('created_at');
        $messages = [];
        if ($request) {
            foreach ($request as $item) {

                $dateTime = Carbon::parse($item['created_at']);
                $fa_date = jdate('Y/m/d H:i:s', strtotime($dateTime));

                $seen = ($item['seen_audience']) ? true : false;

                if ($item['audience_id'] == $user->id) {
                    $item->seen_audience = 1;
                    $item->save();
                    $seen = true;
                }

                $messages[] = [
                    'key' => $item['id'],
                    'user_id' => $item['user_id'],
                    'audience' => $item['audience_id'],
                    'message' => $item['message'],
                    'type' => $item['type'],
                    'file' => $item['file'],
                    'seen' => $seen,
                    'reply_to' => $item['reply_to'],
                    'type_message' => $item['type_message'] ? $item['type_message'] : 'message',
                    'created_at' => $fa_date,
                ];
            }
        }
//        $messages['medical_records']=$event->MedicalRecords()->get();
//        $messages['medical_instructions']=$event->MedicalInstructions()->get();
        return success_template($messages);

    }

    public function auth()
    {
        $user = auth()->user();

        return $user;

    }

    public function send(Request $request)
    {
        $user = auth()->user();

        $ValidData = $this->validate($this->request, [
            'audience' => 'required|numeric',
            'message' => 'nullable',
            'type' => 'nullable',
            'file0' => 'nullable|mimes:jpeg,png,jpg,gif,svg,mp4,mp4v,mpg4,mpeg,mpg,mpe,m1v,m2v,ogv,mkv,wmv,movie,avi,mpga,mp2,mp2a,mp3,m2a,m3a,pdf,mp4,blob|max:16360',
            'room_token' => 'nullable',
            'reply_to' => 'nullable|integer'
        ],[
            'file0.mimes'=>'لطفا فرمت فایل را چک کنید',
            'file0.max' => 'حجم هر فایل نباید بیشتر از ۸ مگابایت باشد'
        ]);

        /* @var User $audience */
        $audience = User::find($ValidData['audience']);

        $j = \request()->input('length');
        if ($j > 0) {
            for ($i = 0; $i < $j; $i++) {
                if (\request()->hasFile('file' . $i)) {
                    \request()->validate([
                        'file'.$i =>'mimes:jpeg,png,jpg,gif,svg,mp4,mp4v,mpg4,mpeg,mpg,mpe,m1v,m2v,ogv,mkv,wmv,movie,avi,mpga,mp2,mp2a,mp3,m2a,m3a,pdf,mp4,blob|max:16360',
                    ],
                    [
                        'file'.$i.'.mimes'=>'لطفا فرمت فایل را چک کنید',
                        'file'.$i.'.max' => 'حجم هر فایل نباید بیشتر از ۸ مگابایت باشد'
                    ]);
                }
            }
        }
        if ($j > 0) {
            for ($i = 0; $i < $j; $i++) {
                $newMessage = new Message();
                $newMessage->file = null;
                if (\request()->hasFile('file' . $i)) {
                    $file = $this->uploadImageCt('file' . $i);
                    $newMessage->file = $file;
                }
                $newMessage->user_id = $user->id;
                $newMessage->audience_id = $ValidData['audience'];
                $newMessage->room_token = (isset($ValidData['room_token'])) ? $ValidData['room_token'] : null;
                if ($j == $i + 1) {
                    $newMessage->message = (isset($ValidData['message'])) ? $ValidData['message'] : ' ';
                }
                $newMessage->type = (isset($ValidData['type'])) ? $ValidData['type'] : 'text';
                $newMessage->reply_to = ($request->has('reply_to') && $request->input('reply_to'))
                    ? $request->input('reply_to') : null;
                $newMessage->save();

                $dateTime = Carbon::parse($newMessage->created_at);
                $fa_date = jdate('Y/m/d H:i:s', strtotime($dateTime));

                $messages[$i] = [
                    'key' => $newMessage->id,
                    'user_id' => $newMessage->user_id,
                    'audience' => $newMessage->audience_id,
                    'message' => $newMessage->message,
                    'type' => $newMessage->type,
                    'file' => $newMessage->file,
                    'reply_to' => $newMessage->reply_to,
                    'seen' => false,
                    'created_at' => $fa_date,
                ];

                try {
                    broadcast(new MessageSent($newMessage->room_token, $newMessage))->toOthers();
                    $audience->notify(new NewMessage($user));
                } catch (\Exception $exception) {
                    return $exception;
                }

            }
        } else {
            $newMessage = new Message();
            $newMessage->file = null;

            $newMessage->user_id = $user->id;
            $newMessage->audience_id = $ValidData['audience'];
            $newMessage->room_token = (isset($ValidData['room_token'])) ? $ValidData['room_token'] : null;
            $newMessage->message = (isset($ValidData['message'])) ? $ValidData['message'] : ' ';
            $newMessage->type = (isset($ValidData['type'])) ? $ValidData['type'] : 'text';
            $newMessage->reply_to = ($request->has('reply_to') && $request->input('reply_to'))
                ? $request->input('reply_to') : null;
            $newMessage->save();

            try {
                broadcast(new MessageSent($newMessage->room_token, $newMessage))->toOthers();
                $audience->notify(new NewMessage($user));
            } catch (\Exception $exception) {
                // return $exception;
            }

            $dateTime = Carbon::parse($newMessage->created_at);
            $fa_date = jdate('Y/m/d H:i:s', strtotime($dateTime));

            return success_template(['message' => [
                'key' => $newMessage->id,
                'user_id' => $newMessage->user_id,
                'audience' => $newMessage->audience_id,
                'message' => $newMessage->message,
                'type' => $newMessage->type,
                'file' => $newMessage->file,
                'reply_to' => $newMessage->reply_to,
                'seen' => false,
                'created_at' => $fa_date,
            ], 'status' => 'send']);

        }
        return success_template(['message' =>
            $messages
            , 'status' => 'send']);

    }

    public function seenMessage(Request $request)
    {
        $user = auth()->user();

        $ValidData = $this->validate($this->request, [
            'key' => 'required|numeric',
            'audience' => 'required|numeric',
        ]);

        $request = Message::where('status', 'active')->where('id', $ValidData['key'])->first();

        if ($request) {

            $request->seen_audience = 1;
            $request->save();
            try {
                broadcast(new SeenMessage($request, $request->room_token))->toOthers();
            } catch (\Exception $exception) {

            }
            $request = Message::where('status', 'active')->whereIn('user_id', [$user->id, $ValidData['audience']])->whereIn('audience_id', [$user->id, $ValidData['audience']])
                ->get();

            $seen_id = [];
            $messages = [];
            if ($request) {
                foreach ($request as $item) {

                    $dateTime = Carbon::parse($item['created_at']);
                    $fa_date = jdate('Y/m/d H:i:s', strtotime($dateTime));

                    $seen = ($item['seen_audience']) ? true : false;

                    $messages[] = [
                        'key' => $item['id'],
                        'user_id' => $item['user_id'],
                        'audience' => $item['audience_id'],
                        'message' => $item['message'],
                        'type' => $item['type'],
                        'file' => $item['file'],
                        'seen' => $seen,
                        'created_at' => $fa_date,
                    ];
                    $seen_id[] = [
                        'id' => $item['id']
                    ];
                }
            }
            return success_template(['message' => $messages, 'status' => 'seen']);

        }

        return success_template([]);

    }

    public function destroy(Message $message)
    {
        if ($message->user_id != auth()->user()->id)
            return error_template('You dont have permission');
        else {
            try {
                $message->delete();
                return success_template([
                    'message' => 'پیام با موفقیت حذف شد'
                ]);
            } catch (\Exception $exception) {
                return error_template($exception->getMessage());
            }
        }
    }

    public function webauth()
    {

        $user = auth()->user();

        return success_template($user);

    }

    public function OpenRoomDoctor()
    {

        $doctor = auth()->user();

        $ValidData = $this->validate($this->request, [
            'token' => 'required',
        ]);

        $EventList = EventReserves::join('users', 'users.id', '=', 'event_reserves.doctor_id')
            ->where('event_reserves.doctor_id', $doctor->id)
            ->where('event_reserves.token_room', $ValidData['token'])
            ->where('event_reserves.status', 'active')
            ->first();

        if ($EventList) {
            /* @var User $user */
            $user = User::where('id', $EventList->user_id)->first();

            if ($user) {
                if ($user->mobile) {
                    SendSMS::sendTemplateTwo($user->mobile, $user->name, $EventList->fa_data, 'DoctorInRoom');
                }

            }


        }

        return success_template(['send' => true]);


    }

    public function OpenRoomUser()
    {

        $user = auth()->user();

        $ValidData = $this->validate($this->request, [
            'token' => 'required',
        ]);

        $EventList = EventReserves::join('users', 'users.id', '=', 'event_reserves.doctor_id')
            ->where('event_reserves.user_id', $user->id)
            ->where('event_reserves.token_room', $ValidData['token'])
            ->where('event_reserves.status', 'active')
            ->first();

        if ($EventList) {

            $doctor = User::where('id', $EventList->doctor_id)->first();

            if ($doctor) {

                if ($doctor->mobile) {
                    $params = array(
                        "token" => $doctor->family,
                        "token2" => $user->fullname,
                        "token3" => $EventList->fa_data .' - '.$EventList->time .':00'
                    );
//                    SendSMS::sendTemplateTwo($doctor->mobile, $doctor->family, $EventList->fa_data, 'UserInRoom');
                    SendSMS::send($doctor->mobile, 'UserInRoom',$params);
                }

            }


        }

        return success_template(['send' => true]);


    }

    public function initial_()
    {
//        if (auth()->id() == 170790){
//            dd($this->request);
//        }
        \request()->validate([
            'type' => 'required',
            'text' => 'required',
            'name' => 'required',
            'birthday' => 'nullable',
           // 'national_code' => 'required|digits:10',
            'cellphone' => 'required',
            'family' => 'required',
            'token_room' => 'required',
        ], [
                'national_code.digits' => 'کدملی را لاتین و با فرمت صحیح وارد کنید',
                'text.required' => 'شرح حال بیماری الزامی است',
                'national_code.required' => 'کدملی الزامی است',
                'name.required' => 'نام الزامی است',
                'family.required' => ' نام خانوادگی الزامی است',
                'cellphone.required' => 'شماره همراه الزامی است',
                'birthday.required' => 'تاریخ تولد الزامی است',
        ]);


        $user = auth()->user();


//        if (\request()->has('national_code'))
        $user->nationalcode = change_number(\request()->get('national_code')) ?? $user->nationalcode;

        $user->name = \request()->input('name');
        $user->birthday = \request()->input('birthday');
        $user->family = \request()->input('family');
        $user->fullname = \request()->input('name') .' '. \request()->input('family');
        $user->save();

        $event = EventReserves::where('token_room',
            \request()->input('token_room'))
            ->where('user_id', $user->id)->first();

        if (\request()->hasFile('file') || \request()->file('file')) {
            $new = new Message();
            $file = $this->uploadImageCt('file');
            $new->file = $file;
            $new->user_id = $user->id;
            $new->audience_id = $event->doctor_id;
            $new->room_token = \request()->input('token_room');
            $new->type = 'dossierFile';
            $new->save();
        }
        if (\request()->has('text')) {
            $new_text = new Message();
            $new_text->user_id = $user->id;
            $new_text->audience_id = $event->doctor_id;
            $new_text->type = $this->request->input('type') ?: 'dossierText';
            $new_text->message = $this->request->input('text');
            $new_text->room_token = \request()->input('token_room');
            $new_text->save();

            $new_text_ = new Message();
            $new_text_->user_id = $user->id;
            $new_text_->audience_id = $event->doctor_id;
            $new_text_->type = 'text';
            $new_text_->message = $this->request->input('text');
            $new_text_->room_token = \request()->input('token_room');
            $new_text_->save();
        }

        if ($this->request->has('cellphone') &&
            $this->request->input('cellphone')) {
            $event->safe_call_mobile = change_number($this->request->input('cellphone'));
            $event->save();
        }
        return success_template(['send' => true]);
    }

    public function initial_2()
    {

        \request()->validate([
            'type' => 'required',
            'text' => 'required',
            'name' => 'required',
            'birthday' => 'required',
            'national_code' => 'required|digits:10',
            'cellphone' => 'nullable',
            'family' => 'required',
            'token_room' => 'required',
        ], [
                'national_code.digits' => 'کدملی نامعتبر',
                'text.required' => 'شرح حال بیماری الزامی است',
                'national_code.required' => 'کدملی الزامی است',
                'name.required' => 'نام الزامی است',
                'family.required' => ' نام خانوادگی الزامی است',
                'cellphone.required' => 'شماره همراه الزامی است',
                'birthday.required' => 'تاریخ تولد الزامی است',
        ]);


        $user = auth()->user();


//        if (\request()->has('national_code'))

        $user->nationalcode = change_number(\request()->get('national_code') ?? $user->nationalcode) ;

        $user->name = \request()->input('name');
        $user->birthday = \request()->input('birthday');
        $user->family = \request()->input('family');
        $user->fullname = \request()->input('name') .' '. \request()->input('family');
        $user->save();

        $event = EventReserves::where('token_room',
            \request()->input('token_room'))
            ->where('user_id', $user->id)->first();

        if (!$event){
            return error_template('ویزیت یافت نشد');
        }

        if (\request()->hasFile('file') || \request()->file('file')) {
            $new = new Message();
            $file = $this->uploadImageCt('file');
            $new->file = $file;
            $new->user_id = $user->id;
            $new->audience_id = $event->doctor_id;
            $new->room_token = \request()->input('token_room');
            $new->type = 'dossierFile';
            $new->save();
        }
        if (\request()->has('text')) {
            $new_text = new Message();
            $new_text->user_id = $user->id;
            $new_text->audience_id = $event->doctor_id;
            $new_text->type = $this->request->input('type') ?: 'dossierText';
            $new_text->message = $this->request->input('text');
            $new_text->room_token = \request()->input('token_room');
            $new_text->save();

            $new_text_ = new Message();
            $new_text_->user_id = $user->id;
            $new_text_->audience_id = $event->doctor_id;
            $new_text_->type = 'text';
            $new_text_->message = $this->request->input('text');
            $new_text_->room_token = \request()->input('token_room');
            $new_text_->save();
        }

        if ($this->request->has('cellphone') &&
            $this->request->input('cellphone')) {
            $event->safe_call_mobile = change_number($this->request->input('cellphone'));
            $event->save();
        }
        return success_template(['send' => true]);
    }

    public function get_initial()
    {

        \request()->validate(['token_room'=>'required']);

        $user = auth()->user();

        $event = EventReserves::where('token_room',
            \request()->input('token_room'))
            ->where('user_id', $user->id)->first();

        if (!$event)
            return error_template('ویزیت یافت نشد');

        $name = trim($user->name);
        $family = trim($user->family);

        $message = Message::where('user_id',$user->id)
            ->where('audience_id',$event->doctor_id)
            ->first();

        return success_template([
            'name'=> $name ?: "",
            'family'=> $family ?: "",
            'national_code'=> $user->nationalcode ?: "",
            'birthday'=> $user->birthday ?: "",
            'cellphone'=>$event->safe_call_mobile ?: $user->mobile,
            'message'=> $message ? true : false
        ]);

    }
}
