<?php

namespace App\Events\SMS;

use App\Model\Visit\DoctorCalender;
use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class SetTimeNotificationEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $calendar;
    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(DoctorCalender $calendar)
    {
        $this->calendar = $calendar;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel('channel-name');
    }
}
