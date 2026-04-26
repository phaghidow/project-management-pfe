<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\Milestone;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DepartementDashboardController extends Controller
{
    public function dashboard(): View
    {
        $user = auth()->user();
        $structureId = $user->structure_id;

        // Récupère tous les IDs de la structure et de ses descendants
        $structureIds = Project::getStructureTreeIds($structureId);

        // Projets du département (et sous-structures)
        $projects = Project::whereHas('user', function ($q) use ($structureIds) {
            $q->whereIn('structure_id', $structureIds);
        })
            ->with(['user.structure', 'milestones'])
            ->latest()
            ->get();

        // Tâches du département
        $tasks = Task::whereHas('milestone.project.user', function ($q) use ($structureIds) {
            $q->whereIn('structure_id', $structureIds);
        })
            ->with(['milestone.project', 'users'])
            ->latest()
            ->get();

        // Jalons du département
        $milestones = Milestone::whereHas('project.user', function ($q) use ($structureIds) {
            $q->whereIn('structure_id', $structureIds);
        })
            ->with('project')
            ->orderBy('due_date')
            ->get();

        // Membres rattachés à la structure (et sous-structures)
        $members = User::whereIn('structure_id', $structureIds)
            ->with('structure')
            ->orderBy('name')
            ->get();

        // Statistiques rapides
        $stats = [
            'projects_count' => $projects->count(),
            'members_count' => $members->count(),
            'tasks_count' => $tasks->count(),
            'milestones_count' => $milestones->count(),
            'progress_avg' => $projects->avg('progress') ?? 0,
            'tasks_validated' => $tasks->where('status', 'validated')->count(),
            'tasks_pending' => $tasks->whereIn('status', ['pending', 'started', 'in_progress'])->count(),
        ];

        return view('departement.dashboard', compact(
            'projects',
            'tasks',
            'milestones',
            'members',
            'stats',
            'structureIds'
        ));
    }
}

