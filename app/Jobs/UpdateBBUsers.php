<?php

namespace App\Jobs;

use App\Model\BB\BbUser;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class UpdateBBUsers implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $bb_users = BbUser::where('is_enable',0)
            ->where('updated_at','<',
                Carbon::now()->subHours(2)->format('Y-m-d H:i:s'))
            ->get();
        foreach ($bb_users as $user){
            $user->is_enable=1;
            $user->current_meeting_id = 0;
            $user->save();
        }
    }
}
