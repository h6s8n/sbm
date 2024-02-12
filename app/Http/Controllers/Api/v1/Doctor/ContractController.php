<?php

namespace App\Http\Controllers\Api\v1\Doctor;

use App\Model\Notification\UserDoctorNotification;
use App\Model\Transaction\AffiliateTransaction;
use App\Model\Visit\DoctorCalender;
use App\Model\Visit\EventReserves;
use App\Model\Visit\Message;
use App\Model\Wallet\DoctorWallet;
use App\Model\Doctor\DoctorContract;
use App\Model\Visit\TransactionReserve;
use App\Repositories\v2\User\UserInterface;
use App\SendSMS;
use App\User;
use App\Services\Gateways\src\Zibal;
use App\Services\Gateways\src\PayStar;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Str;
use SoapClient;
use Vandar\Laravel\Facade\Vandar;

class ContractController extends Controller
{

    protected $request;

    public function __construct(Request $request)
    {
        date_default_timezone_set("Asia/Tehran");
        require(base_path('app/jdf.php'));
        $this->request = $request;

    }

    public function index()
    {
        $doctor_id = auth()->id();

        $contracts = DoctorContract::where('user_id',$doctor_id)->get();

        return success_template($contracts);
    }

    public function show($contract)
    {
        $contract = DoctorContract::find($contract);
        return success_template($contract);
    }

    public function sign()
    {
        $contract = DoctorContract::where('sign_picture',null)->findOrFail($this->request->id);

        if ($request->file('sign_picture')) {
            $sign_picture = $this->uploadImageCt('sign_picture');
            $contract->sign_picture = $sign_picture;
        }

        if ($request->file('picture')) {
            $picture = $this->uploadImageCt('picture');
            $contract->picture = $picture;
        }


        $contract->save();

    }

}
