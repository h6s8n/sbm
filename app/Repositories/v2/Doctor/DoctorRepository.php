<?php


namespace App\Repositories\v2\Doctor;


use App\Traites\RepositoryResponseTrait;
use App\User;

class DoctorRepository implements DoctorInterface
{
    use RepositoryResponseTrait;

    public function find($id)
    {
        return User::find($id);
    }
    public function update($data)
    {
        /* @var User $user */
        try {
            $user = User::find($data['user_id']);
            $user->fill($data)->save();
            if ($data['specialization_id'])
                $user->specializations()->sync(($data['specialization_id']));
            return $this->SuccessResponse($user);
        } catch (\Exception $exception) {
            return $this->ErrorTemplate($exception->getMessage());
        }
    }

    public function SyncSearchArea($doctor,$data)
    {
     /* @var User $doctor*/
        $doctor->SearchArea()->delete();
        if ($data['items'])
        $doctor->SearchArea()->create($data);
    }
}
