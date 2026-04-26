<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\Project;
use App\Models\Task;
use Illuminate\Http\Request;
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

        return view('member.dashboard', compact('tasks'));
    }

    /**
     * Store a comment on a task.
     */
    public function storeComment(Request $request, Task $task)
    {
        $request->validate([
            'content' => 'required|string|max:2000',
        ]);

        $user = auth()->user();

        // Membre : doit être assigné à la tâche
        if ($user->isMembre()) {
            if (!$task->users()->where('users.id', $user->id)->exists()) {
                return back()->with('error', 'Vous n\'êtes pas assigné à cette tâche.');
            }
        }

        // Chef de département : la tâche doit appartenir à sa structure (ou descendante)
        if ($user->isChefDepartement()) {
            $structureIds = Project::getStructureTreeIds($user->structure_id);
            $taskStructureId = $task->milestone?->project?->user?->structure_id;

            if (!in_array($taskStructureId, $structureIds, true)) {
                return back()->with('error', 'Cette tâche ne fait pas partie de votre département.');
            }
        }

        // Chef de projet : doit être propriétaire du projet
        if ($user->isChefProjet()) {
            if ($task->milestone?->project?->user_id !== $user->id) {
                return back()->with('error', 'Vous n\'êtes pas le responsable de ce projet.');
            }
        }

        Comment::create([
            'user_id' => $user->id,
            'task_id' => $task->id,
            'content' => $request->content,
        ]);

        return back()->with('success', 'Commentaire ajouté.');
    }

    /**
     * Delete a comment.
     */
    public function destroyComment(Comment $comment)
    {
        $this->authorize('delete', $comment);

        $comment->delete();

        return back()->with('success', 'Commentaire supprimé.');
    }
}

