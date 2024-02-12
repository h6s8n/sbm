<?php

namespace App\Jobs;

use App\StarRate;
use App\User;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\DB;

class InsertComment implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    private $comments;
    private $doctors;
    public function __construct($comments)
    {
        $this->comments = $comments;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        return;
        $this->doctors  = User::where('approve', 1)->whereIn('status', ['active', 'imported'])
            ->where('doctor_status', 'active')->doesntHave('DoctorEvents')
            ->whereHas('specializations')->whereNotIn('id',StarRate::select('votable_id')
                ->whereIn('flag',[5,6])->get()->toArray())->get();
        try {
            foreach ($this->doctors as $doctor) {
                $index_of_comments = [];
                $number_of_comments = random_int(5, 20);
                for ($i = 0; $i < $number_of_comments; $i++)
                    $index_of_comments[] = random_int(2, 800);
                foreach ($index_of_comments as $index) {
                    $user = User::where('fullname', ' ')->where('approve', 2)->inRandomOrder()->first();
                    $comment = $this->comments[$index];
                    $my_comment = str_replace(array('ن ن ن', 'ت ت ت', 'ننن', 'تتت'), array($doctor->fullname, $doctor->specializations()->first()->name, $doctor->fullname, $doctor->specializations()->first()->name), $comment);
                    $data = [
                        'user_id' => $user->id,
                        'votable_type' => 'App\User',
                        'votable_id' => $doctor->id,
                        'quality' => 5,
                        'cost' => 5,
                        'behaviour' => 5,
                        'overall' => 5,
                        'flag' => 6,
                        'comment' => $my_comment['comment']
                    ];
                    StarRate::create($data);
                }
            }
        }
        catch(\Exception $exception){
        }
    }
}
