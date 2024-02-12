<?php

namespace App\Jobs;

use App\Model\BB\BbUser;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class AddBBUsers implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    private $key;
    public function __construct()
    {
        $this->key = "MFwwDQYJKoZIhvcNAQEBBQADSwAwSAJBANY03fd4M5NO06J27q/18w3uBThFKecJ";

    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        for ($i=1;$i<=50;$i++) {
            $username = 'sbmvideouser'.$i;
            $ch = curl_init("https://register.abrish.ir/service.php");
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            $apiKey = $this->key;
            $paramsList = array();
            $paramsList['id'] = 277;
            $paramsList['userid'] = 0;
            $paramsList['firstname'] = 'name'.$i;
            $paramsList['lastname'] = 'lastname'.$i;
            $paramsList['isenable'] = 1;
            $paramsList['username'] = $username;
            $paramsList['gender'] = 1;
            $paramsList['specialaccess'] = 1;
            $paramsList['password'] = str_random(5);
            $paramsList['serviceusername'] = 'm.aliyari1990@gmail.com';
            $paramsList['mod'] = 'Organization';
            $paramsList['action'] = 'postUser';

            $params = array();
            $params['params'] = json_encode($paramsList);
            $params['checksum'] = sha1(json_encode($paramsList) . $apiKey);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
            $res = curl_exec($ch);
            $res = json_decode($res, true);
            BbUser::create([
                'username'=>$username
            ]);
        }
    }
}
