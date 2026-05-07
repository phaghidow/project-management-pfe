<?php

namespace App\Listeners;

use App\Events\Task\TaskAssigned;
use App\Models\Task;
use App\Services\NotificationService;

class NotifyOnTaskAssigned
{
    /**
     * Handle the event.
     */
    public function handle(TaskAssigned $event)
    {
        $task = $event->task;
        $assignedUser = $event->assignedUser;
        $assignedBy = $event->assignedBy;
        $project = $task->milestone?->project;

        // Guard: Ensure we have valid task and project context
        if (!$task || !$assignedUser || !$assignedBy || !$project) {
            return;
        }

            // Notify assigned user
            NotificationService::send(
                $assignedUser->id,
                "📋 Nouvelle tâche assignée",
                "Vous avez été assigné à la tâche '{$task->name}'",
                'task_assigned_to_me',
                Task::class,
                $task->id,
                [
                    'task_name' => $task->name,
                    'milestone_name' => $task->milestone?->name,
                    'milestone_id' => $task->milestone?->id,
                    'assigned_by_id' => $assignedBy->id,
                    'assigned_by_name' => $assignedBy->name,
                    'project_id' => $project->id,
                    'project_name' => $project->name,
                ]
            );

        // Notify project manager (if exists and is not the assignee or assigner)
        if ($project->user_id && $project->user_id !== $assignedUser->id && $project->user_id !== $assignedBy->id) {
                NotificationService::send(
                    $project->user_id,
                    "👥 Nouvel assigné",
                    "Une tâche '{$task->name}' a été assignée dans votre projet '{$project->name}'",
                    'task_assigned_in_my_project',
                    Task::class,
                    $task->id,
                    [
                        'task_id' => $task->id,
                        'task_name' => $task->name,
                        'milestone_name' => $task->milestone?->name,
                        'milestone_id' => $task->milestone?->id,
                        'assigned_by_id' => $assignedBy->id,
                    ]
                );
        }

        // Notify other assignees on the task
        $otherAssignees = $task->users()
            ->where('user_id', '!=', $assignedUser->id)
            ->where('user_id', '!=', $assignedBy->id)
            ->pluck('user_id')
            ->take(5); // Limit to first 5 to avoid spam

        if ($otherAssignees->count() > 0) {
                foreach ($otherAssignees as $assignee) {
                    NotificationService::send(
                        $assignee,
                        "📋 Nouvelle tâche dans vos affectations",
                        "La tâche '{$task->name}' a été assignée à {$assignedUser->name}",
                        'task_assigned_to_me',
                        Task::class,
                        $task->id,
                        [
                            'task_name' => $task->name,
                            'milestone_name' => $task->milestone?->name,
                            'milestone_id' => $task->milestone?->id,
                            'assigned_to_id' => $assignedUser->id,
                            'assigned_to_name' => $assignedUser->name,
                            'assigned_by_id' => $assignedBy->id,
                        ]
                    );
                }
        }
    }
}
