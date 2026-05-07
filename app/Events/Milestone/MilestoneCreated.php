<?php

namespace App\Events\Milestone;

use App\Models\Milestone;
use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Event fired when milestone is created
 */
class MilestoneCreated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $milestone;
    public $createdBy;

    /**
     * Create a new event instance.
     */
    public function __construct(Milestone $milestone, ?User $createdBy = null)
    {
        $this->milestone = $milestone;
        $this->createdBy = $createdBy ?? auth()->user();
    }
}
