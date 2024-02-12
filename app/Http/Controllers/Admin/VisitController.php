<?php

namespace App\Http\Controllers\Admin;

use App\Enums\VisitLogEnum;
use App\Enums\VisitTypeEnum;
use App\Events\MessageSent;
use App\Model\Discount\Discount;
use App\Model\Partners\Partner;
use App\Model\Transaction\AffiliateTransaction;
use App\Model\Visit\EventReserves;
use App\Model\Visit\TransactionCredit;
use App\Model\Visit\TransactionDoctor;
use App\Model\Visit\TransactionReserve;
use App\Model\Visit\VisitLog;
use App\Repositories\v2\Visit\VisitLogInterface;
use App\Model\Doctor\Specialization;
use App\SendSMS;
use App\User;
use Carbon\Carbon;
use Hekmatinasser\Verta\Facades\Verta;
use App\Enums\LanguageEnum;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Laravel\Passport\Passport;

class VisitController extends Controller
{
    private $log;

    public function __construct(VisitLogInterface $visitLog)
    {
        $this->log = $visitLog;
        require_once(base_path('app/jdf.php'));
    }

    public function listsOfAbsences()
    {
        $meetings = EventReserves::where('visit_status', 'absence_of_doctor')->paginate(10);
        return view('admin.visit.index', compact('meetings'));
    }

    public function refund(User $user, EventReserves $er)
    {
        $transaction = TransactionReserve::where('user_id', $user->id)
            ->where('doctor_id', $er->doctor_id)
            ->where('calender_id', $er->calender_id)
            ->where('status', 'paid')->first();
        if ($er->visit_status == 'refunded')
            return redirect()->back()->with(['error' => 'وجه قبلا بازگشت داده شده است']);
        if ($transaction) {
            DB::beginTransaction();
            try {
                $er->visit_status = 'refunded';
                $er->save();
                $sku = str_random(20);
                $newTransaction = new TransactionCredit();
                $newTransaction->user_id = $user->id;
                $newTransaction->amount = $transaction->amount;
                $newTransaction->token = $sku;
                $newTransaction->status = 'paid';
                $newTransaction->message = 'افزایش - توسط ادمین ('. auth()->user()->fullname .') پزشک غایب دکتر ' . $er->doctor->fullname;
                $newTransaction->save();
                $user->credit = ($user->credit + $transaction->amount);
                $user->save();

                $this->log->createLog($er, auth()->id(), VisitLogEnum::AbsenceOfDoctor);

//                $cal = $er->calendar()->first();
//                if ($cal)
//                {
//                    --$cal->reservation;
//                    $cal->save();
//                }

                DB::commit();
                SendSMS::sendTemplateTwo($user->mobile, $user->family, $transaction->amount, 'refund');
                SendSMS::sendTemplateTwo($er->doctor()->first()->mobile,
                    $er->fa_data, $er->time, 'cancelVisit');
                return redirect()->back()->with(['success' => 'بازگشت وجه با موفقیت انجام شد']);
            } catch (\Exception $exception) {
                DB::rollBack();
                return redirect()->back()->with(['error' => 'انجام نشد']);
            }
        }
        return redirect()->back()->with(['error' => 'فایل پرداختی کاربر یافت نشد']);
    }

    public function FullyCanceled(User $user, EventReserves $er)
    {
        $transaction = TransactionReserve::where('user_id', $user->id)
            ->where('doctor_id', $er->doctor_id)
            ->where('calender_id', $er->calender_id)
            ->where('status', 'paid')->first();
        if ($er->visit_status == 'refunded')
            return redirect()->back()->with(['error' => 'وجه قبلا بازگشت داده شده است']);
        if ($transaction) {
            DB::beginTransaction();
            try {
                $er->visit_status = 'refunded';
                $er->save();
                $sku = str_random(20);
                $newTransaction = new TransactionCredit();
                $newTransaction->user_id = $user->id;
                $newTransaction->amount = $transaction->amount;
                $newTransaction->token = $sku;
                $newTransaction->status = 'paid';
                $newTransaction->message = 'افزایش - توسط ادمین ('. auth()->user()->fullname .') لغو ویزیت با ' . $er->doctor->fullname;
                $newTransaction->save();
                $user->credit = ($user->credit + $transaction->amount);
                $user->save();

                $this->log->createLog($er, auth()->id(), VisitLogEnum::CancellationVisit);

                DB::commit();
                SendSMS::sendTemplateTwo($user->mobile, $user->family, $transaction->amount, 'refund');
                return redirect()->back()->with(['success' => 'بازگشت وجه با موفقیت انجام شد']);
            } catch (\Exception $exception) {
                DB::rollBack();
                return redirect()->back()->with(['error' => 'انجام نشد']);
            }
        }
        return redirect()->back()->with(['error' => 'فایل پرداختی کاربر یافت نشد']);
    }

