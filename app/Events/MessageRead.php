<?php

namespace App\Events;

use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageRead implements ShouldBroadcastNow
{
    use Dispatchable, SerializesModels;

    public int $readerId;
    public int $senderId;
    public string $readAt;
    public ?int $requestListId;

    public function __construct(int $readerId, int $senderId, string $readAt, ?int $requestListId = null)
    {
        $this->readerId      = $readerId;
        $this->senderId      = $senderId;
        $this->readAt        = $readAt;
        $this->requestListId = $requestListId;
    }

    public function broadcastOn(): array
    {
        return [new PrivateChannel('chat.' . $this->senderId)];
    }

    public function broadcastAs(): string
    {
        return 'message.read';
    }

    // 明確指定廣播的欄位名稱（camelCase，和前端一致）
    public function broadcastWith(): array
    {
        return [
            'readerId'      => $this->readerId,
            'senderId'      => $this->senderId,
            'readAt'        => $this->readAt,
            'requestListId' => $this->requestListId,
        ];
    }
}