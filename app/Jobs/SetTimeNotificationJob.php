<?php

namespace App\Jobs;

use App\Model\Notification\UserDoctorNotification;
use App\SendSMS;
use App\User;
use Illuminate\Bus\Queueable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class SetTimeNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $records;
    private $date;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Collection $records, string $date)
    {
        $this->records = $records;
        $this->date = $date;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        /* @var UserDoctorNotification $record */
        /* @var User $user */

        foreach ($this->records as $record) {
            $user = $record->user()->firstOrFail();
            if ($user) {
                if (!$user->hasTimeWith($record->doctor_id)) {
                    SendSMS::send($user->mobile, "eventReserve", [
                        "token" =>  $user->fullname=="" ? 'کاربر' : $user->fullname,
                        "token2" => $doctor->fullname,
                        "token3" => $doctor->username,
                    ]);
                    $record->sent_message = 1;
                } else
                    $record->sent_message = 2;
                $record->save();
            }
        }
    }

}
