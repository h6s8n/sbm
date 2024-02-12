<?php

namespace App\Jobs;

use App\Enums\VisitTypeEnum;
use App\Model\Visit\EventReserves;
use App\Repositories\v2\ShortMessageService\SMSRepository;
use App\SendSMS;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\DB;

class RemindNextHourVisitsToDoctorsJob implements ShouldQueue
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
        $data = EventReserves::select(DB::raw('min(event_reserves.time) as min, MAX(event_reserves.time) as max, COUNT(event_reserves.id) as overall'),
            'event_reserves.doctor_id', 'users.mobile','users.fullname','users.id','doctor_calenders.type as ctype','doctor_calenders.id','event_reserves.calender_id')
            ->join('users', 'users.id', 'event_reserves.doctor_id')
            ->join('doctor_calenders','doctor_calenders.id','event_reserves.calender_id')
            ->where('event_reserves.visit_status', 'not_end')
            ->where('doctor_calenders.type','!=',5)
            ->where('event_reserves.time','=',Carbon::now()->addHour()->format('H'))
            ->whereDate('event_reserves.reserve_time', '=', Carbon::today()->format('Y-m-d'))
            ->groupBy('event_reserves.doctor_id')
            ->get();
        foreach ($data as $dt) {

            $mapping = [
                1 => '۱ تا ۳ ساعت',
                2 => 'حداکثر ۱۵ دقیقه',
                3 => '۲۴ تا ۴۸ ساعت',
                4 => '۱ تا ۳ ساعت'
            ];

            $time_to_visit = $mapping[$dt->ctype];

            $max_time = $dt->max+1;
            $params = array(
                "token" => $max_time,
                "token2" => $dt->overall,
                "token3" => $time_to_visit,
                "token10" =>  $dt->min,
            );
            SendSMS::send($dt->mobile, 'remindNextHourVisits', $params);
//            SendSMS::sendTemplateTree($dt->mobile, $dt->min, $max_time, $dt->overall, 'remindNextHourVisits');

        }
    }
}
