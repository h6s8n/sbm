<?php

namespace App\Http\Controllers\Api\v1\Doctor;

use App\Model\Wallet\DoctorWallet;
use App\SendSMS;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Str;

class FinancialManagementController extends Controller
{
    public function index($showSuccessMessage = false)
    {
        $doctor = auth()->user();
        $rial_amount = $this->accountBalance($doctor);
        $gold_amount = $doctor->golds;
        $gold_price = $this->goldPrice();
        $logs = DB::table('exchange_logs')->where('user_id', $doctor->id)->get(['exchange_type', 'given_amount', 'received_amount', 'unit_price', 'created_at'])->toArray();

        return view('financial_management', compact('doctor', 'rial_amount', 'gold_amount', 'gold_price', 'logs', 'showSuccessMessage'));
    }
    
    public function withdrawMoney(Request $request)
    {
        $doctor = auth()->user();
        $amount = floor($request->amount);
        $type = $request->type;
        $account_balance = $this->accountBalance($doctor);
        
        if($type == 'selected')
        {
            if(empty($amount))
            {
                return back()->withErrors(['amount' => 'مقدار انتخابی برای برداشت نباید خالی باشد.']);
            }
            if($amount <= 0)
            {
                return back()->withErrors(['amount' => 'مقدار انتخاب شده برای برداشت نباید کمتر از صفر باشد.']);
            }
            if($amount > $account_balance)
            {
                return back()->withErrors(['amount' => 'مقدار انتخاب شده برای برداشت بیشتر از دارایی ریالی شما است.']);
            }

            $withdraw = $this->withdraw($doctor->account_sheba, $amount);
            if (!$withdraw->ok) {
                return back()->with('withdraw', $result->message);
            }
            $bank_wage = $withdraw->amount_with_wage - $withdraw->data->amount;
            $this->decreaseRial(
                $doctor,
                $withdraw->data->amount,
                "برداشت از حساب از طریق پنل مدیریت امور مالی",
                $bank_wage
            );
            return $this->index('واریز بخشی از موجودی ریالی به حساب بانکی شما انجام شد.');
        }
        elseif($type == 'full')
        {
            $withdraw = $this->withdraw($doctor->account_sheba, $account_balance);
            if (!$withdraw->ok) {
                return back()->with('withdraw', $result->message);
            }
            $bank_wage = $withdraw->amount_with_wage - $withdraw->data->amount;
            $this->decreaseRial(
                $doctor,
                $withdraw->data->amount,
                "برداشت از حساب از طریق پنل مدیریت امور مالی",
                $bank_wage
            );
            return $this->index('واریز تمام موجودی ریالی به حساب بانکی شما انجام شد.');
        }
        else
        {
            return back()->withErrors(['type' => 'خطا در نوع برداشت.']);
        }
    }

    public function exchange(Request $request)
    {
        $doctor = auth()->user();
        $amount = floor($request->amount);
        $type = $request->type;
        $method = 'exchanging' . Str::studly($type);

        if(empty($amount) || $amount <= 0)
        {
            return back()->withErrors(['amount' => 'مقدار مبادله باید بیشتر از صفر باشد.']);
        }
        if(empty($type))
        {
            return back()->withErrors(['type' => 'نوع مبادله باید مشخص شود.']);
        }
        if(method_exists($this, $method))
        {
            return back()->withErrors(['type' => 'نوع تبادل تعریف نشده است.']);
        }
        return $this->{$method}($doctor, $amount, $type);
    }


    private function withdraw($sheba, $amount)
    {
        $result = (new \App\Services\Gateways\src\PayStarWallet())->pay(
            $amount,
            'IR' . $sheba,
            '', // firstname
            '', // lastname
        );
        $settlement = $result->data->created_settlements[0];
        unset($result->data);
        return $result + $settlement;
    }

