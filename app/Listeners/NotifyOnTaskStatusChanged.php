<?php

namespace App\Listeners;

use App\Events\Task\TaskStatusChanged;
use App\Models\Task;
use App\Services\NotificationService;

class NotifyOnTaskStatusChanged
{
    /**
     * Handle the event.
     */
    public function handle(TaskStatusChanged $event)
    {
        $task = $event->task;
        $oldStatus = $event->oldStatus;
        $newStatus = $event->newStatus;
        $changedBy = $event->changedBy;
        $project = $task->milestone->project;

        // Define status change messages
        $statusMessages = [
            'in_progress' => [
                'title' => '🔄 Tâche commencée',
                'message' => "La tâche '{$task->name}' est passée en cours de réalisation",
            ],
            'completed' => [
                'title' => '✅ Tâche complétée',
                'message' => "La tâche '{$task->name}' a été marquée comme complétée",
            ],
            'validated' => [
                'title' => '✔️ Tâche validée',
                'message' => "La tâche '{$task->name}' a été validée et fermée",
            ],
            'draft' => [
                'title' => '📝 Tâche créée',
                'message' => "La tâche '{$task->name}' a été créée",
            ],
        ];

        $messageData = $statusMessages[$newStatus] ?? [
            'title' => 'Changement de statut',
            'message' => "Le statut de la tâche '{$task->name}' a changé",
        ];

        // Notify all assigned users
        $assignedUserIds = $task->users()->pluck('users.id');

        foreach ($assignedUserIds as $userId) {
            if ($userId !== $changedBy->id) {
                NotificationService::send(
                    $userId,
                    $messageData['title'],
                    $messageData['message'],
                    "task_status_changed_{$newStatus}",
                    Task::class,
                    $task->id,
                    [
                        'old_status' => $oldStatus,
                        'new_status' => $newStatus,
                        'changed_by_id' => $changedBy->id,
                        'changed_by_name' => $changedBy->name,
                        'task_name' => $task->name,
                    ]
                );
            }
        }

        // Notify project manager
        if ($project->user_id !== $changedBy->id && !$assignedUserIds->contains($project->user_id)) {
            NotificationService::send(
                $project->user_id,
                $messageData['title'],
                $messageData['message'],
                "task_status_changed_{$newStatus}",
                Task::class,
                $task->id,
                [
                    'old_status' => $oldStatus,
                    'new_status' => $newStatus,
                    'changed_by_id' => $changedBy->id,
                ]
            );
        }
    }
}
