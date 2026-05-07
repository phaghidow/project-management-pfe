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

class NotificationRead implements ShouldBroadcast, ShouldQueue
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

    public $notificationId;
    public $userId;

    /**
     * Create a new event instance.
     */
    public function __construct(int $notificationId, int $userId)
    {
        $this->notificationId = $notificationId;
        $this->userId = $userId;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('private-user.' . $this->userId),
        ];
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'notification_id' => $this->notificationId,
            'read_at' => now()->toISOString(),
        ];
    }
}

