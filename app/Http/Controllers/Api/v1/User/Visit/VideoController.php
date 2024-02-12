<?php

namespace App\Http\Controllers\Api\v1\User\Visit;

use App\Model\Visit\VideoRequest;
use App\Model\Visit\VideoRoom;
use App\Notifications\NewSkyRoom;
use App\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class VideoController extends Controller
{
    protected $request;

    public function __construct(Request $request)
    {
        date_default_timezone_set("Asia/Tehran");
        $this->request = $request;

    }

    public function ConnectChanel(){

        $ValidData = $this->validate($this->request,[
            'room' => 'required',
        ]);

        $request = VideoRoom::where('status', 'connecting')->where('token', $ValidData['room'])->where('audience_id', auth()->user()->id)->first();
        if($request){

            $request->status = 'call';
            $request->save();

            return success_template(['status' => 'call']);

        }

        return error_template('به نظر میرسد شما با چند دستگاه وارد این بخش شدید. تماس برای یک دستگاه مجاز است.');

    }

    public function rate(){

        $ValidData = $this->validate($this->request,[
            'rate' => 'required|numeric',
            'room' => 'required',
        ]);


        $request = VideoRoom::where('token', $ValidData['room'])->where('audience_id', auth()->user()->id)->first();
        if($request){

            if(!$request->finish_at){
                $request->finish_at = date('Y-m-d h:i:s');
            }
            $request->rate_audience = $ValidData['rate'];
            $request->status = 'end';
            $request->save();

            return success_template(['status' => 'end']);

        }

        return error_template('مشکلی در ثبت پیش امده است .');


    }

    public function request(){

        $ValidData = $this->validate($this->request,[
            'doctor' => 'required|numeric',
            'room_token' => 'nullable',
        ]);


        if (VideoRequest::where('user_id',auth()->user()->id)
            ->where('audience_id',$ValidData['doctor'])->where('room_token',$ValidData['room_token'])->doesntExist()) {

            $new = new VideoRequest();
            $new->user_id = auth()->user()->id;
            $new->audience_id = $ValidData['doctor'];
            $new->room_token = (isset($ValidData['room_token'])) ? $ValidData['room_token'] : null;
            $new->save();

            $doctor = User::find($new->audience_id);
            $doctor->notify(new NewSkyRoom(auth()->user(), $new->room_token));
        }
        return success_template(['request' => 'sends']);


    }

}
