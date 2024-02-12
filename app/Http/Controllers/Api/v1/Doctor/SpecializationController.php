<?php

namespace App\Http\Controllers\Api\v1\Doctor;

use App\Repositories\v1\Doctor\Specialization\SpecializationInterface;
use http\Env\Response;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class SpecializationController extends Controller
{
    private $specialization;

    public function __construct(SpecializationInterface $specialization)
    {
        $this->specialization = $specialization;
        ob_end_clean();
    }

    public function withCalender()
    {
        $data = $this->specialization->withCalender();
        if ($data)
            return success_template($data);
        return error_template('دریافت اطلاعات با مشکل مواجه شده است');
    }

    public function withUsers()
    {
        //ob_start('ob_gzhandler');
        $data = cache()->remember('specializationWithUsers_',3600,function() {
            return$this->specialization->withUsers();
        });
        if ($data)
            return success_template($data);
        return error_template('دریافت اطلاعات با مشکل مواجه شده است');
    }
}
