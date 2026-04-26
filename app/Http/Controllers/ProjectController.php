<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Project;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    public function index(Request $request)
    {
        // Demo toast notifications
        if ($request->has('demo') && $request->demo === 'success') {
            session()->flash('success', '✅ Démo succès !');
        }
        if ($request->has('demo') && $request->demo === 'error') {
            session()->flash('error', '❌ Démo erreur !');
        }
        if ($request->has('demo') && $request->demo === 'warning') {
            session()->flash('warning', '⚠️ Démo avertissement !');
        }
        if ($request->has('demo') && $request->demo === 'info') {
            session()->flash('info', 'ℹ️ Démo info !');
        }

        $query = Project::visibleFor(auth()->user())->with('user');

        $query->when($request->filled('q'), function ($q) use ($request) {
            $search = trim($request->q);

            $q->where(function ($inner) use ($search) {
                $inner->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($userQuery) use ($search) {
                        $userQuery->where('name', 'like', "%{$search}%");
                    });
            });
        });

        // Filters
        $query->when($request->filled('status'), function ($q) use ($request) {
            $q->where('status', $request->status);
        });

        $query->when($request->filled('user_id'), function ($q) use ($request) {
            $q->where('user_id', $request->user_id);
        });

        $query->when($request->filled('start_date'), function ($q) use ($request) {
            $q->whereDate('start_date', '>=', $request->start_date);
        });

        $query->when($request->filled('end_date'), function ($q) use ($request) {
            $q->whereDate('end_date', '<=', $request->end_date);
        });

        $projects = $query->latest()->paginate(12);

        $filters = $request->only(['q', 'status', 'user_id', 'start_date', 'end_date']);

        return view('projects.index', compact('projects', 'filters'));
    }

    public function myProjects(Request $request)
    {
        $query = Project::where('user_id', auth()->id())->with('user');

        $query->when($request->filled('q'), function ($q) use ($request) {
            $search = trim($request->q);

            $q->where(function ($inner) use ($search) {
                $inner->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        });

        // Filters (status, dates only - no user_id since my projects)
        $query->when($request->filled('status'), function ($q) use ($request) {
            $q->where('status', $request->status);
        });

        $query->when($request->filled('start_date'), function ($q) use ($request) {
            $q->whereDate('start_date', '>=', $request->start_date);
        });

        $query->when($request->filled('end_date'), function ($q) use ($request) {
            $q->whereDate('end_date', '<=', $request->end_date);
        });

        $projects = $query->latest()->paginate(12);

        $filters = $request->only(['q', 'status', 'start_date', 'end_date']);

        return view('projects.my-projects', compact('projects', 'filters'));
    }

    public function create()
    {
        return view('projects.create');
    }    

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'description' => 'nullable',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
        ]);

        Project::create([
            'name' => $request->name,
            'description' => $request->description,
            'user_id' => auth()->id(),
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'status' => 'draft',
        ]);

        return redirect()->route('projects.index')
            ->with('success', 'Projet créé avec succès');
    }

    public function show(Project $project)
    {
$project->load(['milestones.tasks.users', 'tasks.users', 'attachments.user', 'statusHistory.actor', 'auditLogs.actor']);

        return view('projects.show', compact('project'));
    } 
    
    public function edit(Project $project)
    {
        return view('projects.edit', compact('project'));
    }

    public function update(Request $request, Project $project)
    {
        $project->update([
            'name' => $request->name,
            'description' => $request->description,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
        ]);

        return redirect()->route('projects.index')
            ->with('success', 'Projet mis à jour');
    }

    public function restore($project)
    {
        $project = Project::withTrashed()->findOrFail($project);
        $this->authorize('restore', $project);
        $project->restore();
        return redirect()->back()->with('success', 'Projet restauré');
    }

    public function forceDelete($project)
    {
        $project = Project::withTrashed()->findOrFail($project);
        $this->authorize('forceDelete', $project);
        $project->forceDelete();
        return redirect()->back()->with('success', 'Projet supprimé définitivement');
    }

    public function destroy(Project $project)
    {
        $project->delete();
        return redirect()->route('projects.index')
            ->with('success', 'Projet supprimé (soft)');
    }

    public function closeProject(Project $project)
    {
        $this->authorize('closeProject', $project);

        try {
            $project->closeProject();
            return redirect()->back()->with('success', 'Projet clôturé avec succès.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }
}

