<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class UserNotification extends Notification
{
    use Queueable;

    private $message;
    private $tone;
    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($message,$tone)
    {
        $this->message = $message;
        $this->tone = $tone;
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

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     */
    public function toBroadcast($notifiable)
    {
        return (new BroadcastMessage([
      //      'action'=>$action,
            'message'=>$this->message,
            'notif_type'=>'entrance',
            'tone'=>$this->tone
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
