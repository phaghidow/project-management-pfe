<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\StructureController;
use App\Http\Controllers\OrganigrammeController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\MilestoneController;
use App\Http\Controllers\CalendarController;
use App\Http\Controllers\CalendarEventController;
use App\Http\Controllers\AttachmentController;
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
            ->with('milestone.project')
            ->get();

        // 2. projets visibles (pour membre : dérivés de ses tâches assignées)
        $projects = \App\Models\Project::visibleFor($user)
            ->with('user')
            ->get();

        return view('dashboard', compact('projects', 'tasks'));
    })->name('dashboard');

});

Route::middleware(['auth', 'active_user'])->group(function () {

    // Profil utilisateur
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile');
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile/update', [ProfileController::class, 'update'])->name('profile.update');

    // Organigramme
    Route::get('/organigramme', [OrganigrammeController::class, 'index'])->name('organigramme.index');
    Route::get('/api/structures', [OrganigrammeController::class, 'getStructures']);
    Route::get('/api/structures/{id}/hierarchy', [OrganigrammeController::class, 'getHierarchy']);

    // Projets
    Route::resource('projects', ProjectController::class);
    Route::get('/my-projects', [ProjectController::class, 'myProjects'])->name('projects.my-projects');
    Route::post('/projects/{project}/close', [ProjectController::class, 'closeProject'])->name('projects.close');
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
    Route::get('/api/my-tasks', [TaskController::class, 'apiMyTasks'])->name('api.my-tasks');
    Route::post('/tasks/{task}/start', [TaskController::class, 'start'])->name('tasks.start');
    Route::post('/tasks/{task}/validate', [TaskController::class, 'validateTask'])->name('tasks.validate');
    Route::post('/tasks/{task}/dependencies', [TaskController::class, 'addDependency'])->name('tasks.dependencies.add');
    Route::delete('/tasks/{task}/dependencies/{dependency}', [TaskController::class, 'removeDependency'])->name('tasks.dependencies.remove');

    // Calendrier
    Route::get('/calendar', [CalendarEventController::class, 'index'])->name('calendar.index');
    Route::get('/api/calendar/events', [CalendarController::class, 'events'])->name('calendar.events');
    Route::get('/calendar/events/{calendarEvent}/edit', [CalendarEventController::class, 'edit'])->name('calendar.manual-events.edit');
    Route::post('/calendar/events', [CalendarEventController::class, 'store'])->name('calendar.manual-events.store');
    Route::put('/calendar/events/{calendarEvent}', [CalendarEventController::class, 'update'])->name('calendar.manual-events.update');
    Route::delete('/calendar/events/{calendarEvent}', [CalendarEventController::class, 'destroy'])->name('calendar.manual-events.destroy');

    // Pièces jointes
    Route::post('/attachments', [AttachmentController::class, 'store'])->name('attachments.store');
    Route::get('/attachments/{attachment}/download', [AttachmentController::class, 'download'])->name('attachments.download');
    Route::delete('/attachments/{attachment}', [AttachmentController::class, 'destroy'])->name('attachments.destroy');

    // Gantt
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
                "progress" => $task->status === 'validated' ? 100 : ($task->status === 'started' ? 50 : 0)
            ];
        });
    });

    // Notifications
    Route::prefix('notifications')->name('notifications.')->group(function () {
        Route::get('/', [NotificationController::class, 'index']);
        Route::get('/count', [NotificationController::class, 'count']);
        Route::post('/{notification}/read', [NotificationController::class, 'read']);
    });

    // Espace membre : commentaires sur tâches
    Route::post('/member/tasks/{task}/comment', [MemberController::class, 'storeComment'])->name('member.tasks.comment');

    // Route de test directe pour le dashboard membre
    Route::get('/member/dashboard', [MemberController::class, 'dashboard'])->name('member.dashboard');

    // Dashboard Chef de Département
    Route::get('/departement/dashboard', [DepartementDashboardController::class, 'dashboard'])->name('departement.dashboard');

    // Suppression de commentaires
    Route::delete('/comments/{comment}', [MemberController::class, 'destroyComment'])->name('comments.destroy');

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
    Route::resource('users', AdminUserController::class);
    Route::get('users/{user}', [AdminUserController::class, 'show'])->name('users.show');
    Route::post('users/{user}/toggle', [AdminUserController::class, 'toggleStatus'])->name('users.toggle');

});

require __DIR__.'/auth.php';

