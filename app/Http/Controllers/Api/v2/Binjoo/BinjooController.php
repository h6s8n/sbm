<?php

namespace App\Http\Controllers\Api\v2\Binjoo;

use App\Model\Visit\EventReserves;
use App\Notifications\NewVideoCall;
use App\SendSMS;
use App\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;
use Ixudra\Curl\Facades\Curl;
use App\Repositories\v2\Visit\VisitLogRepository;
use App\Enums\VisitLogEnum;



class BinjooController extends Controller
{
    private $token;
    private $url;
    private $prefix;
    private $log;


    public function __construct()
    {
//        $this->token = '60dd5e9de62577a145ad6f43/74de1584-5605-486a-ab3f-1ab4e59ec732';
        $this->token = '60e4270f689a8455716d2d42/283059b1-efee-451d-8f91-5f18d161d660';
        $this->url = 'https://online.bellmeet.com/api/' . $this->token;
        $this->prefix = 'sbm-';
        $this->log = New VisitLogRepository();

    }

    public function request($token_room = null)
    {
        $token = \request()->input('token') ?? $token_room;

        $event = EventReserves::where('token_room', $token)->first();
        $doctor = $this->GetDoctor($event->doctor()->first());

        /* Start Creating Room */
        $room = $this->creatRoom($event);
        if (!$room->success)
            return error_template('اتاق ساخته نشد');
        $room = $room->room;
        /* End Creating Room */

        /* Creating Doctor User */
        if (!$doctor->success)
            return error_template('کاربر ساخته نشد');
        $doctor = $doctor->user;
        /* End Doctor User */

        $link = $this->createLink($doctor, $room);
        $user_link = $this->createGuestLink($event->user()->first(), $room);

        $doctor_ = $event->doctor()->first();
        $patient = $event->user()->first();

        try {
            info('starting video call: user_id: ' . $event->user->id);
            $patient->notify(new NewVideoCall($doctor_, $event->token_room, $user_link->link));
        } catch (\Exception $e) {
            Log::error('video call error: user_id: ' . $event->user->id .
                "\n\n message: " .
                $e->getMessage() .
                "\n\n trace: " .
                $e->getTraceAsString()
            );
            // @todo: if notification is not working, super admin should be notified. not implemented yet
            // return error_template('مشکل ارتباط در زیر ساخت. با پشتیبانی تماس بگیرید');
        }

        $params = array(
            "token" => $doctor_->fullname,
            "token2"  => $patient->fullname,
            "token10"  => $user_link->link,
        );

        SendSMS::send($patient->mobile, 'videoChatLink', $params);


        if ($link->success && $user_link->success) {
            $this->log->createLog($event,$patient->id,VisitLogEnum::BellMeet);

            return success_template([
                'doctor' => $link->link,
                'user' => $user_link->link]);
        }
    }

    public function GetDoctor(User $doctor)
    {
        $username = $this->prefix . $doctor->id;

        $user = $this->getUser($username);

        if (!$user->success) {
            $user = $this->createUser($doctor);
        }
        return $user;
    }

    private function creatRoom($event)
    {
        $method = 'createRoom';
        $name = $event->token_room . time() . uniqid('', false);
        $params = array(
            'name' => $name,
            'duration' => 3600,
            'max_user' => 2,
            'has_guest' => true,
            'redirect_url' => 'https://cp.sbm24.com',
        );

        $data = array(
            'method' => $method,
            'params' => $params
        );

        return $this->prepareRequest($data);
//        return Curl::to($this->url)
//            ->withData($data)
//            ->asJson(true)
//            ->post();

    }

    private function getUser($username)
    {
        $method = 'getUser';

        $params = array(
            'username' => $username,
        );

        $data = array(
            'method' => $method,
            'params' => $params
        );

        return $this->prepareRequest($data);

//        return Curl::to($thiss->url)
//            ->withData($data)
//            ->asJson(true)
//            ->post();
    }

    private function createUser($user)
    {
        $method = 'createUser';
        $username = $this->prefix . $user->id;

        $params = array(
            'username' => $username,
            'password' => $username,
            'name' => $user->fullname
        );

        $data = array(
            'method' => $method,
            'params' => $params
        );

        return $this->prepareRequest($data);

//        return Curl::to($this->url)
//            ->withData($data)
//            ->asJson(true)
//            ->post();
    }

    private function deleteUser($username)
    {
        $user = $this->getUser($username);
        $method = 'deleteUser';

        $params = array(
            'id' => $user->user->id,
        );

        $data = array(
            'method' => $method,
            'params' => $params
        );

        if ($user->success) {
            return $this->prepareRequest($data);
        }

        return true;
//        return Curl::to($this->url)
//            ->withData($data)
//            ->asJson(true)
//            ->post();
    }

    private function createLink($doctor, $room)
    {

        $method = 'addUsersToRoom';
        $params = array(
            'id' => $room->id,
            'users' => array(
                array(
                    'user' => $doctor->id,
                    'role' => 4
                )
            )
        );
        $data = array(
            'method' => $method,
            'params' => $params
        );

        $response = $this->prepareRequest($data);

//        $response = Curl::to($this->url)
//            ->withData($data)
//            ->asJson(true)
//            ->post();
//dd($response);
        $method = 'getLink';
        $params = array(
            'room' => $room->id,
            'user' => $doctor->id,
            'ttl' => 3600
        );
        $data = array(
            'method' => $method,
            'params' => $params
        );

        return $this->prepareRequest($data);

//        return Curl::to($this->url)
//            ->withData($data)
//            ->asJson(true)
//            ->post();
    }

    public function createGuestLink($user, $room)
    {
        $method = 'getGuestLink';
        $params = array(
            'room' => $room->id,
            'user' => array(
                    'name' => $user->fullname,
                    'role' => 4
            )
        );
        $data = array(
            'method' => $method,
            'params' => $params
        );

        return $this->prepareRequest($data);

//        return Curl::to($this->url)
//            ->withData($data)
//            ->asJson(true)
//            ->post();

    }

    public function prepareRequest($data)
    {
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $this->url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_FOLLOWLOCATION   => true,
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_SSL_VERIFYPEER => 0,
            CURLOPT_TIMEOUT => 30000,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => array(
                // Set here requred headers
                "accept: */*",
                "accept-language: en-US,en;q=0.8",
                "content-type: application/json",
            ),
        ));
        $response = curl_exec($curl);
        $err = curl_error($curl);
        if (auth()->id() == 3334){
            return error_template($response);
        }
//dd($response);
        return json_decode($response);
    }
}
