<?php

namespace App\Listeners;

use App\Events\Project\MemberAssignedToProject;
use App\Models\Project;
use App\Services\NotificationService;

class NotifyOnMemberAssignedToProject
{
    /**
     * Handle the event.
     */
    public function handle(MemberAssignedToProject $event)
    {
        $project = $event->project;
        $member = $event->member;
        $assignedBy = $event->assignedBy;
        $role = $event->role;

        // Notify the assigned member
        NotificationService::send(
            $member->id,
            "Affectation à un projet",
            "Vous avez été affecté au projet '{$project->name}' en tant que {$role}",
            'project_member_assigned',
            Project::class,
            $project->id,
            [
                'project_name' => $project->name,
                'project_manager_id' => $project->user_id,
                'project_manager_name' => $project->user->name,
                'role' => $role,
                'assigned_by_id' => $assignedBy->id,
                'assigned_by_name' => $assignedBy->name,
                'structure_id' => optional($project->user)->structure_id,
            ]
        );

        // Notify project manager
        if ($project->user_id !== $member->id && $project->user_id !== $assignedBy->id) {
            NotificationService::send(
                $project->user_id,
                "Nouveau membre du projet",
                "{$member->name} a été ajouté à votre projet '{$project->name}'",
                'member_assigned_to_my_project',
                Project::class,
                $project->id,
                [
                    'member_id' => $member->id,
                    'member_name' => $member->name,
                    'member_email' => $member->email,
                    'role' => $role,
                ]
            );
        }

        // Notify department head if applicable
        $deptHead = optional($project->user)->structure?->user;
        if ($deptHead && $deptHead->id !== $member->id && $deptHead->id !== $assignedBy->id && $deptHead->isChefDepartement()) {
            NotificationService::send(
                $deptHead->id,
                "Assignation dans le département",
                "{$member->name} assigné au projet '{$project->name}' de {$project->user->name}",
                'member_assigned_in_my_dept',
                Project::class,
                $project->id,
                [
                    'member_id' => $member->id,
                    'member_name' => $member->name,
                    'project_manager_id' => $project->user_id,
                ]
            );
        }
    }
}