    private function exchangingRialToGold($doctor, $amount, $type)
    {
        $gold_price = $amount * $this->goldPrice();
        $account_balance = $this->accountBalance($doctor);

        if($gold_price > $account_balance)
        {
            return back()->withErrors(['account_balance' => "دارایی ریالی برای خرید $amount گرم طلا کافی نمی باشد."]);
        }

        $this->decreaseRial(
            $doctor,
            $gold_price,
            "دریافت $amount گرم طلا."
        );
        $this->increaseGold(
            $doctor,
            $amount,
        );

        $this->registerLog($doctor, $type, $amount, $gold_price, $this->goldPrice());

        return $this->index("مقدار $amount گرم طلا خریداری شد.");
    }

    private function exchangingGoldToRial($doctor, $amount, $type)
    {
        $gold_price = $amount * $this->goldPrice();

        if($amount > $doctor->gold)
        {
            return back()->withErrors(['account_balance' => "دارایی طلای شما برای فروش $amount گرم طلا کافی نمی باشد."]);
        }

        $this->decreaseGold(
            $doctor,
            $amount,
        );
        $this->increaseRial(
            $doctor,
            $gold_price,
            "فروش $amount گرم طلا."
        );

        $this->registerLog($doctor, $type, $gold_price, $amount, $this->goldPrice());
        
        return $this->index("مقدار $amount گرم طلا فروخته شد.");
    }

    private function goldPrice()
    {
        static $result;
        if(!$result)
        {
            $result = json_decode(file_get_contents('http://108.165.128.108/api/gold.json'));
        }
        return $result;
    }

    private function increaseRial($doctor, $amount, $desc)
    {
        $doctor = auth()->user();

        $token = Str::random(30);
        $newTrans = new DoctorWallet();

        $newTrans->doctor_id = $doctor->id;
        $newTrans->user_id = $doctor->id;

        $newTrans->amount = $amount;
        $newTrans->description = $desc;

        $newTrans->bank_wage = 0;
        $newTrans->service_wage = 0;
        $newTrans->type = 'increase';
        $newTrans->status = 'paid_increase';
        $newTrans->service = 'exchange';
        $newTrans->token = $token;

        return $newTrans->save();
    }

    private function decreaseRial($doctor, $amount, $desc, $bank_wage = 0)
    {
        $token = Str::random(30);
        $newTrans = new DoctorWallet();

        $newTrans->doctor_id = $doctor->id;
        $newTrans->user_id = $doctor->id;

        $newTrans->amount = $amount;
        $newTrans->description = $desc;

        $newTrans->bank_wage = $bank_wage;
        $newTrans->service_wage = 0;
        $newTrans->type = 'decrease';
        $newTrans->status = 'paid_decrease';
        $newTrans->service = 'exchange';
        $newTrans->token = $token;

        return $newTrans->save();
    }

    private function increaseGold($doctor, $amount)
    {
        return $doctor->update([
            'gold' => $doctor->gold + $amount
        ]);
    }

    private function decreaseGold($doctor, $amount)
    {
        return $doctor->update([
            'gold' => $doctor->gold + $amount
        ]);
    }

    private function accountBalance($doctor)
    {
        $doctor_id = $doctor->id;
        $paid_increase = $this->calculateWallet($doctor_id, ['paid_increase']);
        $paid_decrease = $this->calculateWallet($doctor_id, ['paid_decrease']);
        $bank_wages = $this->calculateWallet($doctor_id, ['paid_increase', 'paid_decrease', 'pending_decrease'], 'bank_wage');
        $service_wages = $this->calculateWallet($doctor_id, ['paid_increase'], 'service_wage');
        $pending_decrease = $this->calculateWallet($doctor_id, ['pending_decrease']);
        $net = $paid_increase + $paid_decrease - $bank_wages;
        $account_accessible = $net + $pending_decrease - abs($service_wages);
        return $account_accessible;
    }

    private function calculateWallet($doctor_id, $status, $column = 'amount')
    {
        return DoctorWallet::where(['doctor_id' => $doctor_id, 'payment_type' => 'Wallet'])
            ->whereIn('status', $status)->sum($column);
    }

    private function registerLog($doctor, $exchange_type, $amount_received, $amount_given, $unit_price)
    {
        return;
        DB::table('exchange_logs')->create(
            compact('exchange_type', 'amount_received', 'amount_given', 'unit_price') + [
                'user_id' => $doctor->id,
                'created_at' => Carbon::now()
            ]
        );
    }
}
