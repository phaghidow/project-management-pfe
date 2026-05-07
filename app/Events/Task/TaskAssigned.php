<?php

namespace App\Events\Task;

use App\Models\Task;
use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Event fired when a user is assigned to a task
 */
class TaskAssigned
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $task;
    public $assignedUser;
    public $assignedBy;

    /**
     * Create a new event instance.
     */
    public function __construct(Task $task, User $assignedUser, ?User $assignedBy = null)
    {
        $this->task = $task;
        $this->assignedUser = $assignedUser;
        $this->assignedBy = $assignedBy ?? auth()->user();
    }
}
