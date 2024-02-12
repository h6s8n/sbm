<?php

namespace App\Http\Controllers\Api\v1\Pusher;

use App\Enums\VisitLogEnum;
use App\Events\MessageSent;
use App\Events\SeenMessage;
use App\Model\Visit\EventReserves;
use App\Model\Visit\Message;
use App\Notifications\NewMessage;
use App\Notifications\UserNotification;
use App\Repositories\v2\Visit\VisitLogInterface;
use App\SendSMS;
use App\User;
use Carbon\Carbon;
use Illuminate\Broadcasting\BroadcastException;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;
use Mockery\Exception;
use Pusher\Pusher;
use Pusher\PusherException;
use Twilio\Exceptions\ConfigurationException;
use Twilio\Jwt\AccessToken;
use Twilio\Jwt\Grants\VideoGrant;
use Twilio\Rest\Client;

class PusherController extends Controller
{
    protected $request;

    private $log;

    public function __construct(Request $request,VisitLogInterface  $visitLog)
    {
        date_default_timezone_set("Asia/Tehran");
        $this->request = $request;
        $this->log=$visitLog;
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
            
            return $e . "this is your erro";
        }

    }

    public function TwilioTools()
    {

        $accountSid = 'AC0390cee992f977d3c7dfe94bb11a288f';
        $apiKeySid = 'SK6548935c33a44178c4fd70e9e4866b9a';
        $apiKeySecret = '615dN0yPIw2B0Ij5W2B03CgzCwx8OqZp';

        $identity = str_random(10);

        // Create an Access Token
        $token = new AccessToken(
            $accountSid,
            $apiKeySid,
            $apiKeySecret,
            3600,
            $identity
        );

        // Grant access to Video
        $grant = new VideoGrant();
        //$grant->setRoom('sbm room ' . auth()->id());
        $token->addGrant($grant);

        // Serialize the token as a JWT
        return success_template([
            'identity' => $identity,
            'token' => $token->toJWT()
        ]);

    }

    public function fetchMessages()
    {

        $ValidData = $this->validate($this->request, [
            'audience' => 'required|numeric',
        ]);
        $tone = false;
        /* @var EventReserves $event */
        $event = EventReserves::where('token_room',\request()->input('token'))->first();
        if ($event)
        {
            $date = Carbon::parse($event->data);
            if (Carbon::now() >= $date)
                $tone = true;
        }

        $user = auth()->user();

        $request = Message::where('status', 'active')->whereIn('user_id', [$user->id, $ValidData['audience']])
            ->whereIn('audience_id', [$user->id, $ValidData['audience']])
            ->orderBy('created_at', 'asc')->get();


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

            $maxRetries = 2;
            $retryCount = 0;
            while ($retryCount < $maxRetries) {
                try {
                    $notif->notify(new UserNotification($message, $tone));                    break;
                } catch (PusherException $exception) {
                    return success_template($messages);
                } catch (BroadcastException $exception) {
                    Log::error('🔴 connection to socket server failed. trying to call restart endpoint' .
                        "\n\n message: " .
                        $exception->getMessage() .
                        "\n\n trace: " .
                        $exception->getTraceAsString()
                    );

                    $http = new \GuzzleHttp\Client();
                    // @todo: an authentication approach is needed to be considered
                    $response = $http->get('https://push.sbm24.com/api/services/repair');
//                    json_decode((string)$response->getBody(), true);

                    $retryCount++;
                }
            }
        }



//            $dossiers = Dossiers::where('user_id', $event->user_id)
//                ->where('status','active')
//                ->where('audience_id',$ValidData['audience'])
//                ->orderBy('created_at', 'asc')->get('*', 'type_message', 'type');

//        $prescriptions = Prescription::where('status', 'active')
//            ->whereIn('user_id', [$user->id, $ValidData['audience']])
//            ->whereIn('audience_id', [$user->id, $ValidData['audience']])
//            ->get('*','type_message','type');
//
//            $request = $request->merge($dossiers);
//            $request = $request->merge($prescriptions);
//            $request = $request->sortBy('created_at');

