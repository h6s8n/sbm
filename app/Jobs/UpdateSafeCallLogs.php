<?php

namespace App\Jobs;

use App\Model\Visit\SafeCall;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use PhpParser\Node\Stmt\TryCatch;

class UpdateSafeCallLogs implements ShouldQueue
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
//        try {

        $calls = SafeCall::whereNull('duration')
            ->where('created_at', '<', Carbon::now()->subMinutes(15)
                ->format('Y-m-d H:i:s'))
            ->whereDate('created_at', '>=', Carbon::now()
                ->subDays(1)->format('Y-m-d'))
            ->get();
        foreach ($calls as $call) {
            $name = $call->name;
            if (0 === strpos(bin2hex($name), 'efbbbf')) {
                $name = substr($name, 3);
            }
            if (is_object(json_decode($name))) {
                $name = json_decode($name, true);
                $name = $name['callfile'];

                $curl = curl_init();
                curl_setopt_array($curl, array(
                    CURLOPT_URL => "https://portal.callmee.org/my/getCDR.php?email=parseh.manager@gmail.com&pass=uCu5EH7%40fVz%40fW6&callfile={$name}",
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    CURLOPT_CUSTOMREQUEST => "GET",
                    CURLOPT_HTTPHEADER => array(
                        "accept: application/json",
                        "content-type: application/json",
                    ),
                ));

                $response = curl_exec($curl);
                $err = curl_error($curl);
                curl_close($curl);

                if ($response) {
                    if (0 === strpos(bin2hex($response), 'efbbbf')) {
                        $response = substr($response, 3);
                    }
                    $response = json_decode($response, true);
                    if (!array_key_exists('result', $response)) {
                        $call->duration = $response['duration'];
                        $call->save();
                    } else {
                        $call->duration = 0;
                        $call->save();
                    }
                }
            } else {
                $call->duration = 0;
                $call->save();
            }
        }
    }
}
