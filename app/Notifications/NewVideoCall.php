<?php

namespace App\Notifications;

use App\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class NewVideoCall extends Notification
{
    use Queueable;

    private $user;
    private $token_room;
    private $link;

    public function __construct(User $user,$token_room,$link)
    {
        $this->user = $user;
        $this->token_room = $token_room;
        $this->link = $link;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['broadcast'];
    }

    public function toBroadcast()
    {
        return (new BroadcastMessage([
            'room_type'=>'bb',
            'client_id'=>$this->user->id,
            'fullname'=>$this->user->fullname,
            'nickname'=>$this->user->doctor_nickname,
            'picture'=>$this->user->picture,
            'event'=>'new_sk_call',
            'room_id'=>$this->token_room,
            'link'=>$this->link,
            'notif_type' => 'NewVideoCallBB'
        ]))->onConnection('sync');
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
