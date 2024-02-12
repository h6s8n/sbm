<?php


namespace App\Repositories\v2\User;

use App\Traites\RepositoryResponseTrait;
use App\User;
use Illuminate\Http\ResponseTrait;

class UserRepository implements UserInterface
{
    use RepositoryResponseTrait;

    public function store($data)
    {
        try {

            $user = User::create($data);
            return $this->SuccessResponse($user);
        }
        catch(\Exception $exception)
        {
            return $this->ErrorTemplate($exception->getMessage());
        }

    }

    public function findByMobile($mobile)
    {
        try {
            $user = User::where('mobile',$mobile)->first();
            if ($user)
                return $this->SuccessResponse($user);
            return $this->ErrorTemplate('User not found');
        }
        catch (\Exception $exception){
            return $this->ErrorTemplate($exception->getMessage());
        }
    }

    public function findByEmail($email)
    {
        try {
            $user = User::where('email',$email)->first();
            if ($user)
                return $this->SuccessResponse($user);
            return $this->ErrorTemplate('User not found');
        }
        catch (\Exception $exception){
            return $this->ErrorTemplate($exception->getMessage());
        }
    }

    public function NotifyMeNewTime($data)
    {
        /* @var User $user*/
        try {
            $user = auth()->user();
            $notification = $user->SetTimeNotification()->create($data);
            return $this->SuccessResponse($notification);
        }
        catch(\Exception $exception)
        {
            return $this->ErrorTemplate($exception->getMessage());
        }
    }

    public function HasSetTimeNotification($doctor_id)
    {
        /* @var User $user*/
        try {
            $user = auth()->user();
            $notification = $user->SetTimeNotification()
                ->where('doctor_id',$doctor_id)
                ->where('sent_message','=',0)
                ->get();
            return $this->SuccessResponse($notification);
        }
        catch(\Exception $exception)
        {
            return $this->ErrorTemplate($exception->getMessage());
        }
    }
}
