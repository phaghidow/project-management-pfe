<?php

namespace App\Listeners;

use App\Events\Milestone\MilestoneCreated;
use App\Models\Milestone;
use App\Services\NotificationService;

class NotifyOnMilestoneCreated
{
    /**
     * Handle the event.
     */
    public function handle(MilestoneCreated $event)
    {
        $milestone = $event->milestone;
        $project = $milestone->project;
        $createdBy = $event->createdBy;

        // Get all project members
        $projectMembers = $project->members()->pluck('user_id');
        $projectMembers = $projectMembers->push($project->user_id)->unique();

        // Notify project members
        foreach ($projectMembers as $memberId) {
            if ($memberId !== $createdBy->id) {
                NotificationService::send(
                    $memberId,
                    "📌 Nouveau jalon créé",
                    "Jalon '{$milestone->name}' ajouté au projet '{$project->name}'",
                    'milestone_created_in_my_project',
                    Milestone::class,
                    $milestone->id,
                    [
                        'milestone_name' => $milestone->name,
                        'project_name' => $project->name,
                        'project_id' => $project->id,
                        'due_date' => $milestone->due_date?->format('Y-m-d'),
                        'created_by_id' => $createdBy->id,
                        'created_by_name' => $createdBy->name,
                    ]
                );
            }
        }

        // Notify department head
        $deptHead = optional($project->user)->structure?->user;
        if ($deptHead && $deptHead->id !== $createdBy->id && $deptHead->isChefDepartement()) {
            NotificationService::send(
                $deptHead->id,
                "📌 Jalon créé dans le département",
                "Jalon '{$milestone->name}' du projet '{$project->name}'",
                'milestone_created_notification',
                Milestone::class,
                $milestone->id,
                [
                    'milestone_name' => $milestone->name,
                    'project_name' => $project->name,
                    'project_id' => $project->id,
                    'project_manager_id' => $project->user_id,
                ]
            );
        }
    }
}
