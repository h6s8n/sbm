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

class RemindTomorrowVisitsToDoctorsJob implements ShouldQueue
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
            'event_reserves.doctor_id', 'users.mobile','users.fullname','users.id','doctor_calenders.id','event_reserves.calender_id')
            ->join('users', 'users.id', 'event_reserves.doctor_id')
            ->join('doctor_calenders','doctor_calenders.id','event_reserves.calender_id')
            ->where('event_reserves.visit_status', 'not_end')
            ->where('doctor_calenders.type','!=',5)
            ->whereDate('event_reserves.reserve_time', '=', Carbon::tomorrow()->format('Y-m-d'))
            ->groupBy('event_reserves.doctor_id')
            ->get();
        foreach ($data as $dt) {
//            $params = array(
//                array(
//                    "Parameter" => "fullname",
//                    "ParameterValue" => $dt->fullname
//                ),
//                array(
//                    "Parameter" => "min",
//                    "ParameterValue" => $dt->min
//                ),
//                array(
//                    "Parameter" => "max",
//                    "ParameterValue" => $dt->max+1
//                ),
//                array(
//                    "Parameter" => "overall",
//                    "ParameterValue" => $dt->overall
//                )
//            );
            $max_time = $dt->max+1;
            SendSMS::sendTemplateTree($dt->mobile, $dt->min, $max_time, $dt->overall, 'remindNextDayVisits');

//            SendSMS::sendTemplateTree('09191344460',$dt->min,$max_time,$dt->overall,'remindNextDayVisits');
//            return 1;
//            $this->sms->template($dt->mobile,$params,38680);
        }
    }
}