    public function openRefund(EventReserves $event)
    {
        DB::beginTransaction();
        try {
            $event->visit_status = 'not_end';
            $event->save();


            $transaction = $event->UserTransaction('paid')->first();
            if ($transaction) {

                $amount_visit = $transaction->amount;
                $amount = 0;

                $calendar = $event->calendar()->first();
                if ($amount_visit >= OurBeneficiary($calendar->type ?? null) && date('Y-m-d', strtotime($event->created_at)) >=
                    date('Y-m-d', strtotime(dateChangedBeneficiary())))
                    $amount = $amount_visit - OurBeneficiary($calendar->type ?? null);
                else {

                    if ($amount_visit > 400000) {
                        $amount = $amount_visit - 108000;
                    } else {
                        $amount = $amount_visit - 108000;
                    }
                }

                $client = User::where('id', $event->user_id)->first();
                if ($client) {
                    $client->credit = ($client->credit - $amount);
                    $client->save();
                    if ($amount > 0) {
                        $sku = str_random(20);
                        $newTransaction = new TransactionCredit();
                        $newTransaction->user_id = $client->id;
                        $newTransaction->amount = (0 - $amount);
                        $newTransaction->token = $sku;
                        $newTransaction->status = 'paid';
                        $newTransaction->message = 'کاهش توسط ادمین ('. auth()->user()->fullname .') - لغو عودت ویزیت با دکتر ' . $event->doctor->fullname;
                        $newTransaction->save();
                    }
                }
            }

            $this->log->createLog($event, auth()->id(), VisitLogEnum::OpenRefund);
            DB::commit();

            return redirect()->back()->with(['success' => 'وضعیت ویزیت به پایان نیافته تغییر کرد']);
        } catch (\Exception $exception) {
            DB::rollBack();
            return redirect()->back()->with(['error' => $exception->getMessage()]);
        }
    }

    public function cancelRefund(EventReserves $er)
    {
        try {

            $requestEvent = EventReserves::where('user_id', $er->user_id)
                ->where('doctor_id', $er->doctor_id)
                ->where('status', 'active')
                ->where('visit_status', 'not_end')
                ->where('id' , '!=' , $er->id)
                ->orderBy('created_at', 'desc')->first();

            if ($requestEvent && $requestEvent->visit_status == 'not_end') {
                return error_template('در حال حاضر نوبت قبلی این بیمار با این پزشک به پایان نرسیده است،');
            }

            $er->visit_status = 'not_end';
            $er->save();
            $this->log->createLog($er, auth()->id(), VisitLogEnum::RejectRefundRequest);
            return redirect()->back()->with(['success' => 'وضعیت ویزیت به پایان نیافته تغییر کرد']);
        } catch (\Exception $exception) {
            return redirect()->back()->with(['error' => 'متاسفانه درخواست شما انجام نشد']);
        }
    }