//        $messages['medical_records']=$event->MedicalRecords()->get();
//        $messages['medical_instructions']=$event->MedicalInstructions()->get();
        return success_template($messages);

    }

    public function auth()
    {
        $user = auth()->user();

        return $user;

    }

    public function sendMessage(Request $request)
    {
        $user = auth()->user();


        $ValidData = $this->validate($this->request, [
            'audience' => 'required|numeric',
            'message' => 'nullable',
            'type' => 'nullable',
            'file' => 'nullable',
//                |mimes:jpeg,png,jpg,gif,svg,mp4,mp4v,mpg4,mpeg,mpg,mpe,m1v,m2v,ogv,mkv,wmv,movie,avi,mpga,mp2,mp2a,mp3,m2a,m3a,pdf,mp4,blob',
            'room_token' => 'nullable',
            'reply_to' => 'nullable|integer'
        ],[
            'file.mimes'=>'لطفا فرمت فایل ارسالی را چک کنید'
        ]);

//
//        if (auth()->id() == 3334){
//            return error_template($this->request->file('file')->getClientOriginalExtension());
//        }
        $newMessage = new Message();
        $newMessage->file = null;
        if (isset($ValidData['file'])) {
            $ValidDataImage = $this->validate($this->request, [
                'file' => 'required',
            ]);

            $file = $this->uploadImageCt('file');

            $newMessage->file = $file;

        }

        $newMessage->user_id = $user->id;
        $newMessage->audience_id = $ValidData['audience'];
        $newMessage->room_token = (isset($ValidData['room_token'])) ? $ValidData['room_token'] : null;
        $newMessage->message = (isset($ValidData['message'])) ? $ValidData['message'] : ' ';
        $newMessage->type = (isset($ValidData['type'])) ? $ValidData['type'] : 'text';
        $newMessage->reply_to = ($request->has('reply_to') && $request->input('reply_to'))
            ? $request->input('reply_to') : null;
        $newMessage->save();

        /* @var User $audience */
        $audience = User::find($newMessage->audience_id);

        $dateTime = Carbon::parse($newMessage->created_at);
        $fa_date = jdate('Y/m/d H:i:s', strtotime($dateTime));

        $response = [
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

        return success_template(['message' => $response, 'status' => 'send']);

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

            $items = Message::where('status', 'active')->whereIn('user_id', [$user->id, $ValidData['audience']])->whereIn('audience_id', [$user->id, $ValidData['audience']])
                ->get();

            $seen_id=[];
            $messages = [];
            if ($items) {
                foreach ($items as $item) {

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
                    $seen_id[]=[
                        'id'=>$item['id']
                    ];
                }
            }

            try {
                broadcast(new SeenMessage($request,$request->room_token))->toOthers();
            }catch (\Exception $exception){
                return success_template(['message' => $messages, 'status' => 'seen']);
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
        $EventList = EventReserves::where('event_reserves.user_id', $user->id)
            ->where('event_reserves.token_room', $ValidData['token'])
            ->where('event_reserves.status', 'active')
            ->first();
        if ($EventList) {
            $now = date('Y-m-d H:i');
            $visit = date('Y-m-d H:i', strtotime($EventList->reserve_time));
            if ($now >= $visit) {
                $doctor = User::where('id', $EventList->doctor_id)->first();
                $denyToCall = $doctor->settings()->where('setting_type_id',28)->where('subscribed',0)->first();
            if ($doctor) {
                    if ($doctor->mobile && is_null($denyToCall)) {
                        $params = array(
                            "token" => $doctor->family,
                            "token2" => $user->fullname,
                            "type" => 'call',
                            "token3" => $EventList->fa_data .' - '.$EventList->time .':00'
                        );
                        SendSMS::send($doctor->mobile, 'UserInRoom',$params);
                        return success_template(['message' => 'پیامک با موفقیت به پزشک ارسال شد']);
                    }else {
                    
                        return success_template(['send'=> false]);
                    }
                }else {
                    return success_template(['send'=> false]);
                }
            }
            else {
                return error_template(['message' => 'ناموفق: زمان ویزیت شما هنوز فرا نرسیده لطفا در ساعات و روز ویزیت مجدد امتحان نمایید.']);
            }
        }
        return success_template(['send' => true]);
    }

}
