<?php

namespace App\Events\Project;

use App\Models\Project;
use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Event fired when a team member is assigned to a project
 */
class MemberAssignedToProject
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $project;
    public $member;
    public $assignedBy;
    public $role;

    /**
     * Create a new event instance.
     */
    public function __construct(Project $project, User $member, ?User $assignedBy = null, ?string $role = null)
    {
        $this->project = $project;
        $this->member = $member;
        $this->assignedBy = $assignedBy ?? auth()->user();
        $this->role = $role ?? 'contributor';
    }
}