    public function listOfVisits()
    {
        $events = EventReserves::whereHas('user', function ($query) {
            $query->where('fullname', 'LIKE',
                \request()->input('filter_br') ?
                    '%' . \request()->input('filter_br') . '%' : '%');
            if (\request()->input('filter_br_mobile')){
                $query->where('mobile', \request()->input('filter_br_mobile'));
            }
            if (\request()->input('filter_br_email')){
                $query->where('email', \request()->input('filter_br_email'));
            }
        })->whereHas('doctor', function ($query) {
                $query->where('fullname', 'LIKE',
                    \request()->input('filter_user') ?
                        '%' . \request()->input('filter_user') . '%' : '%');
            if (\request()->has('specialization_id') && \request()->input('specialization_id'))
                $query->whereHas('specializations',function ($query_){
                    $query_->where('id',\request()->input('specialization_id'));
                });
            });

        if (\request()->has('from') &&
            \request()->input('from') &&
            \request()->has('to') &&
            \request()->input('to')){
            $from = change_number(\request()->input('from'));
            /* @var \Hekmatinasser\Verta\Verta $from_date */
            $from = explode('/',$from);
            $from_date = Verta::create();
            $from_date->year($from[0]);
            $from_date->month($from[1]);
            $from_date->day($from[2]);
            $from_date = $from_date->formatGregorian('Y-m-d');

            $to = change_number(\request()->input('to'));
            /* @var \Hekmatinasser\Verta\Verta $to_date */
            $to = explode('/',$to);
            $to_date = Verta::create();
            $to_date->year($to[0]);
            $to_date->month($to[1]);
            $to_date->day($to[2]);
            $to_date = $to_date->formatGregorian('Y-m-d');

            $events = $events->whereDate('data','>=',$from_date)
                ->whereDate('data','<=',$to_date);

        }elseif(\request()->getQueryString() == null) {
            $events = $events->whereDate('data', Carbon::now()->format('Y-m-d'));
        }
        if (\request()->has('calendar_type') && \request()->input('calendar_type'))
            $events = $events->whereHas('calendar',function ($query){
                $query->where('type',\request()->input('calendar_type'));
            });

        if (\request()->has('partner_id') && \request()->input('partner_id'))
            $events = $events->whereHas('calendar',function ($query){
                $query->where('partner_id',\request()->input('partner_id'));
            });
        try {

            $events = $events->paginate(10);
        }catch (\Exception $exception){
            dd($exception);}
        $partners = Partner::all();
        $specializations = Specialization::all()->where('language_id',LanguageEnum::Farsi);
        return view('admin.visit.list', compact('events','partners','specializations'));
    }

    public function OpenAgain(EventReserves $event)
    {
        $event->doctor_payment_status = 'pending';
        $event->visit_status = 'not_end';
        $event->save();
        $this->log->createLog($event, auth()->id(), VisitLogEnum::Reactivating);

        $transaction = $event->UserTransaction('paid')->first();
        if ($transaction) {

            $amount_visit = $transaction->amount;
            $amount = 0;

            $calendar = $event->calendar()->first();
            if ($amount_visit >= OurBeneficiary($calendar->type ?? null) && date('Y-m-d', strtotime($event->created_at)) >=
                date('Y-m-d', strtotime(dateChangedBeneficiary())))
                $amount = $amount_visit - OurBeneficiary($calendar->type ?? null);
            else {

                if ($amount_visit > 400000) {
//                        $amount_pe = ($amount_visit - 89000) / 100;
                    $amount = $amount_visit - 108000;
                } else {
                    $amount = $amount_visit - 108000;
                }
            }

            $client = User::where('id', $event->user_id)->first();
            if ($client) {
                $client->credit = ($client->credit - $amount);
                $client->save();
                if ($amount > 0) {
                    $sku = str_random(20);
                    $newTransaction = new TransactionCredit();
                    $newTransaction->user_id = $client->id;
                    $newTransaction->amount = (0 - $amount);
                    $newTransaction->token = $sku;
                    $newTransaction->status = 'paid';
                    $newTransaction->message = 'کاهش توسط ادمین ('. auth()->user()->fullname .') - بازگشایی مجدد ویزیت با دکتر ' . $event->doctor->fullname;
                    $newTransaction->save();
                }
            }
        }

        return redirect()->back()->with(['success' => 'ویزیت با موفقیت بازگشایی شد']);
    }

    public function FinishedOpenAgain(EventReserves $event)
    {
        $event->doctor_payment_status = 'pending';
        $event->visit_status = 'not_end';
        $event->save();
        $this->log->createLog($event, auth()->id(), VisitLogEnum::ReactivateFinishedVisit);

//        $transaction = $event->DoctorTransaction('paid')->first();
//        if ($transaction) {
//            $transaction->status = 'pending';
//            $transaction->save();
//        }
        return redirect()->back()->with(['success' => 'ویزیت با موفقیت بازگشایی شد']);
    }

