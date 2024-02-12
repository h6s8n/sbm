<?php

namespace App\Events;

use App\User;
use App\Model\Visit\Message;
use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class MessageSent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * @var Message
     */
    public $message;
    private $token;

    /**
     * Create a new event instance.
     *
     * @param Message|null $message
     * @param $token
     */
    public function __construct($token,  $message = null)
    {
        $this->message = $message;
        $this->token = $token;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return Channel|array
     */
    public function broadcastOn()
    {
        return new PresenceChannel('chat.' . $this->token);
    }

    public function broadcastWith()
    {
        if ($this->message)
            return ['data' => $this->message];
        return ['data' => [
            'message' => 'End Of Chat'
        ]];
    }
}
