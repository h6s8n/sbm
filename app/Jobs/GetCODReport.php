<?php

namespace App\Jobs;

use App\Enums\VisitTypeEnum;
use App\Model\Doctor\DoctorContract;
use App\Model\Visit\EventReserves;
use App\Model\Wallet\DoctorWallet;
use App\Repositories\v2\ShortMessageService\SMSRepository;
use App\SendSMS;
use App\Services\Gateways\src\Zibal;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\DB;

class GetCODReport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    private $sms;
    public function __construct()
    {
//        $this->sms = new SMSRepository();
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $wallet_id = '1604881';

//        $terminalIds = ['198552'=>'98127076','3334'=>'98127075'];

        $terminalIds = DoctorContract::where(['category' => 'cod'])->where('terminal_id'  , '!=' , null)->pluck('user_id' , 'terminal_id');

        foreach ($terminalIds as $doctor_id => $terminalId) {
            $contract = DoctorContract::where([
                'user_id' => $doctor_id,
                'terminal_id' => $terminalId,
            ])->orderBy('created_at','DESC')->first();
            $percent = $contract ? $contract->percent : 0.01;

            $gateway = new Zibal();

            $Y = Carbon::now()->subDay(1)->format('Y');
            $m = Carbon::now()->subDay(1)->format('m');
            $d = Carbon::now()->subDay(1)->format('d');
            $date = gregorian_to_jalali($Y,$m,$d);

            $data = $date[0].$date[1].$date[2]. '-' . $contract->terminal_id;

            $result = $gateway->CODInquiry($data,$wallet_id);

            if($result->result == 1 && isset($result->data->data)){
                $doctorWallet = DoctorWallet::where(['doctor_id' => $doctor_id,'transId' => $result->data->data])->first();
                if (!$doctorWallet) {
                    $newTrans = new DoctorWallet();
                    $newTrans->doctor_id = $doctor_id;
                    $newTrans->amount = $result->data->amount;
                    $newTrans->service_wage = $result->data->amount * $percent;
                    $newTrans->transId = $result->data->data;
                    $newTrans->bank_wage = 0;
                    $newTrans->type = 'increase';
                    $newTrans->settlement_type = 'rial';
                    $newTrans->payment_type = 'COD';
                    $newTrans->status = 'paid_increase';
                    $newTrans->paid_at = Carbon::now()->subDay(1)->format('Y-m-d H:i:s');
                    $newTrans->description = $result->data->data . ' گزارش تجمیعی روزانه دستگاه پوز ';

                    $newTrans->save();
                }
            }
        }
    }
}
