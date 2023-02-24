<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class FacebookLogEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $type;
    public $adminId;
    public $content;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($type, $adminId, $content)
    {
        $this->type = $type;
        $this->adminId = $adminId;
        $this->content = $content;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel('channel-name');
    }
}
