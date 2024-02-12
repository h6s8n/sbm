<?php

namespace App\Http\Controllers\Admin\Advertising;

use App\Model\Advertising\Advertising;
use App\Model\Badge\BadgeRequest;
use App\Model\Badge\UserBadge;
use App\SendSMS;
use App\User;
use Carbon\Carbon;
use Hekmatinasser\Verta\Verta;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use function GuzzleHttp\Promise\all;

class AdvertisingController extends Controller
{
    public function __construct(Request $request)
    {
        $this->request = $request;
        require (base_path('app/jdf.php'));
    }

    public function index()
    {
        $advertising = Advertising::paginate(10);
        return view('admin.Advertising.index', compact('advertising'));
    }

    public function submitPaymentForm(Request $request,$id = null)
    {
        \request()->validate([
            'mobile' => 'required|digits:11|starts_with:09',
            'fullname' => 'required',
            'amount' => 'required|numeric',
            'plan' => 'required',
        ], [
            'mobile.digits' => 'شماره همراه را لاتین و با فرمت صحیح وارد کنید',
            'mobile.starts_with' => 'شماره همراه نامعتبر',
            'mobile.required' => 'شماره همراه الزامی',
            'amount.required' => 'مبلغ الزامی',
            'amount.digits' => 'مبلغ نامعنبر',
            'fullname.required' => 'نام و نام خانوادگی الزامی',
            'plan.required' => 'عنوان خدمت الزامی'
        ]);


        $this->request = $request;
        $token = Str::random(30);
        try {
            if ($id){
                $Advertising = Advertising::wherePaymentStatus('pending')->find($id);
            }else{
                $Advertising = new Advertising();
            }
            $Advertising->amount = $this->request->amount;
            $Advertising->fullname = $this->request->fullname;
            $Advertising->mobile = $this->request->mobile;
            $Advertising->plan = $this->request->plan;
            $Advertising->token = $token;
            $Advertising->save();

            $pay_link = url('payment/advertising/' . $token);

            if ($Advertising instanceof Advertising)
//                if (\request()->file('picture')) {
//                    $picture = $this->uploadImageCt('picture', 'files');
//                    $Advertising->picture = $picture;
//                    $Advertising->save();
//                }

                $params = array(
                    "token" => number_format($Advertising->amount),
                    "token2" => $Advertising->fullname,
                    "token3" => $pay_link,
                    "token10" => $Advertising->plan,
                );

                SendSMS::send($Advertising->mobile, 'adPayLink', $params);
                return redirect()->back()->with('success', 'لینک پرداخت ارسال شد');
        } catch (Exception $ex) {
            return redirect()->back()->with('error', 'ارسال لینک پرداخت با مشکل مواجه شد');
        }
    }

    public function paymentForm()
    {
        return view('admin.Advertising.form');
    }

    public function update(Advertising $ad)
    {
        if ($this->request->send == 'sms'){
            return $this->submitPaymentForm(\request(),$ad->id);
        }
        $data = \request()->all();
        if (\request()->file('picture')) {
            $picture = $this->uploadImageCt('picture', 'picture');
            $data['picture'] = $picture;
        }
        $ad->fill($data)->save();
        return redirect()->back()->with(['success' => 'ویرایش درخواست با موفقیت انجام شد']);
    }

    public function assign(User $user)
    {
        $badges = Badge::all();
        return view('admin.Badge.DoctorBadge', compact('badges','user'));
    }

    public function storeAssign($user)
    {
        if (\request()->has('activation_time')) {
            $activation_time = str_replace('/', '-',
                change_number(\request()->input('activation_time')));
            $activation_time = explode('-', $activation_time);
            $activation_time = Verta::getGregorian($activation_time[0], $activation_time[1], $activation_time[2]);
            $activation_time = Carbon::create($activation_time[0], $activation_time[1], $activation_time[2])->format('Y-m-d');
        }
        if (\request()->has('expiration_time')) {
            $expiration_time = str_replace('/', '-',
                change_number(\request()->input('expiration_time')));
            $expiration_time = explode('-', $expiration_time);
            $expiration_time = Verta::getGregorian($expiration_time[0], $expiration_time[1], $expiration_time[2]);
            $expiration_time = Carbon::create($expiration_time[0], $expiration_time[1], $expiration_time[2])->format('Y-m-d');
        }
        UserBadge::create([
            'badge_id'=>$this->request->input('badge_id'),
            'user_id'=>$user,
            'last_changed_user_id'=>auth()->id(),
            'activation_time'=>$activation_time,
            'expiration_time'=>$expiration_time
        ]);
        return redirect()->back();
    }

    public function detach(Badge $badge,User $user)
    {
        $user->badges()->detach($badge);
        return redirect()->back()->with(['success' => ' نشان با موفقیت حذف شد']);
    }

    public function requests()
    {
        $badge_requests = BadgeRequest::paginate(20);
        return view('admin.Badge.badgeRequest', compact('badge_requests'));
    }

    public function edit(Advertising $ad)
    {
        return view('admin.Advertising.edit', ['request' => $ad]);
    }

    public function updateRequest(BadgeRequest $request)
    {
        \request()->validate([
            'status' => 'required',
        ]);

        \request()->merge(['updated_by'=>auth()->id()]);

        $data = \request()->all();
        $request->fill($data)->save();
        return redirect()->back()->with(['success' => ' با موفقیت انجام شد']);
    }
}
