<?php

namespace App\Http\Controllers\Api\v2\User;

use App\Repositories\v2\User\UserInterface;
use App\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    private $user;

    public function __construct(UserInterface $user)
    {
     $this->user = $user;
    }

    public function SetTimeNotification_Store(Request $request)
    {
        $request->validate([
            'doctor_id'=>'required'
        ]);

        /* @var User $user*/
        $user = auth()->user();
        if ($user->mobile){
            $request = $request->all();
            $request['mobile'] = $user->mobile;
            $has = $this->user->HasSetTimeNotification($request['doctor_id']);
            if ($has->status) {
                if ($has->object->isEmpty()){
                    if (!$user->hasTimeWith($request['doctor_id'])) {
                        $response = $this->user->NotifyMeNewTime($request);
                        if ($response->status)
                            return success_template($response->object);
                        return error_template($response->message);
                    }
                    return error_template('شما در حال حاضر یک ویزیت با این دکتر دارید');
                }
                return error_template('شما قبلا درخواست خود را ثبت کرده اید');
            }else
            return error_template($has->message);
        }
        return error_template('شماره موبایل شما در سیستم ثبت نشده است');
    }

    public function all()
    {
        $approve=\request()->input('approve');
        $search=\request()->input('search');
        $users = User::select('id','fullname','mobile','email','approve')
            ->where('doctor_status','LIKE',
                DB::raw('CASE WHEN approve=1 then "active" else "%" END'))
            ->whereIn('status',['imported','active'])
			->where('fullname', 'LIKE' , '%'.$search.'%');
        if ($approve)
            $users = $users->where('approve',$approve);
        $users=$users->get();
        return success_template($users);
    }
}
