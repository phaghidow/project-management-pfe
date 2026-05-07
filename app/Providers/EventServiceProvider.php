<?php

namespace App\Providers;

use App\Events\Task\TaskAssigned;
use App\Events\Task\TaskStatusChanged;
use App\Events\Project\MemberAssignedToProject;
use App\Events\Milestone\MilestoneCreated;
use App\Listeners\NotifyOnTaskAssigned;
use App\Listeners\NotifyOnTaskStatusChanged;
use App\Listeners\NotifyOnMemberAssignedToProject;
use App\Listeners\NotifyOnMilestoneCreated;
use App\Listeners\BroadcastNewNotification;
use App\Listeners\BroadcastReadNotification;
use App\Events\NewNotification;
use App\Events\NotificationRead;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        // Task Events
        TaskAssigned::class => [
            NotifyOnTaskAssigned::class,
        ],
        TaskStatusChanged::class => [
            NotifyOnTaskStatusChanged::class,
        ],

        // Project Events
        MemberAssignedToProject::class => [
            NotifyOnMemberAssignedToProject::class,
        ],

        // Milestone Events
        MilestoneCreated::class => [
            NotifyOnMilestoneCreated::class,
        ],

        // Notification Events (Broadcast)
        NewNotification::class => [
            BroadcastNewNotification::class,
        ],
        NotificationRead::class => [
            BroadcastReadNotification::class,
        ],
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        //
    }
}
