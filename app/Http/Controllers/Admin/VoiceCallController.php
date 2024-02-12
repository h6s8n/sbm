<?php

namespace App\Http\Controllers\Admin;

use App\Enums\VisitLogEnum;
use App\Model\Visit\EventReserves;
use App\Model\Visit\SafeCall;
use App\Repositories\v2\Visit\VisitLogInterface;
use App\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class VoiceCallController extends Controller
{
    protected $request;
    private $log;

    public function __construct(Request $request,VisitLogInterface $log)
    {
        date_default_timezone_set("Asia/Tehran");
        $this->request = $request;
        $this->log = $log;
        require (base_path('app/jdf.php'));

    }

    public function CreateCall(){

        $token_room = $this->request->token_room;


        $event = EventReserves::where('token_room', $token_room)
            ->orderBy('created_at', 'DESC')
            ->first();
        if(!$event)
            return redirect()->back()->with(['error'=>'ویزیت نا معتبر است'])->withInput();

        $event->last_activity_doctor = date('Y-m-d H:i:s');
        $event->save();

        /* @var \App\User $user */
        $dr_mobile = 0;
        $user = User::where('id', $event->doctor_id)->orderBy('created_at', 'DESC')->first();
        if(!$user)
            return redirect()->back()->with(['error'=>'شماره موبایل پزشک نامعتبر است'])->withInput();
        $dr_mobile = $user->mobile;

        $this->log->createLog($event,$user->id,VisitLogEnum::AdminCreateCall);
       // $max_talk = $user->hasSpecialization([4,8])  ? 30 : 10;
        $max_talk = 300;

        $us_mobile = 0;
        $user = User::where('id', $event->user_id)->orderBy('created_at', 'DESC')->first();
        if(!$user)
            return redirect()->back()->with(['error'=>'شماره موبایل بیمار نامعتبر است'])->withInput();

        if ($event->safe_call_mobile) {
            $us_mobile = $event->safe_call_mobile;
        } else {
            $us_mobile = $user->mobile;
        }

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://portal.callmee.org/my/api215.php?email=parseh.manager@gmail.com&pass=uCu5EH7%40fVz%40fW6&drivers={$us_mobile}&customer={$dr_mobile}&siteurl=sbm24.com&maxtalktime={$max_talk}&ivrfile=/custom/215",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
//            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => array(
                "accept: application/json",
                "cache-control: no-cache",
                "content-type: application/json",
            ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);
        if ($response) {
            SafeCall::create([
                'event_id' => $event->id,
                'name' => $response,
                'mobile'=>$us_mobile
            ]);
        }

        return redirect()->back()->with(['success'=>'تماس تا دقایقی دیگر انجام می شود'])->withInput();

    }

    public function lists($id)
    {
        $item = SafeCall::where('event_id',$id)->get();
        $event = EventReserves::find($id);
        return view('admin.visit.changeSafeCall',compact('item','id','event'));
    }

    public function change($id)
    {
        $safecall = EventReserves::where('id',$id)->first();
        if ($safecall){
            $safecall->safe_call_mobile=change_number($this->request->input('safe_call_mobile'));
            $safecall->save();
            return redirect()->back()->with(['success'=>'تغییر شماره با موفقیت انجام شد']);
        }
        return redirect()->back()->withErrors('تغییر شماره با مشکل مواجه شده است');
    }
}
