<?php

namespace Tests\Feature;

use App\Events\Task\TaskAssigned;
use App\Events\Milestone\MilestoneCreated;
use App\Events\Project\MemberAssignedToProject;
use App\Models\Milestone;
use App\Models\Notification;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationListenersTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that TaskAssigned event creates notifications
     */
    public function test_task_assigned_listener_creates_notifications()
    {
        $task = Task::factory()->create();
        $assignedUser = User::factory()->create();
        $projectManager = $task->milestone->project->user;
        $assignedBy = User::factory()->create();

        // Dispatch the event
        TaskAssigned::dispatch($task, $assignedUser, $assignedBy);

        // Check that notifications were created
        $this->assertDatabaseHas('notifications', [
            'user_id' => $assignedUser->id,
            'type' => 'task_assigned_to_me',
            'related_id' => $task->id,
        ]);

        $this->assertDatabaseHas('notifications', [
            'user_id' => $projectManager->id,
            'type' => 'task_assigned_in_my_project',
            'related_id' => $task->id,
        ]);
    }

    /**
     * Test that MilestoneCreated event notifies project members
     */
    public function test_milestone_created_listener_notifies_members()
    {
        $project = Project::factory()->create();
        $milestone = Milestone::factory()->create(['project_id' => $project->id]);
        
        $member1 = User::factory()->create();
        $member2 = User::factory()->create();
        $project->members()->attach([$member1->id, $member2->id]);

        $createdBy = User::factory()->create();

        // Dispatch event
        MilestoneCreated::dispatch($milestone, $createdBy);

        // Check notifications for members
        $this->assertDatabaseHas('notifications', [
            'user_id' => $member1->id,
            'type' => 'milestone_created_in_my_project',
            'related_id' => $milestone->id,
        ]);

        $this->assertDatabaseHas('notifications', [
            'user_id' => $member2->id,
            'type' => 'milestone_created_in_my_project',
            'related_id' => $milestone->id,
        ]);

        // Check notification for project manager
        $this->assertDatabaseHas('notifications', [
            'user_id' => $project->user_id,
            'type' => 'milestone_created_in_my_project',
            'related_id' => $milestone->id,
        ]);
    }

    /**
     * Test that MemberAssignedToProject notifies the assigned member
     */
    public function test_member_assigned_listener_notifies_member()
    {
        $project = Project::factory()->create();
        $newMember = User::factory()->create();
        $assignedBy = User::factory()->create();

        // Dispatch event
        MemberAssignedToProject::dispatch($project, $newMember, $assignedBy, 'contributor');

        // Check notification for new member
        $this->assertDatabaseHas('notifications', [
            'user_id' => $newMember->id,
            'type' => 'project_member_assigned',
            'related_id' => $project->id,
        ]);

        // Check notification for project manager
        $this->assertDatabaseHas('notifications', [
            'user_id' => $project->user_id,
            'type' => 'member_assigned_to_my_project',
            'related_id' => $project->id,
        ]);
    }

    /**
     * Test notification metadata is properly set
     */
    public function test_notification_contains_proper_metadata()
    {
        $task = Task::factory()->create();
        $assignedUser = User::factory()->create();
        $assignedBy = User::factory()->create();

        TaskAssigned::dispatch($task, $assignedUser, $assignedBy);

        $notification = Notification::where('user_id', $assignedUser->id)
            ->where('type', 'task_assigned_to_me')
            ->first();

        $this->assertNotNull($notification);
        $this->assertEquals($task->name, $notification->metadata['task_name']);
        $this->assertEquals($task->milestone->name, $notification->metadata['milestone_name']);
        $this->assertEquals($assignedBy->id, $notification->metadata['assigned_by_id']);
    }

    /**
     * Test that no duplicate notifications for same user
     */
    public function test_no_duplicate_notifications()
    {
        $task = Task::factory()->create();
        $user = User::factory()->create();

        // Dispatch event twice (simulate duplicate)
        TaskAssigned::dispatch($task, $user);
        TaskAssigned::dispatch($task, $user);

        // Should only have one notification (dedup logic in NotificationService)
        $notifications = Notification::where('user_id', $user->id)
            ->where('type', 'task_assigned_to_me')
            ->where('related_id', $task->id)
            ->count();

        $this->assertLessThanOrEqual(1, $notifications);
    }

    /**
     * Test that creator is not notified about their own actions
     */
    public function test_creator_not_notified_about_own_task_assignment()
    {
        $task = Task::factory()->create();
        $creator = User::factory()->create();

        TaskAssigned::dispatch($task, $creator, $creator);

        // Creator should still get notification (they're the assignee)
        $this->assertDatabaseHas('notifications', [
            'user_id' => $creator->id,
            'type' => 'task_assigned_to_me',
        ]);

        // But should not get duplicate for being the assigner
        $notificationCount = Notification::where('user_id', $creator->id)
            ->where('type', 'task_assigned_to_me')
            ->where('related_id', $task->id)
            ->count();

        $this->assertEquals(1, $notificationCount);
    }
}
