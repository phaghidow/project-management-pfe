<?php

namespace App\Http\Controllers;

use App\Events\Milestone\MilestoneCreated;
use App\Http\Controllers\Controller;
use App\Models\Milestone;
use App\Models\Project;
use Illuminate\Http\Request;

class MilestoneController extends Controller
{
    public function index()
    {
        $milestones = Milestone::visibleFor(auth()->user())
            ->with('project.user')
            ->latest()
            ->get();

        return view('milestones.index', compact('milestones'));
    }

    public function create()
    {
        $projects = Project::visibleFor(auth()->user())->get();

        return view('milestones.create', compact('projects'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'project_id' => 'required|exists:projects,id',
            'due_date' => 'nullable|date|after_or_equal:projects,start_date',
        ]);

        $milestone = Milestone::create($request->all());

        // Dispatch event to notify project members and stakeholders
        MilestoneCreated::dispatch($milestone, auth()->user());

        return redirect()->route('milestones.index')
            ->with('success', 'Jalon créé avec succès');
    }

    public function show(Milestone $milestone)
    {
$milestone->load('project.user', 'tasks.users', 'statusHistory.actor', 'auditLogs.actor');

        return view('milestones.show', compact('milestone'));
    }

    public function edit(Milestone $milestone)
    {
        $projects = Project::visibleFor(auth()->user())->get();

        return view('milestones.edit', compact('milestone', 'projects'));
    }

    public function update(Request $request, Milestone $milestone)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'project_id' => 'required|exists:projects,id',
            'due_date' => 'nullable|date',
        ]);

        $milestone->update($request->all());

        return redirect()->route('milestones.index')
            ->with('success', 'Jalon mis à jour');
    }

    public function destroy(Milestone $milestone)
    {
        $milestone->delete();

        return redirect()->route('milestones.index')
            ->with('success', 'Jalon supprimé');
    }

    public function byProject(Project $project)
    {
        $this->authorize('viewAny', Milestone::class);
        return response()->json($project->milestones);
    }
}


