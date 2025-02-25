<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OrderStatusChanged implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $orderId;
    public $status;
    public $tableId;
    public $isNewOrder;

    public function __construct($orderId, $status, $tableId, $isNewOrder = false)
    {
        $this->orderId = $orderId;
        $this->status = $status;
        $this->tableId = $tableId;
        $this->isNewOrder = $isNewOrder;
    }

    public function broadcastOn(): array
    {
        return [
            new Channel('orders'),
        ];
    }

    public function broadcastWith(): array
    {
        return [
            'orderId' => $this->orderId,
            'status' => $this->status,
            'tableId' => $this->tableId
        ];
    }
}
