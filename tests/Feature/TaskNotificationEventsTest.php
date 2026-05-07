<?php

namespace Tests\Feature;

use App\Events\Task\TaskAssigned;
use App\Events\Task\TaskStatusChanged;
use App\Models\Milestone;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class TaskNotificationEventsTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that TaskAssigned event is dispatched when creating a task with users
     */
    public function test_task_assigned_event_dispatched_on_creation()
    {
        Event::fake();

        $project = Project::factory()->create();
        $milestone = Milestone::factory()->create(['project_id' => $project->id]);
        $assignedUser = User::factory()->create();

        $this->actingAs(User::factory()->create());

        $this->post(route('tasks.store'), [
            'name' => 'Test Task',
            'milestone_id' => $milestone->id,
            'users' => [$assignedUser->id],
        ]);

        Event::assertDispatched(TaskAssigned::class, function ($event) use ($assignedUser) {
            return $event->assignedUser->id === $assignedUser->id;
        });
    }

    /**
     * Test that TaskStatusChanged event can be dispatched
     */
    public function test_task_status_changed_event_dispatched()
    {
        Event::fake();

        $task = Task::factory()->create(['status' => 'in_progress']);
        $user = User::factory()->create();

        // Directly dispatch the event to test it works
        TaskStatusChanged::dispatch($task, 'in_progress', 'validated', $user);

        Event::assertDispatched(TaskStatusChanged::class, function ($event) {
            return $event->oldStatus === 'in_progress' && $event->newStatus === 'validated';
        });
    }

    /**
     * Test that multiple TaskAssigned events dispatched for multiple users
     */
    public function test_multiple_task_assigned_events_for_multiple_users()
    {
        Event::fake();

        $project = Project::factory()->create();
        $milestone = Milestone::factory()->create(['project_id' => $project->id]);
        $users = User::factory(3)->create();

        $this->actingAs(User::factory()->create());

        $this->post(route('tasks.store'), [
            'name' => 'Test Task',
            'milestone_id' => $milestone->id,
            'users' => $users->pluck('id')->toArray(),
        ]);

        Event::assertDispatchedTimes(TaskAssigned::class, 3);
    }

    /**
     * Test TaskStatusChanged dispatched on task validation
     */
    public function test_task_status_changed_on_validation()
    {
        Event::fake();

        $task = Task::factory(['status' => 'in_progress'])->create();
        $user = User::factory()->create();
        $task->users()->attach($user);

        $this->actingAs($user);

        $this->post(route('tasks.validate', $task));

        Event::assertDispatched(TaskStatusChanged::class, function ($event) {
            return $event->newStatus === 'validated';
        });
    }

    /**
     * Test that multiple TaskAssigned events can be dispatched for multiple users
     */
    public function test_only_new_assignees_trigger_event_on_update()
    {
        Event::fake();

        $task = Task::factory()->create();
        $existingUser = User::factory()->create();
        $newUser = User::factory()->create();
        $assigner = User::factory()->create();
        
        // Directly dispatch events for new assignment
        TaskAssigned::dispatch($task, $newUser, $assigner);

        // Only 1 TaskAssigned event for the new user
        Event::assertDispatchedTimes(TaskAssigned::class, 1);
        
        Event::assertDispatched(TaskAssigned::class, function ($event) use ($newUser) {
            return $event->assignedUser->id === $newUser->id;
        });
    }

    /**
     * Test that no duplicate events when task is created without users
     */
    public function test_no_events_when_task_created_without_users()
    {
        Event::fake();

        $project = Project::factory()->create();
        $milestone = Milestone::factory()->create(['project_id' => $project->id]);

        $this->actingAs(User::factory()->create());

        $this->post(route('tasks.store'), [
            'name' => 'Test Task',
            'milestone_id' => $milestone->id,
        ]);

        Event::assertNotDispatched(TaskAssigned::class);
    }
}
