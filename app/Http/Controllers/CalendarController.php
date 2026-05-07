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

        // Log the request for debugging
        \Log::debug('Calendar API called', [
            'user_id' => $user->id,
            'user_role' => $user->role,
            'start' => $request->query('start'),
            'end' => $request->query('end'),
        ]);

        // Parse optional range filters (FullCalendar provides start/end)
        $start = $request->query('start');
        $end = $request->query('end');

        try {
            $rangeStart = $start ? new \Carbon\Carbon($start) : null;
            $rangeEnd = $end ? new \Carbon\Carbon($end) : null;
        } catch (\Throwable $e) {
            $rangeStart = null;
            $rangeEnd = null;
        }

        // Validate range size to avoid expensive queries
        $maxDays = 365;
        if ($rangeStart && $rangeEnd && $rangeEnd->diffInDays($rangeStart) > $maxDays) {
            return response()->json(['error' => 'Range too large. Max ' . $maxDays . ' days.'], 422);
        }

        // Tasks
        $tasksQuery = Task::visibleFor($user)
            ->select('tasks.*')
            ->distinct()
            ->with(['milestone.project.user', 'users']);

        if ($rangeStart && $rangeEnd) {
            // Tasks that overlap the requested window
            $tasksQuery->where(function ($q) use ($rangeStart, $rangeEnd) {
                $q->where(function ($q2) use ($rangeStart, $rangeEnd) {
                    $q2->whereNotNull('start_date')->whereNotNull('end_date')
                        ->where('start_date', '<=', $rangeEnd)
                        ->where('end_date', '>=', $rangeStart);
                })->orWhere(function ($q3) use ($rangeStart, $rangeEnd) {
                    // fallback: tasks with due_date in range
                    $q3->whereNotNull('tasks.due_date')
                        ->whereBetween('tasks.due_date', [$rangeStart->toDateString(), $rangeEnd->toDateString()]);
                });
            });
        }

        $tasks = $tasksQuery->get();

        \Log::debug('Calendar tasks fetched', [
            'count' => $tasks->count(),
            'user_id' => $user->id,
        ]);

        foreach ($tasks as $task) {
            $color = match($task->status) {
                'validated' => '#10b981', // green
                'in_progress' => '#3b82f6', // blue
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
                    'responsible' => $task->users->pluck('name')->filter()->first() ?? $task->milestone?->project?->user?->name,
                ]
            ];
        }

        // Milestones (all-day)
        $milestonesQuery = Milestone::visibleFor($user)
            ->select('milestones.*')
            ->distinct()
            ->with('project.user');
        if ($rangeStart && $rangeEnd) {
            $milestonesQuery->whereBetween('milestones.due_date', [$rangeStart->toDateString(), $rangeEnd->toDateString()]);
        }

        $milestones = $milestonesQuery->get();

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
                    'responsible' => $milestone->project?->user?->name,
                ]
            ];
        }

        // Projects
        $projectsQuery = Project::visibleFor($user)
            ->select('projects.*')
            ->distinct()
            ->with(['tasks', 'user']);
        if ($rangeStart && $rangeEnd) {
            // include projects that have tasks overlapping the window
            $projectsQuery->whereHas('tasks', function ($q) use ($rangeStart, $rangeEnd) {
                $q->where(function ($q2) use ($rangeStart, $rangeEnd) {
                    $q2->whereNotNull('start_date')->whereNotNull('end_date')
                        ->where('start_date', '<=', $rangeEnd)
                        ->where('end_date', '>=', $rangeStart);
                })->orWhereBetween('tasks.due_date', [$rangeStart->toDateString(), $rangeEnd->toDateString()]);
            });
        }

        $projects = $projectsQuery->get();

        foreach ($projects as $project) {
            $color = match($project->status) {
                'completed', 'validated' => '#10b981', // green
                'in_progress' => '#3b82f6', // blue
                'open' => '#f59e0b', // orange
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
                    'responsible' => $project->user?->name,
                ]
            ];
        }

        // Manual calendar events
        $calendarEventsQuery = CalendarEvent::visibleFor($user)
            ->select('calendar_events.*')
            ->distinct()
            ->with('user');
        if ($rangeStart && $rangeEnd) {
            $calendarEventsQuery->where(function ($q) use ($rangeStart, $rangeEnd) {
                $q->where(function ($q2) use ($rangeStart, $rangeEnd) {
                    $q2->whereNotNull('start_date')->whereNotNull('end_date')
                        ->where('start_date', '<=', $rangeEnd)
                        ->where('end_date', '>=', $rangeStart);
                })->orWhereBetween('start_date', [$rangeStart->toDateString(), $rangeEnd->toDateString()]);
            });
        }

        $calendarEvents = $calendarEventsQuery->get();

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
                    'responsible' => $calendarEvent->user?->name,
                ]
            ];
        }

        $events = collect($events)
            ->unique('id')
            ->values()
            ->all();

        return response()->json($events);
    }
}

