<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Queue\SerializesModels;

class TableScanned implements ShouldBroadcastNow
{
    use InteractsWithSockets, SerializesModels;

    public int $tableId;

    /**
     * Create a new event instance.
     */
    public function __construct(int $tableId)
    {
        $this->tableId = $tableId;
    }

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn(): array
    {
        return [
            new Channel('tables'), // Public channel for table events
        ];
    }

    /**
     * The data to send with the broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'message' => "Table {$this->tableId} was scanned",
            'tableId' => $this->tableId,
        ];
    }
}
