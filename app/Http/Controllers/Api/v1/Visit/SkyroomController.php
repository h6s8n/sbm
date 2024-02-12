<?php

namespace App\Http\Controllers\Api\v1\Visit;

use App\Enums\VisitLogEnum;
use App\Http\Controllers\Api\v2\Binjoo\BinjooController;
use App\Model\Visit\EventReserves;
use App\Notifications\NewSkyRoom;
use App\Repositories\v2\Visit\VisitLogRepository;
use App\SendSMS;
use App\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class SkyroomController extends Controller
{

    protected $request;
    protected $token_key = 'apikey-336945-59-02c82a185b98b1f20156704b41d37f8d';
    protected $app_key = 'sbm1';
    private $log;

    public function __construct(Request $request)
    {
        date_default_timezone_set("Asia/Tehran");
        $this->request = $request;
        $this->log = New VisitLogRepository();

    }

    public function createRoom()
    {

        $token_room = $this->request->token_room;

        $event = EventReserves::where('token_room', $token_room)
            ->orderBy('created_at', 'DESC')->first();
        if (!$event)
            return error_template('اتاق نا معتبر');

        $event->last_activity_doctor = date('Y-m-d H:i:s');
        $event->save();

//        return $this->eventRoom($token_room, $event, $event->doctor_id);
        return $this->newEventRoom($token_room, $event, $event->doctor_id);

    }

    public function joinRoom()
    {

        $token_room = $this->request->token_room;

        $event = EventReserves::where('token_room', $token_room)->orderBy('created_at', 'DESC')->first();
        if (!$event)
            return error_template('اتاق نا معتبر');

        $event->last_activity_user = date('Y-m-d H:i:s');
        $event->save();


       // return $this->eventRoom($token_room, $event, $event->user_id, false);
        return $this->newEventRoom($token_room, $event, $event->user_id, false);
    }

    public function eventRoom($token_room, $event, $uId, $create = true)
    {

        $room = $this->_getRooms($this->app_key . '-' . $token_room);
        $user = User::where('id', $uId)->orderBy('created_at', 'DESC')->first();
        if (!$room && $create)
            $room = $this->_createRoom($this->app_key . '-' . $token_room);
        //  return $room;
        if (!$room)
            return error_template('به دلیل خطا در زیر ساخت تماس بر قرار نشد. اتاق ثبت نشد.');

        $sky_user = $this->_getUsers($this->app_key . '-' . $uId);

        if (!$sky_user) {
            if ($user)
                $sky_user = $this->_createUser($this->app_key . '-' .
                    $uId, $user->username, $user->name, $user->family);
        }

        if (!$sky_user)
            return error_template('به دلیل خطا در زیر ساخت تماس بر قرار نشد. کاربر ثبت نشد.');


        $user_room_status = $this->_getRoomUsers($room, $sky_user);
        if (!$user_room_status)
            $user_room_status = $this->_addRoomUsers($room, $sky_user);

        if (!$user_room_status)
            return error_template('به دلیل خطا در زیر ساخت تماس بر قرار نشد. کاربر مجاز برای این اتاق نیست.');


        $room_link = $this->_getLoginUrl($room, $sky_user);

        // $room_link = $this->_createLoginUrl($room, $user->id);
        if (!$room_link)
            return error_template('به دلیل خطا در زیر ساخت تماس بر قرار نشد. ورود انجام نشد.');
        try {
            $client = User::find($event->user_id);
            $client->notify(new NewSkyRoom($user, $token_room));
        } catch (\Exception $exception) {
            return error_template($exception->getMessage());
        }


        return success_template(['room_link' => str_replace('www.skyroom.online', 'visit.sbm24.com', $room_link)]);

    }

    public function newEventRoom($token_room, $event, $uId, $create = true)
    {

        $room = $this->_getRooms($this->app_key . '-' . $token_room);
        $user = User::where('id', $uId)->orderBy('created_at', 'DESC')->first();

        if (!$room && $create)
            $room = $this->_createRoom($this->app_key . '-' . $token_room);

        if (!$room)
            return error_template('به دلیل خطا در زیر ساخت تماس بر قرار نشد. اتاق ثبت نشد.');


         $room_link = $this->_createLoginUrl($room, $user);

        if (!$room_link)
            return error_template('به دلیل خطا در زیر ساخت تماس بر قرار نشد. ورود انجام نشد.');
        try {
            $client = User::find($event->user_id);
            $client->notify(new NewSkyRoom($user, $token_room));
        } catch (\Exception $exception) {
            return error_template($exception->getMessage());
        }

        if (!$this->log->find($event,$event->doctor_id,VisitLogEnum::VideoCall)){
            $client = User::find($event->user_id);
            $doctor = User::find($event->doctor_id);
            $client_room_link = $this->_createLoginUrl($room, $client);
            $params = array(
                "token"  => $doctor->fullname,
                "token2" => $client->fullname,
                "token10" => str_replace('www.skyroom.online', 'visit.sbm24.com', $client_room_link),
            );

            SendSMS::send($client->mobile, 'videoChatLink', $params);
        }

        $this->log->createLog($event,$user->id,VisitLogEnum::VideoCall);

        return success_template(['room_link' => str_replace('www.skyroom.online', 'visit.sbm24.com', $room_link)]);

    }

    public function _getRooms($room)
    {

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://www.skyroom.online/skyroom/api/" . $this->token_key,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => "{\n\t\"action\": \"getRooms\"\n}",
            CURLOPT_HTTPHEADER => array(
                "cache-control: no-cache",
                "content-type: application/json",
                "postman-token: dea73a03-54d8-485c-8caf-f14865a68656"
            ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if (!$err) {
            $response = json_decode($response);
            if ($response && isset($response->ok) && $response->ok == true) {
                foreach ($response->result as $item) {
                    if (strtolower($room) == $item->name) {
                        return $item->id;
                    }
                }
            }
        }

        return false;

    }

    public function _deleteRoom($room)
    {

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://www.skyroom.online/skyroom/api/" . $this->token_key,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => "{\n    \"action\": \"deleteRoom\",\n    \"params\": {\n        \"room_id\": {$room}\n    }\n}",
            CURLOPT_HTTPHEADER => array(
                "cache-control: no-cache",
                "content-type: application/json",
                "postman-token: 86820930-bc57-8404-0606-62aa20af4a98"
            ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if (!$err) {
            $response = json_decode($response);
            if ($response && isset($response->ok) && $response->ok == true) {
                return true;
            }
        }

        return false;

    }

    public function _createRoom($room)
    {

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://www.skyroom.online/skyroom/api/" . $this->token_key,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => "{\n    \"action\": \"createRoom\",\n    \"params\": {\n        \"name\": \"{$room}\",\n        \"title\": \" مشاوره تصویری با سرویس سلامت بدون مرز\",\n        \"max_users\": 2,\n        \"guest_login\": true\n    }\n}",
            CURLOPT_HTTPHEADER => array(
                "cache-control: no-cache",
                "content-type: application/json",
                "postman-token: 8b07b672-36e3-9cfa-ba82-f0cf093fd1b7"
            ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if (!$err) {
            $response = json_decode($response);
            if ($response && isset($response->ok) && $response->ok == true) {
                return $response->result;
            }
        }

        return false;

    }

    public function _getUsers($username)
    {

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://www.skyroom.online/skyroom/api/" . $this->token_key,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => "{\n\t\"action\": \"getUsers\"\n}",
            CURLOPT_HTTPHEADER => array(
                "cache-control: no-cache",
                "content-type: application/json",
                "postman-token: dea73a03-54d8-485c-8caf-f14865a68656"
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);

        // if (!$err) {
        $response = json_decode($response);
        if ($response && isset($response->ok) && $response->ok == true) {
            foreach ($response->result as $item) {
                if (strtolower($username) == $item->username) {
                    return $item->id;
                }
            }
            //  }
        }

        return false;

    }

    public function _createUser($username, $nickname, $fname, $lname)
    {

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://www.skyroom.online/skyroom/api/" . $this->token_key,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => "{\n\t\"action\": \"createUser\",\n    \"params\": {\n        \"username\": \"{$username}\",\n        \"nickname\": \"{$nickname}\",\n        \"password\": \"Sbm@123456\",\n        \"email\": \"{$username}@sbm24.com\",\n        \"fname\": \"{$fname}\",\n        \"lname\": \"{$lname}\",\n        \"is_public\": true\n    }\n}",
            CURLOPT_HTTPHEADER => array(
                "cache-control: no-cache",
                "content-type: application/json",
                "postman-token: b03c5e55-1e6c-ccca-53cb-191449a85544"
            ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if (!$err) {
            $response = json_decode($response);
            if ($response && isset($response->ok) && $response->ok == true) {
                return $response->result;
            }
        }

        return false;

    }

    public function _deleteUser($user_id)
    {

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://www.skyroom.online/skyroom/api/" . $this->token_key,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => "{\n    \"action\": \"deleteUser\",\n    \"params\": {\n        \"user_id\": {$user_id}\n    }\n}",
            CURLOPT_HTTPHEADER => array(
                "cache-control: no-cache",
                "content-type: application/json",
                "postman-token: 86820930-bc57-8404-0606-62aa20af4a98"
            ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if (!$err) {
            $response = json_decode($response);
            if ($response && isset($response->ok) && $response->ok == true) {
                return true;
            }
        }

        return false;

    }

    public function _getRoomUsers($room_id, $user_id)
    {

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://www.skyroom.online/skyroom/api/" . $this->token_key,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => "{\n\t\"action\": \"getRoomUsers\",\n    \"params\": {\n        \"room_id\": {$room_id}\n    }\n}",
            CURLOPT_HTTPHEADER => array(
                "cache-control: no-cache",
                "content-type: application/json",
                "postman-token: 8183b4ed-82a5-583c-0bc2-26a169604cfb"
            ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if (!$err) {
            $response = json_decode($response);
            if ($response && isset($response->ok) && $response->ok == true) {
                foreach ($response->result as $item) {

                    if ($user_id == $item->user_id) {
                        return true;
                    }

                }
            }
        }

        return false;

    }

    public function _addRoomUsers($room_id, $user_id, $access = 2)
    {

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://www.skyroom.online/skyroom/api/" . $this->token_key,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => "{\n\t\"action\": \"addRoomUsers\",\n    \"params\": {\n        \"room_id\": {$room_id},\n        \"users\": [{\n\t        \"user_id\": {$user_id},\n\t        \"access\": {$access}\n\t    }]\n    }\n}",
            CURLOPT_HTTPHEADER => array(
                "cache-control: no-cache",
                "content-type: application/json",
                "postman-token: 7ab7102a-ef3b-18f0-ea57-d39fd80077e2"
            ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if (!$err) {
            $response = json_decode($response);

            return $response->ok;
        }

        return false;

    }

    public function _getLoginUrl($room_id, $user_id)
    {

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://www.skyroom.online/skyroom/api/" . $this->token_key,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => "{\n\t\"action\": \"getLoginUrl\",\n    \"params\": {\n        \"room_id\": {$room_id},\n\t    \"user_id\": {$user_id},\n        \"language\": \"fa\",\n        \"ttl\": 340\n    }\n}",
            CURLOPT_HTTPHEADER => array(
                "cache-control: no-cache",
                "content-type: application/json",
                "postman-token: bc233ece-e550-6d15-399f-ae99ba6a5d87"
            ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if (!$err) {
            $response = json_decode($response);

            if ($response && isset($response->ok) && $response->ok == true) {
                return $response->result;
            }
        }

        return false;

    }

    public function _createLoginUrl($room_id, $user)
    {
        $user_id = $user->id;
        $fullname = $user->fullname;

        $data = array(
            "action" => "createLoginUrl",
            "params" => array(
                "room_id" => $room_id,
                "user_id" => $user_id,
                "nickname" => $fullname,
                "access" => 3,
                "concurrent" => 2,
                "language" => "fa",
                "ttl" => 3600
            )
        );

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://www.skyroom.online/skyroom/api/" . $this->token_key,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => array(
                "cache-control: no-cache",
                "content-type: application/json",
                "postman-token: bc233ece-e550-6d15-399f-ae99ba6a5d87"
            ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);

        if (!$err) {
            $response = json_decode($response);

            if ($response && isset($response->ok) && $response->ok == true) {
                return $response->result;
            }
        }

        return false;

    }

    public function deleteAllRoom()
    {

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://www.skyroom.online/skyroom/api/" . $this->token_key,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => "{\n\t\"action\": \"getRooms\"\n}",
            CURLOPT_HTTPHEADER => array(
                "cache-control: no-cache",
                "content-type: application/json",
                "postman-token: dea73a03-54d8-485c-8caf-f14865a68656"
            ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if (!$err) {
            $response = json_decode($response);
            if ($response && isset($response->ok) && $response->ok == true) {
                foreach ($response->result as $item) {

                    $this->_deleteRoom($item->id);

                }
            }
        }

        return '';

    }

    public function deleteAllUsers()
    {

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://www.skyroom.online/skyroom/api/" . $this->token_key,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => "{\n\t\"action\": \"getUsers\"\n}",
            CURLOPT_HTTPHEADER => array(
                "cache-control: no-cache",
                "content-type: application/json",
                "postman-token: dea73a03-54d8-485c-8caf-f14865a68656"
            ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if (!$err) {
            $response = json_decode($response);
            if ($response && isset($response->ok) && $response->ok == true) {
                foreach ($response->result as $item) {

                    $this->_deleteUser($item->id);

                }
            }
        }

        return '';

    }


}
