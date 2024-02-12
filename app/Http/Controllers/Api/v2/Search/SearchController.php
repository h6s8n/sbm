<?php

namespace App\Http\Controllers\Api\v2\Search;

use App\Model\Doctor\Specialization;
use App\Repositories\v2\Doctor\DoctorInterface;
use App\Repositories\v2\User\UserInterface;
use App\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class SearchController extends Controller
{
    private $doctor;

    public function __construct(DoctorInterface $doctor)
    {
        $this->doctor = $doctor;
    }

    public function SimilarDoctors()
    {
        $id = \request()->input('id');
        $specialization_id = \request()->input('specialization_id');
        /* @var User $doctor */
        if ($id) {
            $doctor = $this->doctor->find(\request()->input('id'));
            $specializations = $doctor->specializations()->pluck('specialization_id');
        } elseif ($specialization_id) {
            $specializations = Specialization::select(DB::raw('id as specialization_id'))
                ->where('id', $specialization_id)
                ->pluck('specialization_id');
        }
        $similar = User::select('id', 'username', 'picture', 'doctor_nickname', 'fullname', 'job_title')
            ->whereHas('specializations', function ($query) use ($specializations) {
                $query->whereIn('specialization_id', $specializations);
            })
            ->whereHas('NearestTime')
            ->with(['specializations','TimesSorted'])
            ->where('id', '!=', \request()->input('id'))
            ->whereNotIn('id', TestAccount())
            ->where('doctor_status', 'active')
            ->whereIn('status', ['active', 'imported'])
            ->take(\request()->input('user-limit'))->inRandomOrder()->get();

        $similar->map(function ($Users){
            $Users->setRelation('TimesSorted',
                $Users->TimesSorted->take(1));
            return $Users;
        });
        return success_template($similar);
    }
}