    public function cancel(EventReserves $event)
    {
        $event->visit_status = 'cancel';
        $event->finish_at = date('Y-m-d h:i:s');
        $event->doctor_payment_status = 'pending';
        $event->save();

        $this->log->createLog($event, auth()->id(), VisitLogEnum::CancelByAdmin);
        $transaction = $event->UserTransaction('paid')->first();
        if ($transaction) {
            $amount_visit = $transaction->amount;
            $amount = 0;

            $discount_amount = 0;

            if ($transaction->discount_id){
                $discount = Discount::find($transaction->discount_id);
                if ($discount){
                    switch ($discount->type){
                        case 1:{
                            $discount_amount = $transaction->discount_amount;
                            break;
                        }
                    }
                }
            }
            $calendar = $event->calendar()->first();
            if ($amount_visit >= OurBeneficiary($calendar->type ?? null) && date('Y-m-d', strtotime($event->created_at)) >=
                date('Y-m-d', strtotime(dateChangedBeneficiary())))
                $amount = $amount_visit - OurBeneficiary($calendar->type ?? null) + $discount_amount;
            else {
                if ($amount_visit > 400000) {
//                        $amount_pe = ($amount_visit - 89000) / 100;
                    $amount = $amount_visit - 108000;
                } else {
                    $amount = $amount_visit - 108000;
                }
            }

            $client = User::where('id', $event->user_id)->first();
            if ($client) {
                $client->credit = ($client->credit + $amount);
                $client->save();
                if ($amount > 0) {
                    $sku = str_random(20);
                    $newTransaction = new TransactionCredit();
                    $newTransaction->user_id = $client->id;
                    $newTransaction->amount = $amount;
                    $newTransaction->token = $sku;
                    $newTransaction->status = 'paid';
                    $newTransaction->message = 'افزایش توسط ادمین ('. auth()->user()->fullname .') - عودت ویزیت با دکتر ' . $event->doctor->fullname;
                    $newTransaction->save();
                }
            }
        }
        return redirect()->back();
    }

    public function finish(EventReserves $event)
    {
        $event->visit_status = 'end';
        $event->finish_at = Carbon::now()->format('Y-m-d h:i:s');
        $event->doctor_payment_status = 'debtor';
        $event->save();

        $this->log->createLog($event, auth()->id(), VisitLogEnum::FinishByAdmin);

        $transaction = TransactionReserve::where('user_id', $event->user_id)
            ->where('doctor_id', $event->doctor_id)
            ->where('calender_id', $event->calender_id)
            ->where('status', 'paid')
            ->first();

        if ($transaction) {
            $ck_Dr = TransactionDoctor::where('user_id', $event->user_id)
                ->where('doctor_id', $event->doctor_id)
                ->where('event_id', $event->id)
                ->whereIn('status', ['paid', 'pending'])
                ->first();
            if (!$ck_Dr) {

                $amount_visit = $transaction->amount;
                $amount = 0;

                $discount_amount = 0;
                if ($transaction->discount_id){
                    $discount = Discount::find($transaction->discount_id);
                    if ($discount){
                        switch ($discount->type){
                            case 1:{
                                $discount_amount = $transaction->discount_amount;
                                break;
                            }
                        }
                    }
                }


                $calendar = $event->calendar()->first();
                if ($amount_visit >= OurBeneficiary($calendar->type ?? null) && date('Y-m-d', strtotime($event->created_at)) >=
                    date('Y-m-d', strtotime(dateChangedBeneficiary())))
                    $amount = $amount_visit - OurBeneficiary($calendar->type ?? null)+$discount_amount;
                else {

                    if ($amount_visit > 400000) {
//                        $amount_pe = ($amount_visit - 89000) / 100;
                        $amount = $amount_visit - 108000;
                    } else {
                        $amount = $amount_visit - 108000;
                    }
                }
                $transactionDr = new TransactionDoctor();

                $transactionDr->user_id = $event->user_id;
                $transactionDr->doctor_id = $event->doctor_id;
                $transactionDr->event_id = $event->id;
                $transactionDr->amount = $amount;
                $transactionDr->save();

                $affiliate = AffiliateTransaction::where('event_id', $event->id)->first();
                if ($affiliate) {
                    $affiliate->status = 1;
                    $affiliate->save();
                }

            }
            return redirect()->back()->with(['success' => 'ویزیت با موفقیت پایان یافت']);
        }
        return redirect()->back()->with(['error' => 'فایل پرداختی بیمار یافت نشد']);

    }

