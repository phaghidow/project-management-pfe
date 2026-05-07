<?php

namespace App\Events\Task;

use App\Models\Task;
use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Event fired when task status changes (draft → in_progress → completed → validated)
 */
class TaskStatusChanged
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $task;
    public $oldStatus;
    public $newStatus;
    public $changedBy;

    /**
     * Create a new event instance.
     */
    public function __construct(Task $task, string $oldStatus, string $newStatus, ?User $changedBy = null)
    {
        $this->task = $task;
        $this->oldStatus = $oldStatus;
        $this->newStatus = $newStatus;
        $this->changedBy = $changedBy ?? auth()->user();
    }
}
