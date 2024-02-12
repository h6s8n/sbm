<?php

namespace App\Http\Controllers\Api\v2\Affiliate;

use App\Enums\UserActivityLogEnum;
use App\Model\Transaction\AffiliateTransaction;
use App\Model\Visit\TransactionReserve;
use App\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use App\Repositories\v2\Logs\UserActivity\UserActivityLogInterface;


class AffiliateController extends Controller
{
    private $ActivityLog;
    public function __construct(UserActivityLogInterface $activityLog)
    {
        $this->ActivityLog = $activityLog;
    }

    public function groups()
    {
        $mode = \request()->input('mode');
        $user = auth()->user();
        $user = User::find(24758);
        $affiliates = AffiliateTransaction::where('affiliate_id', $user->id);
        switch ($mode) {
            case 'all':
            {
                $affiliates = $affiliates
                    ->select(DB::raw('sum(amount) as amount'),
                    DB::raw('COUNT(id) as count'))->get();
                break;
            }
            case 'group-status':
            {
                $affiliates = $affiliates
                    ->select(DB::raw('SUM(amount) as amount'),
                        DB::raw('COUNT(id) as count'),'status')
                    ->groupBy('status')->get();
                break;
            }
            case 'group-days':
            {
                $affiliates = $affiliates
                    ->select(DB::raw('SUM(amount) as amount'),
                        DB::raw('COUNT(id) as count'),DB::raw('DATE(created_at)'))
                    ->groupBy(DB::raw('DATE(created_at)'))->get();
                break;
            }
            case 'group-month':
            {
                $affiliates = $affiliates
                    ->select(DB::raw('SUM(amount) as amount'),
                        DB::raw('COUNT(id) as count'),DB::raw('DATE(created_at)'))
                    ->groupBy(DB::raw('MONTH(created_at)'))->get();
                break;
            }
        }
        return success_template($affiliates);
    }

    public function getToken()
    {
        $user = auth()->user();
        return success_template($user->token);
    }

    public function pulsynoMenu()
    {
        $menu = [
            [
                'url' => 'https://sbm24.com/presence-doctors',
                'title' => 'نوبت حضوری (مطب)',
                'ref' => 'in-person'
            ],
            [
                'url' => 'https://sbm24.com/specialties',
                'title' => 'نوبت اینترنتی',
                'ref' => 'online'
            ]
        ];

        return success_template($menu);
    }

    public function pulsynoGetToken()
    {
        $page = \request()->get('page',null);
        $user = User::find(\request()->get('user_id'));
        $user = $user ?? auth()->user();

        if ($user) {

            $this->ActivityLog->CreateLog($user, UserActivityLogEnum::GoToPulsyno);

            $url = 'https://api.pulsyno.com/user/GetToken';
            $parameters = array(
                "code" => "salamateFarda",
                "MobileNumber" => $user->mobile,
                "FullName" => $user->fullname ?? ""
            );
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
//                $header
            ]);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($parameters));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $response = curl_exec($ch);
            curl_close($ch);
            $token = json_decode(($response),false)->token;

            return success_template(['link' => 'https://wapp.pulsyno.com/#/'.$page.'?token='.$token]);
        }
        return success_template(['link' => 'https://wapp.pulsyno.com/#/'.$page]);
    }

}
