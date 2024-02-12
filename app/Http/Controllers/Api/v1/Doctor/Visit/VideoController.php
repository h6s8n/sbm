<?php

namespace App\Http\Controllers\Api\v1\Doctor\Visit;

use App\Model\Visit\VideoRoom;
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

    public function CreateChanel(){

        $ValidData = $this->validate($this->request,[
            'audience' => 'required|numeric',
        ]);

        $new_room = new VideoRoom();
        $new_room->token = str_random(15);
        $new_room->user_id = auth()->user()->id;
        $new_room->audience_id = $ValidData['audience'];
        $new_room->status = 'connecting';
        $new_room->save();

        return success_template(['room' => $new_room->token]);

    }

    public function rate(){

        $ValidData = $this->validate($this->request,[
            'rate' => 'required|numeric',
            'room' => 'required',
        ]);


        $request = VideoRoom::where('token', $ValidData['room'])->where('user_id', auth()->user()->id)->first();
        if($request){

            if(!$request->finish_at){
                $request->finish_at = date('Y-m-d h:i:s');
            }

            $request->rate_user = $ValidData['rate'];
            $request->status = 'end';
            $request->save();

            return success_template(['status' => 'end']);

        }

        return error_template('مشکلی در ثبت پیش امده است .');


    }

}
