<?php

namespace App\Listeners\SMS;

use App\Events\SMS\SetTimeNotificationEvent;
use App\Jobs\SetTimeNotificationJob;
use App\Model\Notification\UserDoctorNotification;
use App\SendSMS;
use App\User;
use Carbon\Carbon;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class SetTimeNotificationListener implements ShouldQueue
{
    use InteractsWithQueue;
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  SetTimeNotificationEvent  $event
     * @return void
     */
    public function handle(SetTimeNotificationEvent $event)
    {
        /* @var UserDoctorNotification $dt*/
        /* @var User $user*/

        $doctor_id=$event->calendar->user_id;
        $date = $event->calendar->fa_data;

        $data = UserDoctorNotification::where('doctor_id',$doctor_id)
            ->where('sent_message',0)
            ->whereDate('created_at','>=',Carbon::now()->subDays(30)
                ->format('Y-m-d'))
            ->get();

        foreach ($data as $dt) {
            $user = $dt->user()->first();
            if ($user) {
                if (!$user->hasTimeWith($dt->doctor_id)) {
                   
                        
                        SendSMS::send($user->mobile, "eventReserve", [
                            "token" =>  $date,
                            "token2" => $dt->doctor->fullname,
                            "token3" => $dt->doctor->username,
                        ]);




                    $dt->sent_message = 1;
                } else
                    $dt->sent_message = 2;
                $dt->save();
            }
        }
    }
}
