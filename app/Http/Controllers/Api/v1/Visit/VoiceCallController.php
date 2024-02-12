<?php

namespace App\Http\Controllers\Api\v1\Visit;

use App\Enums\VisitLogEnum;
use App\Model\Visit\EventReserves;
use App\Model\Visit\SafeCall;
use App\Notifications\NewVoiceCall;
use App\Repositories\v2\Visit\VisitLogInterface;
use App\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class VoiceCallController extends Controller
{

    protected $request;
    private $log;

    public function __construct(Request $request, VisitLogInterface $log)
    {
        date_default_timezone_set("Asia/Tehran");
        $this->request = $request;
        $this->log = $log;

    }

    public function createRoom()
    {

        $token_room = $this->request->token_room;


        $event = EventReserves::where('token_room', $token_room)->orderBy('created_at', 'DESC')->first();
        if (!$event)
            return error_template('اتاق نا معتبر');

        $event->last_activity_doctor = date('Y-m-d H:i:s');
        $event->save();

        /* @var \App\User $user */
        $dr_mobile = 0;
        $user = User::where('id', $event->doctor_id)->orderBy('created_at', 'DESC')->first();
        if (!$user) return error_template('در حال حاضر شماره موبایل پزشک در سامانه وارد نشده است.');
        $dr_mobile = $user->mobile;

        $this->log->createLog($event, $user->id, VisitLogEnum::SafeCall);
        // $max_talk = $user->hasSpecialization([4,8])  ? 30 : 10;
        $max_talk = 10;

        $us_mobile = 0;
        $user = User::where('id', $event->user_id)->orderBy('created_at', 'DESC')->first();
        if (!$user || (is_null($user->mobile) && is_null($event->safe_call_mobile))) {
            return error_template(
                'بیمار شما هم اکنون  با ایمیل در سایت ثبت نام کرده اند (ممکن است در ایران نباشند)  امکان تماس تلفنی فقط  برای بیماران دارای شماره موبایل ایرانی  مسیر می باشد.خواهشمند است با بیمارتان به صورت تماس تصویری و یا آفلاین (متنی، ویس، پیام ویدیویی) ارتباط برقرار نمایید.'
            );
        }

        if ($event->safe_call_mobile) {
            $us_mobile = change_number($event->safe_call_mobile);
        } else {
            $us_mobile = change_number($user->mobile);
        }
        if (!$us_mobile)
            return error_template(
                'بیمار شما هم اکنون  با ایمیل در سایت ثبت نام کرده اند (ممکن است در ایران نباشند)  امکان تماس تلفنی فقط  برای بیماران دارای شماره موبایل ایرانی  مسیر می باشد.خواهشمند است با بیمارتان به صورت تماس تصویری و یا آفلاین (متنی، ویس، پیام ویدیویی) ارتباط برقرار نمایید.'
            );

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://45.156.186.248/my/api215.php?email=parseh.manager@gmail.com&pass=uCu5EH7%40fVz%40fW6&drivers={$us_mobile}&customer={$dr_mobile}&siteurl=sbm24.com&maxtalktime={$max_talk}&ivrfile=/custom/215",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_SSL_VERIFYHOST=> 0,
            CURLOPT_SSL_VERIFYPEER=> 0,
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

        if($err && auth()->id() == 3334){
            return error_template($err);
        }
        curl_close($curl);

        if ($response) {
//            return success_template($response['callfile']);
            SafeCall::create([
                'event_id' => $event->id,
                'name' => $response,
                'mobile' => $us_mobile
            ]);
        }

        if (!$err) {
            $user->notify(new NewVoiceCall());
            return success_template('تماس شما تا دقایقی دیگر برقرار میشود.');
        }

        return error_template('تماس برقرار نشد');

//        return success_template('تماس شما تا دقایقی دیگر برقرار میشود.');

    }


}
