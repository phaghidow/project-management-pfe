<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\StructureController;
use App\Http\Controllers\OrganigrammeController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\MilestoneController;
use App\Http\Controllers\CalendarController;
use App\Http\Controllers\CalendarEventController;
use App\Http\Controllers\AttachmentController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\DepartementDashboardController;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return view('welcome');
});



/*
|--------------------------------------------------------------------------
| Authenticated User Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'active_user', 'verified'])->group(function () {

    // Dashboard (adapté par rôle)
    Route::get('/dashboard', function () {
        $user = auth()->user();

        // Redirection automatique pour l'admin
        if ($user->role === 'admin') {
            return redirect()->route('admin.dashboard');
        }

        // Redirection vers le dashboard chef de département
        if ($user->isChefDepartement()) {
            return redirect()->route('departement.dashboard');
        }

        // Redirection vers l'espace membre dédié
        if ($user->isMembre()) {
            return app()->make(MemberController::class)->dashboard();
        }

        // 1. tâches visibles pour le rôle (membre inclus via task_user)
        $tasks = \App\Models\Task::visibleFor($user)

            ->with(['milestone.project', 'attachments.user', 'comments.user'])
            ->get();

        // 2. projets visibles (pour membre : dérivés de ses tâches assignées)
        $projects = \App\Models\Project::visibleFor($user)
            ->with(['user', 'attachments.user'])
            ->get();

        $recentAttachments = $projects->flatMap->attachments
            ->merge($tasks->flatMap->attachments)
            ->sortByDesc('created_at')
            ->take(8)
            ->values();

        $recentComments = $tasks->flatMap->comments
            ->sortByDesc('created_at')
            ->take(8)
            ->values();

        // 3. Available members for chef de projet to assign
        $availableMembers = collect();
        if ($user->isChefProjet()) {
            $availableMembers = \App\Models\User::where('role', 'membre')
                ->where('status', \App\Models\User::STATUS_ACTIVE)
                ->orderBy('name')
                ->get();
        }

        return view('dashboard', compact('projects', 'tasks', 'availableMembers', 'recentAttachments', 'recentComments'));
    })->name('dashboard');


});

Route::middleware(['auth', 'active_user'])->group(function () {

    // Profil utilisateur
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile');
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile/update', [ProfileController::class, 'update'])->name('profile.update');
    // Notification preferences endpoint removed — preferences are no longer supported

    // Organigramme
    Route::get('/organigramme', [OrganigrammeController::class, 'index'])->name('organigramme.index');
    Route::get('/api/structures/{id}/hierarchy', [OrganigrammeController::class, 'getHierarchy']);

    // Projets
    Route::resource('projects', ProjectController::class);
    Route::get('/my-projects', [ProjectController::class, 'myProjects'])->name('projects.my-projects');
    Route::post('/projects/{project}/close', [ProjectController::class, 'closeProject'])->name('projects.close');
    Route::post('/projects/{project}/assign-members', [ProjectController::class, 'assignMembers'])->name('projects.assign-members');
    Route::delete('/projects/{project}/members/{user}', [ProjectController::class, 'removeMember'])->name('projects.remove-member');
    Route::post('/projects/{project}/restore', [ProjectController::class, 'restore'])->name('projects.restore');
    Route::delete('/projects/{project}/force-delete', [ProjectController::class, 'forceDelete'])->name('projects.force-delete');

    // Rapports de projet
    Route::middleware('can:view,project')->get('/projects/{project}/report', [ReportController::class, 'generate']);

    // Jalons
    Route::resource('milestones', MilestoneController::class);
    Route::get('/api/milestones/by-project/{project}', [MilestoneController::class, 'byProject'])->name('api.milestones.by-project');

    // Tâches
    Route::resource('tasks', TaskController::class);
    Route::get('/my-tasks', [TaskController::class, 'myTasks'])->name('tasks.my-tasks');
    Route::get('/my-tasks/data', function (Request $request) {
        $user = auth()->user();
        if (!$user) {
            return response()->json(['error' => 'Unauthorized', 'data' => []], 401);
        }
        
        $query = \App\Models\Task::visibleFor($user)->with(['milestone.project', 'users']);
        
        if ($request->filled('q')) {
            $search = trim($request->q);
            $query->where(function ($inner) use ($search) {
                $inner->where('name', 'like', "%{$search}%")
                    ->orWhereHas('milestone.project', function ($projectQuery) use ($search) {
                        $projectQuery->where('name', 'like', "%{$search}%");
                    });
            });
        }
        
        $status = $request->get('status', 'all');
        if ($status !== 'all') {
            $query->where('status', $status);
        }
        
        $sort = $request->get('sort', 'due_date');
        $dir = $request->get('dir', 'desc');
        if (in_array($sort, ['name', 'due_date', 'created_at'])) {
            $query->orderBy($sort, $dir);
        } else {
            $query->orderBy('due_date', 'desc');
        }
        
        $tasks = $query->paginate(15);

        $user = auth()->user();

        // Map tasks to arrays and include authorization flags used by the frontend
        $items = collect($tasks->items())->map(function ($task) use ($user) {
            return array_merge($task->toArray(), [
                'can_view' => $user ? $user->can('view', $task) : false,
            ]);
        })->values();

        return response()->json([
            'data' => $items,
            'current_page' => $tasks->currentPage(),
            'last_page' => $tasks->lastPage(),
            'per_page' => $tasks->perPage(),
            'total' => $tasks->total(),
            'q' => $request->get('q', ''),
            'status' => $status,
            'sort' => $sort,
            'dir' => $dir,
        ]);
    })->name('tasks.my-tasks.data');
    Route::get('/api/my-tasks', [TaskController::class, 'apiMyTasks'])->name('api.my-tasks');
    Route::post('/tasks/{task}/validate', [TaskController::class, 'validateTask'])->name('tasks.validate');
    Route::post('/tasks/{task}/dependencies', [TaskController::class, 'addDependency'])->name('tasks.dependencies.add');
    Route::delete('/tasks/{task}/dependencies/{dependency}', [TaskController::class, 'removeDependency'])->name('tasks.dependencies.remove');

    // API: Comments and Attachments for task detail drawer
    Route::get('/api/tasks/{task}/comments', function (\App\Models\Task $task) {
        $user = auth()->user();
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // Check if user can view this task
        if (!$user->can('view', $task)) {
            // Still allow members to see comments on their assigned tasks
            if (!$task->users()->where('users.id', $user->id)->exists()) {
                return response()->json(['error' => 'Forbidden'], 403);
            }
        }

        $comments = $task->comments()
            ->with('user:id,name')
            ->latest()
            ->get();

        return response()->json(['data' => $comments]);
    });

    Route::get('/api/tasks/{task}/attachments', function (\App\Models\Task $task) {
        $user = auth()->user();
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // Check if user can view this task
        if (!$user->can('view', $task)) {
            // Still allow members to see attachments on their assigned tasks
            if (!$task->users()->where('users.id', $user->id)->exists()) {
                return response()->json(['error' => 'Forbidden'], 403);
            }
        }

        $attachments = $task->attachments()
            ->latest()
            ->get(['id', 'name', 'size', 'created_at']);

        return response()->json(['data' => $attachments]);
    });

    // Calendrier
    Route::get('/calendar', [CalendarEventController::class, 'index'])->name('calendar.index');
    Route::get('/api/calendar/events', [CalendarController::class, 'events'])->name('calendar.events');
    Route::get('/calendar/events/{calendarEvent}/edit', [CalendarEventController::class, 'edit'])->name('calendar.manual-events.edit');
    Route::post('/calendar/events', [CalendarEventController::class, 'store'])->name('calendar.manual-events.store');
    Route::put('/calendar/events/{calendarEvent}', [CalendarEventController::class, 'update'])->name('calendar.manual-events.update');
    Route::delete('/calendar/events/{calendarEvent}', [CalendarEventController::class, 'destroy'])->name('calendar.manual-events.destroy');

    // Pièces jointes
    Route::post('/attachments', [AttachmentController::class, 'store'])->name('attachments.store');
    Route::patch('/attachments/{attachment}', [AttachmentController::class, 'update'])->name('attachments.update');
    Route::get('/attachments/{attachment}/download', [AttachmentController::class, 'download'])->name('attachments.download');
    Route::delete('/attachments/{attachment}', [AttachmentController::class, 'destroy'])->name('attachments.destroy');

    // Commentaires
    Route::post('/comments', [CommentController::class, 'store'])->name('comments.store');
    Route::patch('/comments/{comment}', [CommentController::class, 'update'])->name('comments.update');
    Route::delete('/comments/{comment}', [CommentController::class, 'destroy'])->name('comments.destroy');
    Route::get('/gantt', function () {
        $tasks = \App\Models\Task::visibleFor(auth()->user())->with('milestone.project')->get();
        return view('gantt', compact('tasks'));
    })->name('gantt');

    Route::get('/api/tasks-gantt', function () {
        return \App\Models\Task::visibleFor(auth()->user())->get()->map(function ($task) {
            return [
                "id" => $task->id,
                "name" => $task->name,
                "start" => $task->start_date,
                "end" => $task->end_date,
                "progress" => $task->status === 'validated' ? 100 : ($task->status === 'in_progress' ? 50 : 0)
            ];
        });
    });

    // Notifications
    Route::prefix('notifications')->name('notifications.')->group(function () {
        Route::get('/', [NotificationController::class, 'index']);
        Route::get('/count', [NotificationController::class, 'count']);
        Route::post('/read-all', [NotificationController::class, 'readAll']);
        Route::post('/{notification}/read', [NotificationController::class, 'read']);
        Route::post('/role/{role}', [NotificationController::class, 'sendToRole']);
        Route::post('/structure/{structureId}', [NotificationController::class, 'sendToStructure']);
    });

    // Route de test directe pour le dashboard membre
    Route::get('/member/dashboard', [MemberController::class, 'dashboard'])->name('member.dashboard');

    // Dashboard Chef de Département
    Route::get('/departement/dashboard', [DepartementDashboardController::class, 'dashboard'])->name('departement.dashboard');

});

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'active_user', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {

    Route::get('/dashboard', AdminDashboardController::class)->name('dashboard');

    // Structures
    Route::resource('structures', StructureController::class);
    Route::get('/structures-hierarchy', [StructureController::class, 'getHierarchy'])->name('structures.hierarchy');
    Route::get('/structures/{parentId}/children', [StructureController::class, 'getChildren']);
    Route::get('/structures/{id}', [StructureController::class, 'getStructure']);
    Route::post('/structures/check-parent', [StructureController::class, 'checkParent']);

    // Utilisateurs
    Route::resource('users', AdminController::class);
    Route::get('users/{user}', [AdminController::class, 'show'])->name('users.show');
    Route::post('users/{user}/toggle', [AdminController::class, 'toggleStatus'])->name('users.toggle');

});

require __DIR__.'/auth.php';

