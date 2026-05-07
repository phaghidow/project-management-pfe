<?php

namespace App\Http\Controllers;

use App\Events\Task\TaskAssigned;
use App\Events\Task\TaskStatusChanged;
use App\Http\Controllers\Controller;
use App\Models\Task;
use App\Models\User;
use App\Models\Milestone;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $query = Task::visibleFor(auth()->user())
            ->with('milestone.project', 'users');

        if (request()->filled('q')) {
            $search = trim(request()->q);

            $query->where(function ($inner) use ($search) {
                $inner->where('name', 'like', "%{$search}%")
                    ->orWhereHas('milestone.project', function ($projectQuery) use ($search) {
                        $projectQuery->where('name', 'like', "%{$search}%");
                    });
            });
        }

        if (request()->filled('status')) {
            $query->where('status', request()->status);
        }

        $tasks = $query->latest()->paginate(15);

        return view('tasks.index', [
            'tasks' => $tasks,
            'filters' => request()->only(['q', 'status']),
        ]);
    }

    /**
     * Display my assigned tasks with filters and sorting.
     */
    public function myTasks(Request $request)
    {
        $query = Task::visibleFor(auth()->user())
            ->with(['milestone.project', 'users']);

        // Search filter
        if ($request->filled('q')) {
            $search = trim($request->q);

            $query->where(function ($inner) use ($search) {
                $inner->where('name', 'like', "%{$search}%")
                    ->orWhereHas('milestone.project', function ($projectQuery) use ($search) {
                        $projectQuery->where('name', 'like', "%{$search}%");
                    });
            });
        }

        // Status filter
        $status = $request->get('status', 'all');
        if ($status !== 'all') {
            $query->where('status', $status);
        }

        // Sorting - default due_date desc
        $sort = $request->get('sort', 'due_date');
        $dir = $request->get('dir', 'desc');
        if (in_array($sort, ['name', 'due_date', 'created_at'])) {
            $query->orderBy($sort, $dir);
        } else {
            $query->orderBy('due_date', 'desc');
        }

        $tasks = $query->paginate(15);

        return view('tasks.my-tasks', [
            'tasks' => $tasks,
            'q' => $request->get('q', ''),
            'status' => $status,
            'sort' => $sort,
            'dir' => $dir
        ]);
    }

    /**
     * API endpoint for my tasks with filters/sorting/pagination.
     */
    public function apiMyTasks(Request $request)
    {
        $query = Task::visibleFor(auth()->user())
            ->with(['milestone.project', 'users']);

        // Search filter
        if ($request->filled('q')) {
            $search = trim($request->q);

            $query->where(function ($inner) use ($search) {
                $inner->where('name', 'like', "%{$search}%")
                    ->orWhereHas('milestone.project', function ($projectQuery) use ($search) {
                        $projectQuery->where('name', 'like', "%{$search}%");
                    });
            });
        }

        // Status filter
        $status = $request->get('status', 'all');
        if ($status !== 'all') {
            $query->where('status', $status);
        }

        // Project filter (new)
        $projectId = $request->get('project_id');
        if ($projectId) {
            $query->whereHas('milestone.project', function ($q) use ($projectId) {
                $q->where('id', $projectId);
            });
        }

        // Sorting
        $sort = $request->get('sort', 'due_date');
        $dir = $request->get('dir', 'desc');
        if (in_array($sort, ['name', 'due_date', 'created_at'])) {
            $query->orderBy($sort, $dir);
        } else {
            $query->orderBy('due_date', 'desc');
        }

        $tasks = $query->paginate(15);

        return response()->json([
            'data' => $tasks->items(),
            'current_page' => $tasks->currentPage(),
            'last_page' => $tasks->lastPage(),
            'per_page' => $tasks->perPage(),
            'total' => $tasks->total(),
            'q' => $request->get('q', ''),
            'status' => $status,
            'sort' => $sort,
            'dir' => $dir,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $projects = \App\Models\Project::visibleFor(auth()->user())->get();
        $users = \App\Models\User::all();
        
        // Get milestone_id from query parameter if provided
        $milestoneId = request()->query('milestone_id');
        $selectedMilestone = null;
        $selectedProjectId = null;
        
        if ($milestoneId) {
            $selectedMilestone = Milestone::find($milestoneId);
            if ($selectedMilestone) {
                $selectedProjectId = $selectedMilestone->project_id;
            }
        }

        return view('tasks.create', compact('projects', 'users', 'selectedMilestone', 'selectedProjectId'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'milestone_id' => 'required|exists:milestones,id',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'due_date' => 'nullable|date',
            'users' => 'nullable|array',
            'users.*' => 'exists:users,id',
        ]);

        $task = Task::create([
            'name' => $request->name,
            'milestone_id' => $request->milestone_id,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'due_date' => $request->due_date,
            'status' => 'in_progress',
        ]);

        // assign users and dispatch task assigned events
        if ($request->users) {
            $task->users()->attach($request->users);

            foreach ($request->users as $userId) {
                $user = User::find($userId);
                TaskAssigned::dispatch($task, $user, auth()->user());
            }
        }

        // Recalculate project end_date (boot handles, explicit for clarity)
        if ($task->milestone?->project) {
            $task->updateProjectEnd();
        }

        $message = 'Tâche créée et intervenants notifiés';
        if (request()->expectsJson()) {
            return response()->json(['success' => true, 'task' => $task, 'message' => $message])
                ->header('X-Flash-Success', $message);
        }

        return redirect()->route('tasks.index')
            ->with('success', $message);
    }

    /**
     * Display the specified resource.
     */
    public function show(Task $task)
    {
        $task->load('users', 'milestone.project', 'dependencies', 'dependents', 'attachments.user', 'statusHistory.actor', 'auditLogs.actor');

        return view('tasks.show', compact('task'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Task $task)
    {
        $projects = \App\Models\Project::visibleFor(auth()->user())->get();
        $selectedProjectId = $task->milestone?->project_id ?? null;
        $users = User::all();

        return view('tasks.edit', compact('task', 'projects', 'users', 'selectedProjectId'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Task $task)
    {
        if ($task->status === 'validated') {
            return redirect()->route('tasks.index')
                ->with('error', 'Cette tache est deja validee et ne peut plus etre modifiee.');
        }

        $request->validate([
            'name' => 'required',
            'milestone_id' => 'required|exists:milestones,id',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'due_date' => 'nullable|date',
            'status' => 'nullable|in:in_progress,validated',
            'users' => 'nullable|array',
            'users.*' => 'exists:users,id',
        ]);

        $oldStatus = $task->status;
        $newStatus = $request->status ?? 'in_progress';

        $task->update([
            'name' => $request->name,
            'milestone_id' => $request->milestone_id,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'due_date' => $request->due_date,
            'status' => $newStatus,
        ]);

        // Handle user assignments and dispatch events
        $previousUserIds = $task->users()->pluck('users.id')->toArray();
        $newUserIds = $request->users ?? [];
        $task->users()->sync($newUserIds);

        // Dispatch events for newly assigned users
        $newlyAssigned = array_diff($newUserIds, $previousUserIds);
        foreach ($newlyAssigned as $userId) {
            $user = User::find($userId);
            TaskAssigned::dispatch($task, $user, auth()->user());
        }

        // Dispatch status change event if status changed
        if ($oldStatus !== $newStatus) {
            TaskStatusChanged::dispatch($task, $oldStatus, $newStatus, auth()->user());
        }

        // Recalculate project end_date (boot handles, explicit for clarity)
        if ($task->milestone?->project) {
            $task->updateProjectEnd();
        }

        $message = 'Tâche mise à jour';
        if (request()->expectsJson()) {
            return response()->json(['success' => true, 'task' => $task, 'message' => $message])
                ->header('X-Flash-Success', $message);
        }

        return redirect()->route('tasks.index')
            ->with('success', $message);
    }

    public function validateTask(Task $task)
    {
        $this->authorize('validateTask', $task);

        try {
            $oldStatus = $task->status;
            $task->validateTask(auth()->id());
            
            // Dispatch event for status change to validated
            TaskStatusChanged::dispatch($task, $oldStatus, 'validated', auth()->user());

            // Return JSON for AJAX requests, redirect for page requests
            $message = 'Tâche validée avec succès';
            if (request()->expectsJson()) {
                return response()->json(['success' => true, 'message' => $message])
                    ->header('X-Flash-Success', $message);
            }
        } catch (\Exception $exception) {
            if (request()->expectsJson()) {
                return response()->json(['success' => false, 'message' => $exception->getMessage()], 400)
                    ->header('X-Flash-Error', $exception->getMessage());
            }
            return redirect()->route('tasks.index')
                ->with('error', $exception->getMessage());
        }

        return redirect()->route('tasks.index')
            ->with('success', 'Tache validee.');
    }

    public function addDependency(Request $request, Task $task)
    {
        $this->authorize('manageDependencies', $task);

        $request->validate([
            'dependency_id' => 'required|exists:tasks,id'
        ]);

        try {
            $dependency = Task::findOrFail($request->dependency_id);
            $task->addDependency($dependency);
            $message = 'Dépendance ajoutée.';
            if (request()->expectsJson()) {
                return response()->json(['success' => true, 'message' => $message])
                    ->header('X-Flash-Success', $message);
            }
            return redirect()->back()->with('success', $message);
        } catch (\Exception $e) {
            if (request()->expectsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 400)
                    ->header('X-Flash-Error', $e->getMessage());
            }
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function removeDependency(Task $task, Task $dependency)
    {
        $this->authorize('manageDependencies', $task);

        try {
            $task->removeDependency($dependency);
            $message = 'Dépendance supprimée.';
            if (request()->expectsJson()) {
                return response()->json(['success' => true, 'message' => $message])
                    ->header('X-Flash-Success', $message);
            }
            return redirect()->back()->with('success', $message);
        } catch (\Exception $e) {
            if (request()->expectsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 400)
                    ->header('X-Flash-Error', $e->getMessage());
            }
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Task $task)
    {
        $task->delete();
        $message = 'Tâche supprimée';
        if (request()->expectsJson()) {
            return response()->json(['success' => true, 'message' => $message])
                ->header('X-Flash-Success', $message);
        }

        return redirect()->route('tasks.index')
            ->with('success', $message);
    }
}
