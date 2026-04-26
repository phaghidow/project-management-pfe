<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\Milestone;
use App\Models\Project;
use App\Models\CalendarEvent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CalendarController extends Controller
{
    public function index()
    {
        return view('calendar.index');
    }

    public function events(Request $request)
    {
        $user = Auth::user();
        $events = [];

        // Tasks
        $tasks = Task::visibleFor($user)
            ->with('milestone.project')
            ->get();

        foreach ($tasks as $task) {
            $color = match($task->status) {
                'validated' => '#10b981', // green
                'in_progress', 'started' => '#3b82f6', // blue
                'pending' => '#f59e0b', // orange
                default => '#6b7280' // gray
            };

            $events[] = [
                'id' => 'task-' . $task->id,
                'title' => $task->name . ' (' . ($task->milestone?->name ?? 'N/A') . ')',
                'start' => $task->start_date? $task->start_date->format('Y-m-d\TH:i:s') : null,
                'end' => $task->end_date? $task->end_date->format('Y-m-d\TH:i:s') : null,
                'due' => $task->due_date? $task->due_date->format('Y-m-d') : null,
                'backgroundColor' => $color,
                'borderColor' => $color,
                'textColor' => 'white',
                'url' => route('tasks.show', $task),
                'extendedProps' => [
                    'type' => 'task',
                    'status' => $task->status,
                    'project' => $task->milestone?->project?->name,
                ]
            ];
        }

        // Milestones (all-day)
        $milestones = Milestone::visibleFor($user)->get();

        foreach ($milestones as $milestone) {
            $events[] = [
                'id' => 'milestone-' . $milestone->id,
                'title' => '🗸 ' . $milestone->name,
                'allDay' => true,
                'start' => $milestone->due_date->format('Y-m-d'),
                'backgroundColor' => '#8b5cf6', // violet
                'borderColor' => '#8b5cf6',
                'textColor' => 'white',
                'url' => route('milestones.show', $milestone),
                'extendedProps' => [
                    'type' => 'milestone',
                    'project' => $milestone->project?->name,
                ]
            ];
        }

        // Projects
        $projects = Project::visibleFor($user)->with('tasks')->get();

        foreach ($projects as $project) {
            $color = match($project->status) {
                'completed', 'validated' => '#10b981', // green
                'in_progress' => '#3b82f6', // blue
                'pending', 'open' => '#f59e0b', // orange
                'closed' => '#6b7280', // gray
                default => '#6b7280'
            };

            $events[] = [
                'id' => 'project-' . $project->id,
                'title' => '📊 ' . $project->name,
                'start' => $project->tasks->min('start_date')?->format('Y-m-d\TH:i:s') ?? $project->created_at->format('Y-m-d'),
                'end' => $project->end_date ?? $project->tasks->max('end_date')?->format('Y-m-d\TH:i:s'),
                'backgroundColor' => $color,
                'borderColor' => $color,
                'textColor' => 'white',
                'url' => route('projects.show', $project),
                'extendedProps' => [
                    'type' => 'project',
                    'status' => $project->status,
                    'progress' => $project->progress ?? 0,
                    'taskCount' => $project->tasks->count(),
                ]
            ];
        }

        // Manual calendar events
        $calendarEvents = CalendarEvent::visibleFor($user)->get();

        foreach ($calendarEvents as $calendarEvent) {
            $events[] = [
                'id' => 'calendar-event-' . $calendarEvent->id,
                'title' => '🗓 ' . $calendarEvent->title,
                'start' => $calendarEvent->start_date?->format('Y-m-d\TH:i:s'),
                'end' => $calendarEvent->end_date?->format('Y-m-d\TH:i:s'),
                'allDay' => $calendarEvent->all_day,
                'editable' => true,
                'backgroundColor' => $calendarEvent->color ?? '#2563eb',
                'borderColor' => $calendarEvent->color ?? '#2563eb',
                'textColor' => 'white',
                'url' => route('calendar.manual-events.edit', $calendarEvent),
                'extendedProps' => [
                    'type' => 'calendar_event',
                    'dbId' => $calendarEvent->id,
                    'description' => $calendarEvent->description,
                    'owner' => $calendarEvent->user?->name,
                ]
            ];
        }

        return response()->json($events);
    }
}

