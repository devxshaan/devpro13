<?php
namespace App\Events;


use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Events\Dispatchable;

class StatusUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets;

    public string $modelName; // 'orders', 'payments', etc.
    public array $payload;
    public int $userId;

    public function __construct(Model $model)
    {
        // 'Order' -> 'orders', 'Payment' -> 'payments'
        $this->modelName = strtolower(class_basename($model)) . 's';
        $this->userId    = $model->user_id;
        $this->payload   = [
            'key'    => $model->getKey(),
            'status' => $model->status,
        ];
    }

    public function broadcastOn(): \Illuminate\Broadcasting\PrivateChannel
    {
        // Ensure yahan koi object null na ho
        return new \Illuminate\Broadcasting\PrivateChannel("orders." . $this->userId);
    }

    public function broadcastAs(): string
    {
        return 'status.updated';
    }

    public function broadcastWith(): array
    {
        return [
            'order_key' => $this->payload['key'], 
            'status'    => $this->payload['status'],
        ];
    }
}