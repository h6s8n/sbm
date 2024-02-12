<?php

namespace App\Http\Controllers\Api\v1\Doctor\Visit;

use App\Model\Visit\Dossiers;
use App\Model\Visit\EventReserves;
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

        $ValidData = $this->validate($this->request,[
            'audience' => 'required|numeric',
            'visit' => 'required',
        ]);


        $user = auth()->user();


        $event = EventReserves::where('token_room', $ValidData['visit'])->where('status', 'active')->first();
        if(!$event) return error_template('خطا ، شما نمیتوانید برای این ویزیت اطلاعات ارسال کنید.');
        if ($user->approve==1) {
            $request = Dossiers::where('status', 'active')
                ->where('user_id', $user->id)
                ->where('audience_id', $ValidData['audience'])->get();
        }else $request=null;

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
}
