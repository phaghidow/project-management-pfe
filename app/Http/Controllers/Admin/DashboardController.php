<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\Project;
use App\Models\Structure;
use App\Models\Task;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $roleFilter = $request->get('role', 'all');
        $startDate = $request->get('start_date', now()->subDays(30)->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->format('Y-m-d'));

        // Fix stats calculations
        $usersTotal = User::count();
        $usersActive = User::where('status', '!=', 'disabled')->where('status', '!=', 'en_attente')->count();
        $projectsTotal = Project::count();
        $structuresTotal = Structure::count();
        $notificationsTotal = Notification::unread()->count();

        // Fix Task query - no direct user_id, use whereHas for assignees or recent tasks
        $query = Task::whereBetween('created_at', [$startDate, $endDate . ' 23:59:59']);

        if ($roleFilter !== 'all') {
            $query->whereHas('users', function ($q) use ($roleFilter) {
                $q->where('role', $roleFilter);
            });
        }

        $tasksByStatus = [
            'pending' => $query->clone()->where('status', 'pending')->count(),
            'in_progress' => $query->clone()->whereIn('status', ['started', 'in_progress'])->count(),
            'validated' => $query->clone()->where('status', 'validated')->count(),
        ];

        $totalTasks = array_sum($tasksByStatus);
        $projectsProgressAvg = Project::avg('progress') ?? 0;
        $overdueTasks = Task::where('end_date', '<', now())
                           ->where('status', '!=', 'validated')
                           ->count();

        $recentNotifications = Notification::unread()
            ->with('user')
            ->latest()
            ->take(10)
            ->get();

        return view('admin.dashboard', compact(
            'usersTotal',
            'usersActive',
            'projectsTotal',
            'structuresTotal',
            'notificationsTotal',
            'tasksByStatus',
            'totalTasks',
            'projectsProgressAvg',
            'overdueTasks',
            'roleFilter',
            'startDate',
            'endDate',
            'recentNotifications'
        ));
    }
}