    public function finishRep(EventReserves $event)
    {
        $event->visit_status = 'end';
        $event->finish_at = Carbon::now()->format('Y-m-d h:i:s');
        $event->doctor_payment_status = 'debtor';
        $event->save();

        $this->log->createLog($event, auth()->id(), VisitLogEnum::FinishByAdmin);

        $transaction = TransactionReserve::where('user_id', $event->user_id)
            ->where('doctor_id', $event->doctor_id)
            ->where('calender_id', $event->calender_id)
            ->where('status', 'paid')
            ->first();

        if ($transaction) {
            $ck_Dr = TransactionDoctor::where('user_id', $event->user_id)
                ->where('doctor_id', $event->doctor_id)
                ->where('event_id', $event->id)
                ->whereIn('status', ['paid', 'pending'])
                ->first();
            if (!$ck_Dr) {

                $amount_visit = $transaction->amount;
                $amount = 0;

                $discount_amount = 0;
                if ($transaction->discount_id){
                    $discount = Discount::find($transaction->discount_id);
                    if ($discount){
                        switch ($discount->type){
                            case 1:{
                                $discount_amount = $transaction->discount_amount;
                                break;
                            }
                        }
                    }
                }

                $calendar=$event->calendar()->first();
                if ($amount_visit >= OurBeneficiary($calendar->type ?? null) && date('Y-m-d', strtotime($event->created_at)) >=
                    date('Y-m-d', strtotime(dateChangedBeneficiary())))
                    $amount = $amount_visit - OurBeneficiary($calendar->type ?? null) + $discount_amount;
                else {

                    if ($amount_visit > 400000) {
//                        $amount_pe = ($amount_visit - 89000) / 100;
                        $amount = $amount_visit - 108000;
                    } else {
                        $amount = $amount_visit - 108000;
                    }
                }
                $transactionDr = new TransactionDoctor();

                $transactionDr->user_id = $event->user_id;
                $transactionDr->doctor_id = $event->doctor_id;
                $transactionDr->event_id = $event->id;
                $transactionDr->amount = $amount;
                $transactionDr->save();

                $affiliate = AffiliateTransaction::where('event_id', $event->id)->first();
                if ($affiliate) {
                    $affiliate->status = 1;
                    $affiliate->save();
                }

            }
            return true;
        }
        return false;

    }

    public function logs($event)
    {
        $logs = VisitLog::where('event_id', $event)->paginate(10);
        return view('admin.visit.logs', compact('logs'));

    }

    public function manage()
    {
        $date = new \DateTime();
        $date->modify('-1 hours');
        $min_date = $date->format('Y-m-d H:i:s');
        $date = new \DateTime();
        $date->modify('+1 hours');
        $max_date = $date->format('Y-m-d H:i:s');

        $events = EventReserves::whereDate('reserve_time', Carbon::now()->format('Y-m-d'))
            ->where('visit_status', 'not_end')
            ->where('reserve_time', '>=', $min_date)
            ->where('reserve_time', '<=', $max_date)
            ->get();

        return view('admin.visit.manage', compact('events'));
    }

    public function sendPatientInroom(EventReserves $event)
    {
        $doctor = $event->doctor()->first();
        $user = $event->user()->first();

        if ($doctor) {
//            SendSMS::sendTemplateTwo($doctor->mobile, $doctor->family,
//                $event->fa_data, 'UserInRoom');

            $params = array(
                "token" => $doctor->family,
                "token2" => $user->fullname,
                "token3" => $event->fa_data .' - '.$event->time .':00'
            );
            SendSMS::send($doctor->mobile, 'UserInRoom',$params);

            $this->log->createLog($event, auth()->id(), VisitLogEnum::AdminPatientInRoom);
        }
        return redirect()->back()->with(['success' => 'پیام کوتاه با موفقیت ارسال شد']);

    }
}
