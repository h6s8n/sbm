<?php

namespace App\Http\Controllers\Admin\Partner;

use App\Model\Partners\Insurance;
use App\Model\Partners\Partner;
use App\Model\Partners\PartnerInsurance;
use App\Model\Partners\PartnerService;
use App\Model\Partners\Service;
use App\Repositories\v2\File\FileInterface;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\View\View;

class CalendersController extends Controller
{
    private $file;

    public function __construct(FileInterface $file)
    {
        $this->file = $file;

        require(base_path('app/jdf.php'));

    }

    public function doctors($id = null)
    {
        $partner = Partner::where('id', $id)
            ->with(['doctors' => function ($query) {
                $query->select('users.id','name','doctor_nickname', 'family', 'fullname', 'email', 'mobile', 'users.created_at','job_title');
            }])->orderBy('id','ASC')->first();


        return view('admin.partner.doctors', ['partner' => $partner]);
    }

}
