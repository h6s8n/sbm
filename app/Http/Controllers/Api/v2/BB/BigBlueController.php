<?php

namespace App\Http\Controllers\Api\v2\BB;

use App\Model\BB\BbUser;
use App\Model\Visit\EventReserves;
use App\Notifications\NewVideoCall;
use App\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;

class BigBlueController extends Controller
{
    private $key;
    private $prefix;

    public function __construct()
    {
        $this->key = "MFwwDQYJKoZIhvcNAQEBBQADSwAwSAJBANY03fd4M5NO06J27q/18w3uBThFKecJ";
        $this->prefix = "sbmVideo";
    }

    public function create()
    {

        $event = EventReserves::where('token_room',\request()->input('token'))->first();
        $doctor = $event->doctor()->first();
        $user = $event->user()->first();
//
//        $users = $this->findTwoAvailable();
//        return success_template($this->changeName($users[0],$doctor));

//        $meeting = $this->getVisit($event);
//        if ($meeting) {
//            $this->getAllPrivilege($meeting['ID']);
//            $meeting['id']=$meeting['ID'];
//        }
        $meeting=null;
        if (!$meeting) {
            $ch = curl_init("https://register.abrish.ir/service.php");
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            $apiKey = $this->key;
            $paramsList = array();
            $paramsList['serviceusername'] = 'm.aliyari1990@gmail.com';
            $paramsList['id'] = 277;
            $paramsList['name'] = "visit-" . $event->token_room.time();
        //    $paramsList['parent'] = 1;
            $paramsList['isPublic'] = 1;
            $paramsList['callback'] = 'https://sandbox.sbm24.net/api/v2/bb/update-users?meeting_id=[classid]';
            $paramsList['maxcount'] = "2";
            $paramsList['allowJoinInClassWithNoModerator'] = 1;
            $paramsList['mod'] = 'Onac';
            $paramsList['action'] = 'postClass';

            $params = array();
            $params['params'] = json_encode($paramsList);
            $params['checksum'] = sha1(json_encode($paramsList) . $apiKey);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
            $res = curl_exec($ch);
            $meeting = json_decode($res, true);
        }

        $users = $this->findTwoAvailable($meeting['id']);
        $this->changeName($users[0],$doctor);
        $this->changeName($users[1],$user);

        $this->addUser($users[0],$meeting['id']);
        $this->addUser($users[1],$meeting['id']);

        $links['doctor'] = $this->generate($users[0]->username, $meeting['id']);
        $links['user'] = $this->generate($users[1]->username , $meeting['id']);
        $user->notify(new NewVideoCall($doctor,$event->token_room, $links['user']));
        return success_template($links);
    }

    public function editMeeting()
    {

    }
    public function getVisit($event)
    {
        $ch = curl_init("https://register.abrish.ir/service.php");
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $apiKey = $this->key;
        $paramsList = array();
        $paramsList['id'] = 277;
        $paramsList['page'] = 0;
        $paramsList['filter'] = $event->token_room;
        $paramsList['serviceusername'] = 'm.aliyari1990@gmail.com';
        $paramsList['mod'] = 'Onac';
        $paramsList['action'] = 'getAllClasses';

        $params = array();
        $params['params'] = json_encode($paramsList);
        $params['checksum'] = sha1(json_encode($paramsList) . $apiKey);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        $res = curl_exec($ch);
        $res = json_decode($res, true);
        if (isset($res['error'])) {
            return null;
        } elseif (isset($res['data']) && $res['data']) {
            return $res['data'][0];
        }
    }

    public function makeUser($user)
    {
        $ch = curl_init("https://register.abrish.ir/service.php");
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $apiKey = $this->key;
        $paramsList = array();
        $paramsList['id'] = 277;
        $paramsList['page'] = 0;
        $paramsList['filter'] = $this->prefix . $user->id;
        $paramsList['serviceusername'] = 'm.aliyari1990@gmail.com';
        $paramsList['mod'] = 'Organization';
        $paramsList['action'] = 'getAllUsers';

        $params = array();
        $params['params'] = json_encode($paramsList);
        $params['checksum'] = sha1(json_encode($paramsList) . $apiKey);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        $res = curl_exec($ch);
        $res = json_decode($res, true);
        if (isset($res['error'])) {
            return $this->createUser($user);
        } elseif (isset($res['data']) && $res['data']) {
            return $res['data'][0];
        } else {
            return $this->createUser($user);

        }
    }

    public function createUser($user)
    {
        $ch = curl_init("https://register.abrish.ir/service.php");
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $apiKey = $this->key;
        $paramsList = array();
        $paramsList['id'] = 277;
        $paramsList['userid'] = 0;
        $paramsList['firstname'] = $user->name;
        $paramsList['lastname'] = $user->family;
        $paramsList['isenable'] = 1;
        $paramsList['username'] = $this->prefix . $user->id;
        $paramsList['gender'] = $user->gender == 0 ? 1 : 2;
        $paramsList['password'] = str_random(5);
        $paramsList['serviceusername'] = 'm.aliyari1990@gmail.com';
        $paramsList['mod'] = 'Organization';
        $paramsList['action'] = 'postUser';

        $params = array();
        $params['params'] = json_encode($paramsList);
        $params['checksum'] = sha1(json_encode($paramsList) . $apiKey);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        $res = curl_exec($ch);
        $res = json_decode($res, true);
        return $res;
    }

