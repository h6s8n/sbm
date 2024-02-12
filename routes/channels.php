<?php

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.User.{id}', function ($user, $id) {
    return (int)$user->id === (int)$id;
});


Broadcast::channel('chat.{token}', function ($user, $token) {
    $event = \App\Model\Visit\EventReserves::where('token_room', $token)->first();
    if ($event && $user) {
        if ($event->user_id == $user->id || $event->doctor_id == $user->id)
            return [
                'id'=>$user->id,
                'fullname'=>$user->fullname,
                'username'=>$user->username,
                'approve'=>$user->approve
            ];
        return false;
    }
    return false;
});
Broadcast::channel('user.{id}', function ($user, $id) {

//    info(
//        "Broadcast::channel('user.{id}",
//        debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS
//        )
//    );

    return (int)$user->id === (int)$id;
});
Broadcast::channel('public-channel', function ($user) {
    if ($user)
        return $user;
//        return [
//            'id'=>321,
//            'fullname'=>$user->fullname,
//            'username'=>$user->username,
//            'approve'=>$user->approve,
//            'token'=>$user->token
//
//    ];
    return false;
});

