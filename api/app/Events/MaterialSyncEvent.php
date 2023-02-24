<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class MaterialSyncEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $userId;
    public $materialIds;

    
    /**
     * construct
     *
     * @param int $userId 管理员id
     * @param array $materialIds 素材id
     */
    public function __construct($userId, $materialIds)
    {
        $this->userId = $userId;
        $this->materialIds = $materialIds;
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