    public function addUser($user, $meeting_id)
    {
        $ch = curl_init("https://register.abrish.ir/service.php");
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $apiKey = $this->key;
        $paramsList = array();
        $paramsList['id'] = 277;
        $paramsList['classid'] = $meeting_id;
        $paramsList['username'] = $user->username;
        $user->approve == 2 ? $paramsList['role'] = 3 : $paramsList['role'] = 2;
        $paramsList['serviceusername'] = 'm.aliyari1990@gmail.com';
        $paramsList['mod'] = 'Onac';
        $paramsList['action'] = 'addPrivilege';

        $params = array();
        $params['params'] = json_encode($paramsList);
        $params['checksum'] = sha1(json_encode($paramsList) . $apiKey);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        $res = curl_exec($ch);
        $res = json_decode($res, true);
        return ($res);
    }

    public function getAllPrivilege($meeting_id): void
    {

        $ch = curl_init("https://register.abrish.ir/service.php");
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $apiKey = $this->key;
        $paramsList = array();
        $paramsList['id'] = 277;
        $paramsList['classid'] = $meeting_id;
        $paramsList['serviceusername'] = 'm.aliyari1990@gmail.com';
        $paramsList['mod'] = 'Onac';
        $paramsList['action'] = 'getAllPrivileges';
        $params = array();
        $params['params'] = json_encode($paramsList);
        $params['checksum'] = sha1(json_encode($paramsList) . $apiKey);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        $res = curl_exec($ch);
        $res = json_decode($res, true);

        if(isset($res['data']) && $res['data']) {
            foreach ($res['data'] as $user)
            {
                $this->deletePrivilege($user['UserName'],$meeting_id);
            }
        }
    }

    public function deletePrivilege($username,$meeting_id)
    {
        $ch = curl_init("https://register.abrish.ir/service.php");
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $apiKey = $this->key;
        $paramsList = array();
        $paramsList['id'] = 277;
        $paramsList['classid'] = $meeting_id;
        $paramsList['username'] = $username;
        $paramsList['serviceusername'] = 'm.aliyari1990@gmail.com';
        $paramsList['mod'] = 'Onac';
        $paramsList['action'] = 'deletePrivilege';
        $params = array();
        $params['params'] = json_encode($paramsList);
        $params['checksum'] = sha1(json_encode($paramsList) . $apiKey);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        $res = curl_exec($ch);
        $res = json_decode($res, true);
        return ($res);
    }

    public function generate($username, $meeting_id)
    {
        $ch = curl_init("https://register.abrish.ir/service.php");
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $apiKey = $this->key;
        $paramsList = array();
        $paramsList['id'] = 277;
        $paramsList['classid'] = $meeting_id;
        $paramsList['username'] = $username;
        $paramsList['serviceusername'] = 'm.aliyari1990@gmail.com';
        $paramsList['mod'] = 'Onac';
        $paramsList['action'] = 'generatePrivateLink';

        $params = array();
        $params['params'] = json_encode($paramsList);
        $params['checksum'] = sha1(json_encode($paramsList) . $apiKey);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        $res = curl_exec($ch);
        $res = json_decode($res, true);
        return ($res);
    }

    public function updateUsers()
    {
        if (!\request()->has('meeting_id') || !\request()->input('meeting_id'))
            return 'Hello';
        $result =BbUser::where('current_meeting_id',\request()->input('meeting_id'))
            ->update(['is_enable'=>1,'current_meeting_id'=>0]);
//        if ($result)
            return redirect()->to('https://cp.sbm24.com');
//        return error_template('false');
    }

    public function updateUserId(): void
    {
        $ch = curl_init("https://register.abrish.ir/service.php");
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $apiKey = $this->key;
        $paramsList = array();
        $paramsList['id'] = 277;
        $paramsList['page'] = 5;
        $paramsList['serviceusername'] = 'm.aliyari1990@gmail.com';
        $paramsList['mod'] = 'Organization';
        $paramsList['action'] = 'getAllUsers';

        $params = array();
        $params['params'] = json_encode($paramsList);
        $params['checksum'] = sha1(json_encode($paramsList) . $apiKey);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        $res = curl_exec($ch);
        $res = json_decode($res, true);
        if (isset($res['error'])) {
           // return $this->createUser($user);
        } elseif (isset($res['data']) && $res['data']) {
            foreach ($res['data'] as $user){
                $bb_user  = BbUser::where('username',$user['UserName'])->first();
                if ($bb_user)
                {
                    $bb_user->userid = $user['ID'];
                    $bb_user->save();
                }
            }
        } else {

        }
    }

    public function changeName($bb_user,$real_user)
    {
        $ch = curl_init("https://register.abrish.ir/service.php");
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $apiKey = $this->key;
        $paramsList = array();
        $paramsList['id'] = 277;
        $paramsList['userid'] = $bb_user->userid;
        $paramsList['firstname'] = $real_user->name;
        $paramsList['lastname'] = $real_user->family;
        $paramsList['username'] = $bb_user->username;
        $paramsList['gender'] = $real_user->gender === 0 ? 1 : 2;
        $paramsList['isenable'] = 1;
        $paramsList['specialaccess'] = 1;
        $paramsList['serviceusername'] = 'm.aliyari1990@gmail.com';
        $paramsList['mod'] = 'Organization';
        $paramsList['action'] = 'postUser';

        $params = array();
        $params['params'] = json_encode($paramsList);
        $params['checksum'] = sha1(json_encode($paramsList) . $apiKey);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        $res = curl_exec($ch);
        $res = json_decode($res, true);
        return $res;
    }

    public function findTwoAvailable($meeting_id)
    {
        $users = BbUser::where('is_enable',1)->limit(2)->get();
        foreach ($users as $user)
        {
            $user->is_enable = 0;
            $user->current_meeting_id = $meeting_id;
            $user->save();
        }
        return $users;
    }
}
