<?php

namespace App\Http\Controllers\Api\v1\User;

use App\Model\Visit\TransactionReserve;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class TransactionsController extends Controller
{
    protected $request;

    public function __construct(Request $request)
    {
        date_default_timezone_set("Asia/Tehran");
        $this->request = $request;

        require(base_path('app/jdf.php'));

    }


    public function reserve(){


        $user = auth()->user();

        $request = TransactionReserve::join('users', 'users.id', '=', 'transaction_reserves.doctor_id')
            ->where('transaction_reserves.status' , 'paid')
            ->where('transaction_reserves.user_id' , $user->id)
            ->select(
                'transaction_reserves.id as key' ,
                'transaction_reserves.created_at' ,
                'transaction_reserves.amount',
                'users.fullname as user_name',
                'receipt_link'
            )->get();


        $RequestFull = [];
        if($request){
            foreach ($request as $item){

                $dateTime = Carbon::parse($item['created_at']);
                $date = jdate('Y/m/d', strtotime($dateTime));
                $time = jdate('H:i', strtotime($dateTime));

                $RequestFull[] = [
                    'key' => $item['key'],
                    'price' => number_format($item['amount']),
                    'user_name' => $item['user_name'],
                    'date' => $date,
                    'time' => $time,
                    'receipt_link' => $item['receipt_link'],
                ];
            }
        }


        return success_template($RequestFull);


    }

}
