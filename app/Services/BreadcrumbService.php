<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Models\Project;
use App\Models\Milestone;
use App\Models\Task;

class BreadcrumbService
{
    public function __construct(
        private Request $request
    ) {}

    public function getBreadcrumbs(): array
    {
        $routeName = $this->request->route()?->getName();
        $breadcrumbs = [];

        if (!$routeName) {
            return $breadcrumbs;
        }

        return match($routeName) {
            'dashboard' => [
                ['label' => 'Dashboard', 'url' => route('dashboard'), 'current' => true]
            ],

            'projects.index' => [
                ['label' => 'Dashboard', 'url' => route('dashboard')],
                ['label' => 'Projets', 'url' => route('projects.index'), 'current' => true]
            ],

            'projects.show' => $this->projectBreadcrumb($this->request->route('project')),

            'milestones.index' => [
                ['label' => 'Dashboard', 'url' => route('dashboard')],
                ['label' => 'Jalons', 'url' => route('milestones.index'), 'current' => true]
            ],

            'milestones.show' => $this->milestoneBreadcrumb($this->request->route('milestone')),

            'tasks.index' => [
                ['label' => 'Dashboard', 'url' => route('dashboard')],
                ['label' => 'Tâches', 'url' => route('tasks.index'), 'current' => true]
            ],

            'tasks.show' => $this->taskBreadcrumb($this->request->route('task')),

            'notifications.index' => [
                ['label' => 'Dashboard', 'url' => route('dashboard')],
                ['label' => 'Notifications', 'url' => route('notifications.index'), 'current' => true]
            ],

            default => []
        };
    }

    private function projectBreadcrumb(Project $project): array
    {
        return [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'Projets', 'url' => route('projects.index')],
            ['label' => $project->name, 'url' => route('projects.show', $project), 'current' => true]
        ];
    }

    private function milestoneBreadcrumb(Milestone $milestone): array
    {
        return [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'Projets', 'url' => route('projects.index')],
            ['label' => $milestone->project->name, 'url' => route('projects.show', $milestone->project)],
            ['label' => 'Jalons', 'url' => route('milestones.index')],
            ['label' => $milestone->name, 'url' => route('milestones.show', $milestone), 'current' => true]
        ];
    }

    private function taskBreadcrumb(Task $task): array
    {
        return [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'Projets', 'url' => route('projects.index')],
            ['label' => $task->milestone->project->name, 'url' => route('projects.show', $task->milestone->project)],
            ['label' => 'Jalon: ' . $task->milestone->name, 'url' => route('milestones.show', $task->milestone)],
            ['label' => $task->name, 'url' => route('tasks.show', $task), 'current' => true]
        ];
    }
}

