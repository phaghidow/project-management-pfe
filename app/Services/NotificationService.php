<?php

namespace App\Services;

use App\Events\NewNotification;
use App\Models\Notification;
use App\Models\Task;
use App\Models\Project;
use App\Models\User;
use Carbon\Carbon;

class NotificationService
{
    public static function send(
        $userId, 
        $title, 
        $message, 
        $type,
        $relatedType = null,
        $relatedId = null,
        $metadata = []
    ) {
        // Anti-duplication: Skip if similar notification sent recently and unacknowledged
        $recentExists = Notification::where('user_id', $userId)
            ->where('type', $type)
            ->where('related_id', $relatedId)
            ->recentlySent(1)
            ->unacknowledged()
            ->exists();

        if ($recentExists) {
            return null; // Skip duplicate
        }

        try {
            $notification = Notification::create([
                'user_id' => $userId,
                'title' => $title,
                'message' => $message,
                'type' => $type,
                'related_type' => $relatedType,
                'related_id' => $relatedId,
                'metadata' => $metadata,
                'sent_at' => now(),
            ]);

            // Dispatch real-time event
            NewNotification::dispatch($notification);

            return $notification;
        } catch (\Illuminate\Database\QueryException $e) {
            // Ignore unique constraint violation (dedup)
            return null;
        }
}

    /**
     * Send deadline alert for task (with dedup)
     */
    /**
     * Send deadline alert for task (with dedup)
     */
    public static function sendDeadlineAlert(Task $task, User $user, string $type = 'task_due_soon')
    {
        $daysLeft = $task->due_date ? $task->due_date->diffInDays(now(), false) : null;

        $titles = [
            'task_due_soon' => '⚠️ Échéance proche',
            'task_overdue' => '🚨 Tâche en retard !',
        ];

        $messages = [
            'task_due_soon' => "La tâche '{$task->name}' expire dans " . max(0, $daysLeft) . " jours. Vérifiez le statut.",
            'task_overdue' => "La tâche '{$task->name}' est en retard de " . abs($daysLeft) . " jours ! Action requise.",
        ];

        $title = $titles[$type] ?? 'Alerte tâche';
        $message = $messages[$type] ?? 'Notification concernant votre tâche.';

        return self::send(
            $user->id,
            $title,
            $message,
            $type,
            'task',
            $task->id,
            ['project_id' => $task->milestone?->project_id ?? null, 'days_left' => $daysLeft]
        );
    }

    // Convenience methods for business rules
    public static function taskAssigned(Task $task, $userId)
    {
        self::send(
            $userId,
            'Tâche assignée',
            "Vous avez été assigné à la tâche '{$task->name}'",
            'task_assigned',
            'task',
            $task->id,
            ['project_id' => $task->milestone->project_id]
        );
    }

    public static function projectReadyForReview(Project $project, $userId)
    {
        self::send(
            $userId,
            'Projet prêt pour révision',
            "Le projet '{$project->name}' est prêt pour révision (100% progress)",
            'project_ready_review',
            'project',
            $project->id
        );
    }
}

