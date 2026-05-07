<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\View\View;

class MemberController extends Controller
{
    /**
     * Display the member dashboard with assigned tasks.
     */
    public function dashboard(): View
    {
        $user = auth()->user();

        $tasks = $user->tasks()
            ->with(['milestone.project', 'attachments.user', 'comments.user'])
            ->orderBy('due_date', 'asc')
            ->get();

        $recentAttachments = $tasks->flatMap->attachments
            ->sortByDesc('created_at')
            ->take(8)
            ->values();

        $recentComments = $tasks->flatMap->comments
            ->sortByDesc('created_at')
            ->take(8)
            ->values();

        $projects = $user->assignedProjects()
            ->with('user')
            ->get()
            ->sortByDesc(fn ($project) => $project->pivot->assigned_at)
            ->values();

        $unreadNotifications = Notification::query()
            ->where('user_id', $user->id)
            ->where(function ($query) {
                $query->whereNull('read_at')
                    ->orWhereNull('acknowledged_at');
            })
            ->latest('created_at')
            ->take(3)
            ->get();

        return view('member.dashboard', compact('tasks', 'projects', 'unreadNotifications', 'recentAttachments', 'recentComments'));
    }

}

