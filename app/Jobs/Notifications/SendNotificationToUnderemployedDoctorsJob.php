<?php

namespace App\Jobs\Notifications;

use App\Model\Notification\UserDoctorNotification;
use App\Repositories\v2\ShortMessageService\ShortMessageInterface;
use App\Repositories\v2\ShortMessageService\ShortMessageServiceRepository;
use App\Repositories\v2\ShortMessageService\SMSRepository;
use App\SendSMS;
use App\User;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class SendNotificationToUnderemployedDoctorsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $sms;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $since = Carbon::now()->subYear(1);
        $doctors = User::withCount(['Waiting' => function($query){
            $query->where('sent_message',0);
        }])->where('approve', 1)
            ->where('doctor_status','active')
            ->whereIn('status',['active','imported'])
            ->whereNotIn('id',TestAccount())
            ->whereDoesntHave('calenders',function ($query){
                $query->whereDate('data','>',Carbon::now()->format('Y-m-d'));
            })
            ->whereHas('calenders',function ($query) use ($since){
                $query->whereDate('data','<=',$since);
                $query->where(function ($where){
                    $where->where('partner_id',0)
                        ->orWhereNull('partner_id');
                });
            })
            ->whereDoesntHave('calenders', function ($query) use ($since) {
                $query->whereDate('data', '>', $since->format('Y-m-d'));
                $query->where(function ($where){
                    $where->where('partner_id',0)
                        ->orWhereNull('partner_id');
                });
            })
            ->get();

        foreach ($doctors as $doctor) {
            if ($doctor->waiting_count < 5) {

                $params = ["token" => $doctor->fullname];

                $has_not_waiting = UserDoctorNotification::where('doctor_id',$doctor->id)->doesntExist();

                if ($doctor->DoctorEvents('end')->count() > 0 && $has_not_waiting) {
                    SendSMS::send($doctor->mobile, 'underemployedDoctors', $params);
                }
            }
        }

    }
}
