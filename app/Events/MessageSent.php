<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageSent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public string $userName;
    public string $messageContent;
    public int $senderId;
    public int $receiverId;
    public int $messageId;
    public string $time;
    public ?int $requestListId;

    public function __construct(
        string $userName,
        string $messageContent,
        int $senderId,
        int $receiverId,
        int $messageId,
        string $time,
        ?int $requestListId = null,
    ) {
        $this->userName       = $userName;
        $this->messageContent = $messageContent;
        $this->senderId       = $senderId;
        $this->receiverId     = $receiverId;
        $this->messageId      = $messageId;
        $this->time           = $time;
        $this->requestListId  = $requestListId;
    }

    // 廣播到接收者的私人頻道
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('chat.' . $this->receiverId),
        ];
    }

    public function broadcastAs(): string
    {
        return 'message.sent';
    }
}