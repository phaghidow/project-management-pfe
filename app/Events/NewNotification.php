<?php

namespace App\Events;

use App\Models\Notification;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;

class NewNotification implements ShouldBroadcast, ShouldQueue
{
    use Dispatchable, InteractsWithSockets, SerializesModels;
    use Queueable;

    /**
     * Number of times the job should be attempted.
     *
     * @var int
     */
    public $tries = 3;

    /**
     * Backoff in seconds before retrying.
     *
     * @var int
     */
    public $backoff = 60;

    public $notification;

    /**
     * Create a new event instance.
     */
    public function __construct(Notification $notification)
    {
        $this->notification = $notification;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('private-user.' . $this->notification->user_id),
        ];
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'id' => $this->notification->id,
            'title' => $this->notification->title,
            'message' => $this->notification->message,
            'type' => $this->notification->type,
            'read_at' => $this->notification->read_at,
            'related_type' => $this->notification->related_type,
            'related_id' => $this->notification->related_id,
            'metadata' => $this->notification->metadata,
        ];
    }
}

