<?php

namespace App\Http\Controllers\Api\v2\User;

use App\Model\User\Refund;
use App\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class RefundController extends Controller
{
    public function store(Request $request)
    {
        $user = auth()->user();
        if ($user) {
            if ($user->refundRequest()->where('flag',0)->first())
                return error_template('شما قبلا درخواست خود را ثبت کرده اید');
            if ($user->credit < $request->amount)
                return error_template('درخواست شما بیش از موجودی حساب شماست');
            if (!$user->account_sheba || $user->account_sheba === '')
                return error_template('شماره شبای شما در سیستم وارد نشده است');
            $data = $request->all();
            $data['last_changed_user_id'] = auth()->id();
            $data['user_id'] = auth()->id();
            if (Refund::create($data))
                return success_template(['message'=>'درخواست شما با موفقیت ثبت شد']);
            return error_template('متاسفانه ثبت درخواست با مشکل مواجه شده است');
        }
        return error_template('کاربر یافت نشد');
    }
}
