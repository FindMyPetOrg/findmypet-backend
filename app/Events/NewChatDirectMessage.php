<?php

namespace App\Events;

use App\Models\Direct;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NewChatDirectMessage implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     * @param int $sender_id
     * @param int $receiver_id
     */
    public function __construct(private readonly int $sender_id, private readonly int $receiver_id, private readonly Direct $direct)
    {
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel(
                $this->sender_id < $this->receiver_id ?
                    "chat.dm.{$this->sender_id}.{$this->receiver_id}" :
                    "chat.dm.{$this->receiver_id}.{$this->sender_id}"
            ),
        ];
    }

    public function broadcastWith()
    {
        return [
            'id' => $this->direct->id,
            'text' => $this->direct->text,
            'sender' => $this->direct->sender,
            'sender_id' => $this->direct->sender_id,
            'seen' => $this->direct->seen
        ];
    }
}
