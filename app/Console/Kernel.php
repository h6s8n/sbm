<?php

namespace App\Console;

use App\Jobs\Notifications\SendNotificationToDoctorsJob;
use App\Jobs\Notifications\SendNotificationToUnderemployedDoctorsJob;
use App\Jobs\RemindTomorrowVisitsToDoctorsJob;
use App\Jobs\RemindNextHourVisitsToDoctorsJob;
use App\Jobs\UpdateBBUsers;
use App\Jobs\GetCODReport;
use App\Jobs\UpdateSafeCallLogs;
use App\Repositories\v2\ShortMessageService\ShortMessageInterface;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Mail\SendQueuedMailable;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
//        $schedule->job(new SendNotificationToDoctorsJob())
//            ->weeklyOn(4,'23:20');

//            ->timezone('Asia/Tehran')
//            ->weekly()
//            ->mondays()
//            ->at('13:55');

//        $schedule->job(new SendNotificationToUnderemployedDoctorsJob())
//            ->weeklyOn(5,'23:30');


        $schedule->job(new RemindTomorrowVisitsToDoctorsJob())
            ->cron('0 23 * * *');
//            ->cron('0 23 * * *');

        $schedule->job(new RemindNextHourVisitsToDoctorsJob())
            ->cron('*/30 * * * *');


        $schedule->job(new UpdateSafeCallLogs())
            ->cron('*/10 * * * *');

        $schedule->job(new UpdateBBUsers())
            ->cron('*/15 * * * *');

    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
