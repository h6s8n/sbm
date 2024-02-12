<?php

namespace App\Jobs\Notifications;

use App\Model\Notification\UserDoctorNotification;
use App\Repositories\v2\ShortMessageService\ShortMessageServiceRepository;
use App\Repositories\v2\ShortMessageService\SMSRepository;
use App\SendSMS;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\DB;

class SendNotificationToDoctorsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

//    private $sms;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
//        $this->sms = new SendSMS();
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $data = UserDoctorNotification::with('doctor')->select(DB::raw('COUNT(user_id) as patients_count,doctor_id'))
            ->where('sent_message', 0)
            ->whereNotIn('doctor_id',[8251,12190,17565,1026])
            ->groupBy('doctor_id')
            ->get();
        /* @var UserDoctorNotification $dt */
        foreach ($data as $dt) {
            if ($dt->doctor()
                ->first()->calenders()
                ->whereDate('data', '>=', Carbon::now()->format('Y-m-d'))
                ->where('reservation', '<', \Illuminate\Support\Facades\DB::raw('capacity'))->first()) {
                continue;
            } elseif ($dt->patients_count >= 5 && $dt->doctor->status == 'active') {
                $params = array(
                    "token" => $dt->patients_count + 10,
                    "token2" => $dt->doctor->fullname
                );

                SendSMS::send($dt->doctor->mobile,'doctorCountVisit' ,$params);
            }
        }

    }
}
