<?php

namespace Plugin\PresenceCommunication\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Plugin\PresenceCommunication\Models\ChatMessage;

class ChatMessageSent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public ChatMessage $chatMessage;

    public function __construct(ChatMessage $chatMessage)
    {
        $this->chatMessage = $chatMessage;
    }

    /**
     * Canal privado entre los dos usuarios.
     * Se usa min/max para que ambos usuarios compartan el mismo canal.
     */
    public function broadcastOn(): array
    {
        $userA = min($this->chatMessage->sender_id, $this->chatMessage->recipient_id);
        $userB = max($this->chatMessage->sender_id, $this->chatMessage->recipient_id);

        return [
            new PrivateChannel("chat.{$userA}.{$userB}"),
        ];
    }

    public function broadcastWith(): array
    {
        return [
            'id' => $this->chatMessage->id,
            'sender_id' => $this->chatMessage->sender_id,
            'recipient_id' => $this->chatMessage->recipient_id,
            'message' => $this->chatMessage->message,
            'created_at' => $this->chatMessage->created_at->toISOString(),
            'sender_name' => $this->chatMessage->sender->full_name ?? '',
        ];
    }
}
